<?php
class Call extends ModelBase {
  var $table_name = 'calls';
  var $call_result;
  var $subscriber;
  var $request;

  static function load_result($id)
  {
    global $factory;
    $query = "SELECT * FROM call_results WHERE id = '".mysql_real_escape_string($id)."'";
  
    $result = array();
    $row = $factory->connection->execute_row($query);

    return new Call($row);
  }
  
  static function load_for_subscriber($subscriber_id)
  {
    global $factory;
    $query = "SELECT c.*, cr.name call_result, u.name user_name
  FROM calls c
  JOIN call_results cr ON c.call_result_id = cr.id
  JOIN users u ON c.created_by = u.id
    WHERE c.subscriber_id = '".mysql_real_escape_string($subscriber_id)."'
  ORDER by c.id DESC
  LIMIT 10";
  
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Call($row);
    unset($rows);
    
    return $result;
  }
  
  static function results($phone_type)
  {
    global $factory;
    $query = "SELECT * FROM call_results
    WHERE ".($phone_type == 'cell' ? "cell_phone" : "home_phone")." = true
  ORDER by position";
  
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Call($row);
    unset($rows);
    
    return $result;
  } 

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $call_attrs = $this->allow_attributes($attributes,
      array(
        'subscriber_id',
        'phone_number',
        'phone_type',
        'call_result_id',
        'promised_on',
        'new_phone_number',
      )
    );
    
    $this->map_attributes($call_attrs);
    
    $this['id'] = $id;
    $this['created_by'] = $_SESSION['user_id'];
    $this['created_at'] = date(MYSQL_TIME, time());

    $this->call_result = Call::load_result($this['call_result_id']);
    
    // Prepare values for saving
    $this['promised_on'] = $this->call_result['lookup_code'] == 'promision' || $this->call_result['lookup_code'] == 'already_paid' ?
      prepare_date($this['promised_on']) : null;
    $this['new_phone_number'] = $this->call_result['lookup_code'] == 'wrong_number' && $this['new_phone_number'] ?
      prepare_phone($this['new_phone_number']) : null;
    
    // Load additional records for processing
    if ($this->call_result['lookup_code'] == 'termination') {
      $this->subscriber = Subscriber::load($this['subscriber_id']);
     
      $req_attributes = array(
        'request_type_id' => 3, // TODO config
        'request_priority_id' => Request::default_priority(), // TODO config
        'comment' => Config::get('termination_call_comment')
      );
        
      $this->request = new Request();      
      $this->request->load_attributes_for_subscriber($this->subscriber, $req_attributes);

      $this->subscriber = new Subscriber();
      $this->subscriber->load_attributes_for_termination($attributes, $this['subscriber_id']);
      
    } else if ($this->call_result['lookup_code'] == 'change_tariff') {
      $this->subscriber = Subscriber::load($this['subscriber_id']);
      
      $req_attributes = array(
        'request_type_id' => 5, // TODO config
        'request_priority_id' => Request::default_priority(), // TODO config
        'comment' => Config::get('change_tariff_call_comment')
      );
      
      $this->request = new Request();
      $this->request->load_attributes_for_subscriber($this->subscriber, $req_attributes);
    }
  }
  
  function validate()
  {
    if ($this->call_result && $this->call_result['id']) {

      if ($this->call_result['lookup_code'] == 'promision' || $this->call_result['lookup_code'] == 'already_paid') {
        if ($this['promised_on'] == null) $this->errors['promised_on'] = ERROR_BLANK;
        else if (parse_db_date($this['promised_on']) < mktime(0,0,0,date("m"),date("d"),date("Y"))) $this->errors['promised_on'] = ERROR_PAST;
        else if (parse_db_date($this['promised_on']) >= time() + 30 * 24 * 3600) $this->errors['promised_on'] = ERROR_FUTURE;

      } else if ($this->call_result['lookup_code'] == 'termination') {
        if (!$this->subscriber->valid_termination()) {
          $this['termination_reason_id'] = $this->subscriber['termination_reason_id'];
          $this['termination_comment'] = $this->subscriber['termination_comment'];
          $this->errors = $this->subscriber->errors;
        }
      }
    }
  }
  
  function save_call()
  {
    if ($this->request) $this['request_id'] = $this->request->create();
    if ($this->subscriber && $this->subscriber['termination_reason_id']) $this->subscriber->terminate();
    $this->save();
  }
  
  static function has_answer($subscriber_id) {
    global $factory;
    $query = "SELECT id FROM calls
    WHERE subscriber_id = '".mysql_real_escape_string($subscriber_id)."' AND 
  ((DATE_ADD(promised_on, INTERVAL 3 DAY) >= CURRENT_DATE()) OR request_id IS NOT NULL)
  LIMIT 1";
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function corrected_phone_number($subscriber_id, $phone_type, $phone_number) {
    global $factory;
    $query = "SELECT new_phone_number FROM calls
    WHERE
      subscriber_id = '".mysql_real_escape_string($subscriber_id)."' AND 
      phone_type = '".mysql_real_escape_string($phone_type)."' AND 
      new_phone_number IS NOT NULL
    ORDER by id DESC
    LIMIT 1";
    
    $new_phone_number = $factory->connection->execute_scalar($query);
    return $new_phone_number ? $new_phone_number : $phone_number;
  }
  
  static function last_call($subscriber_id, $phone_type, $phone_number)
  {
    global $factory;
    $query = "SELECT c.*, cr.name, cr.lookup_code FROM calls c
    JOIN call_results cr ON cr.id = c.call_result_id
    WHERE 
      subscriber_id = '".mysql_real_escape_string($subscriber_id)."' AND 
      phone_type = '".mysql_real_escape_string($phone_type)."' AND 
      phone_number = '".mysql_real_escape_string($phone_number)."' 
    ORDER by id DESC
    LIMIT 1";
    
    return $factory->connection->execute_row($query);
  }
  
  static function report_summary($days)
  {
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    
    $sql = "SELECT DATE(c.created_at) actual_date, count(c.id) cnt, c.call_result_id, cr.name
FROM calls c
JOIN call_results cr ON cr.id = c.call_result_id
WHERE c.created_at >= '".$datetime."'
GROUP BY DATE(c.created_at), c.call_result_id
ORDER BY DATE(c.created_at) DESC, count(c.id) DESC";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($sql);
    while($row = mysql_fetch_array($rows))
      $result[] = new Call($row);
    unset($rows);
    return $result;
  }
  
  static function report_details($days)
  {
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    
    $sql = "SELECT c.*, s.active, ba.actual_balance, bt.subscription_fee, DATE(c.created_at) actual_date, cr.name call_result, u.name user_name, s.first_name, s.last_name, s.middle_name
FROM calls c
JOIN subscribers s ON s.id = c.subscriber_id
JOIN billing_accounts ba ON s.billing_account_id = ba.id
JOIN billing_tariffs bt ON s.billing_tariff_id = bt.id
JOIN call_results cr ON cr.id = c.call_result_id
JOIN users u ON c.created_by = u.id
WHERE c.created_at >= '".$datetime."'
ORDER BY c.id DESC";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($sql);
    while($row = mysql_fetch_array($rows))
      $result[] = new Call($row);
    unset($rows);
    return $result;
  }
  
  static function today_calls()
  {
    $sql = "select count(id) cnt from calls where date(created_at) = CURRENT_DATE()";
    global $factory;
    return $factory->connection->execute_scalar($sql);
  }
  
  static function days_from_last_call()
  {
    $sql = "select DATEDIFF(CURRENT_DATE(), created_at) from calls order by id desc LIMIT 1";
    global $factory;
    return $factory->connection->execute_scalar($sql);
  }
}

?>