<?php
class BillingAccount extends ModelBase {
  var $table_name = 'billing_accounts';

  static function load($id = null)
  {
    global $factory;
    
    $query = "SELECT * FROM billing_accounts WHERE ".
    ($id != null ? " id = ".mysql_real_escape_string($id) : "").
    ($id == null ? " active = true" : "").
    " ORDER BY lookup_code";
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new BillingAccount($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingAccount($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function load_memos_for_subscriber($subscriber_id)
  {
    global $factory;
    
    $query = "SELECT ba.lookup_code, s.*, houses.house, houses.building, streets.name street, cities.name city
    FROM billing_accounts ba
    JOIN subscribers s ON s.billing_account_id = ba.id
    JOIN houses ON houses.id = s.house_id
    JOIN streets ON streets.id = houses.street_id
    JOIN cities ON cities.id = streets.city_id
    WHERE ba.active = true and s.active = true AND s.id = '".mysql_real_escape_string($subscriber_id)."'
    ORDER BY cities.name, streets.name, houses.house, houses.building, s.apartment";
    
    $row = $factory->connection->execute_row($query);
    
    $result = array();
    $result[] = new BillingAccount($row);
    while(count($result)<6)
      $result[] = $result[0];
    
    return $result;
  }

  static function load_memos_for_house($house_id)
  {
    global $factory;
    
    $query = "SELECT ba.lookup_code, s.*, houses.house, houses.building, streets.name street, cities.name city
    FROM billing_accounts ba
    JOIN subscribers s ON ba.id = s.billing_account_id
    JOIN houses ON houses.id = s.house_id
    JOIN streets ON streets.id = houses.street_id
    JOIN cities ON cities.id = streets.city_id
    WHERE ba.active = true and s.active = true AND s.house_id = '".mysql_real_escape_string($house_id)."'
    ORDER BY cities.name, streets.name, houses.house, houses.building, s.apartment";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new BillingAccount($row);
    unset($rows);
    
    return $result;
  }
  
  static function load_for_sms($billing_period_id)
  {
    global $factory;
    
    $query = "SELECT ba.lookup_code, ba.actual_balance, bd.value, bd.actual_date, s.*
    FROM billing_accounts ba
    JOIN billing_details bd ON bd.billing_account_id = ba.id AND bd.value <> 0
    JOIN subscribers s ON s.id = ba.subscriber_id
    WHERE ba.active = true AND s.active = true AND s.cell_phone <> '' AND s.allow_sms = true
    AND ba.actual_balance < 0
    AND bd.billing_period_id = '".mysql_real_escape_string($billing_period_id)."'
    ORDER BY ba.lookup_code";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new BillingAccount($row);
    unset($rows);
    
    return $result;
  }
  
  function validate()
  {
    if ($this->attributes['lookup_code'] == null) $this->errors['lookup_code'] = ERROR_BLANK;
    
    if (!isset($this->errors['lookup_code'])) {
      if (!$this->valid_lookup_code($this->attributes['lookup_code']))
          $this->errors['lookup_code'] = ERROR_EXIST;
    }
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'lookup_code',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this->attributes['id'] = $id;

    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  function generate_lookup_code($subscriber)
  {
    if ($this['lookup_code']) return;
    
    $this['lookup_code'] = $this->generate_lookup_code_way_1($subscriber);
    if (!$this->valid_lookup_code($this['lookup_code'])) {
      $this['lookup_code'] = $this->generate_lookup_code_way_2($subscriber);
      if (!$this->valid_lookup_code($this['lookup_code'])) {
        $this['lookup_code'] = $this->generate_random_lookup_code();
      }
    }
  }

  private function generate_lookup_code_way_1($subscriber)
  {
    $value = $subscriber ? $subscriber['cell_phone'] : '';

    $result = preg_replace("/[^0-9]/","", $value);
    $result = substr($result, -7);
    
    return $result;
  }

  private function generate_lookup_code_way_2($subscriber)
  {
    $value = $subscriber ? $subscriber['home_phone'] : '';
    
    $result = preg_replace("/[^0-9]/","", $value);
    $result = substr($result, -7);
    
    return $result;
  }

  private function generate_random_lookup_code()
  {
    return '999'
      .rand_string(4, '0123456789');
  }
  
  function valid_lookup_code($value)
  {
    global $factory;
    
    $id = isset($this->attributes['id']) ? $this->attributes['id'] : null;
    
    return $value != null && 
      strlen($value) == 7 && 
      $factory->connection->execute_scalar("SELECT id FROM billing_accounts
          WHERE lookup_code = '".mysql_real_escape_string($value)."' and active = true".($id != null ? " and id <> '".mysql_real_escape_string($id)."'": '')) == null;
  }
  
  function change_actual_balance($value, $billing_detail_type, $actual_date, $comment, $subscriber_id, $billing_period_id, $billing_file_id) {
    global $factory;
    $bd_id = null;
    
    $value = tofloat($value);
    $billing_detail_type_id = $factory->connection->execute_scalar("SELECT id FROM billing_detail_types WHERE lookup_code = '".mysql_real_escape_string($billing_detail_type)."'");
    if($billing_detail_type_id == null) die("FATAL: Billing detail type with lookup_code '".$billing_detail_type."' isn't found!");
    
    $row = $factory->connection->execute_row("SELECT id, actual_balance, lock_version
    FROM billing_accounts WHERE id ='".mysql_real_escape_string($this->attributes['id'])."'");
    
    $subscriber = $subscriber_id ? $factory->connection->execute_row("SELECT id, billing_tariff_id
    FROM subscribers WHERE id ='".mysql_real_escape_string($subscriber_id)."' AND billing_account_id = '".mysql_real_escape_string($this->attributes['id'])."'") : null;
    
    if ($subscriber_id && !$subscriber) die("FATAL: Billing account ".$this->attributes['id']."' doesn't belong to subscriber ".$subscriber_id."!");

    $ba = new BillingAccount();
    $ba['id'] = $this->attributes['id'];
    $ba['actual_balance'] = prepare_float(tofloat($row['actual_balance']) + $value);
    $ba['lock_version'] = $row['lock_version'] + 1;
    $ba['updated_at'] = date(MYSQL_TIME, time());
  
    $bd = new BillingDetail();
    $bd['billing_account_id'] = $ba['id'];
    $bd['subscriber_id'] = $subscriber ? $subscriber['id'] : null;
    $bd['billing_tariff_id'] = $subscriber ? $subscriber['billing_tariff_id'] : null;
    $bd['billing_detail_type_id'] = $billing_detail_type_id;
    $bd['billing_period_id'] = $billing_period_id;
    $bd['billing_file_id'] = $billing_file_id;
    $bd['actual_date'] = $actual_date;
    $bd['value'] = prepare_float($value);
    $bd['actual_balance'] = prepare_float($ba['actual_balance']);
    $bd['created_at'] = date(MYSQL_TIME, time());
    $bd['created_by'] = isset($_SESSION) ? $_SESSION['user_id'] : null;
    $bd['comment'] = $comment;
    
    // Optimistic locking
    if ($ba->update("lock_version = '".mysql_real_escape_string($row['lock_version'])."'")) {
      $bd_id = $bd->create();
      
      $this->attributes['actual_balance'] =  $ba['actual_balance'];
      $this->attributes['updated_at'] = $ba['updated_at'];
    }

    return $bd_id;
  }
  
  static function rollback($billing_detail_id) {
    global $factory;
    $success = false;
    
    $row = $factory->connection->execute_row("SELECT id, billing_account_id, value
    FROM billing_details WHERE id ='".mysql_real_escape_string($billing_detail_id)."'");
    
    if ($row) {
      $bd = new BillingDetail($row);
      
      $row = $factory->connection->execute_row("SELECT id, actual_balance, lock_version
      FROM billing_accounts WHERE id ='".mysql_real_escape_string($bd['billing_account_id'])."'");

      $ba = new BillingAccount();
      $ba['id'] = $bd['billing_account_id'];
      $ba['actual_balance'] = prepare_float(tofloat($row['actual_balance']) - tofloat($bd['value']));
      $ba['lock_version'] = $row['lock_version'] + 1;
      $ba['updated_at'] = date(MYSQL_TIME, time());
    
      // Optimistic locking
      if ($ba->update("lock_version = '".mysql_real_escape_string($row['lock_version'])."'")) {
        $bd->destroy();
        $success = true;
      }
    }
    return $success;
  }
  
  static function last_paid($subscriber_id) {
  global $factory;
  return $factory->connection->execute_scalar(
"SELECT bd.actual_date
FROM subscribers s
JOIN billing_details bd ON s.billing_account_id = bd.billing_account_id
WHERE bd.billing_detail_type_id = 3 && s.id ='".mysql_real_escape_string($subscriber_id)."'
ORDER by actual_date DESC");
  }
  
  static function sum() {
    global $factory;
    return $factory->connection->execute_scalar("SELECT sum(actual_balance) FROM billing_accounts WHERE active = true");
  }
  
  static function sum_positive() {
    global $factory;
    return $factory->connection->execute_scalar("SELECT sum(actual_balance) FROM billing_accounts WHERE actual_balance > 0 and active = true");
  }
  
  static function sum_negative() {
    global $factory;
    return $factory->connection->execute_scalar("SELECT sum(actual_balance) FROM billing_accounts WHERE actual_balance < 0 and active = true");
  }
  
  static function flow() {
    global $factory;
    return $factory->connection->execute_scalar(
"SELECT sum(bt.subscription_fee)
FROM billing_accounts ba
JOIN subscribers s ON s.billing_account_id = ba.id
JOIN billing_tariffs bt ON s.billing_tariff_id = bt.id
WHERE ba.active = true AND s.active");
  }
  
  function owner()
  {
    return BillingAccount::load_owner($this['id']);
  }
  
  static function load_owner($billing_account_id)
  {
    global $factory;
    $house_id = $factory->connection->execute_scalar("SELECT id FROM houses WHERE billing_account_id = '".mysql_real_escape_string($billing_account_id)."' LIMIT 1");
    if($house_id !== null)
      return House::load($house_id);
    else {
      $subscriber_id = $factory->connection->execute_scalar("SELECT id FROM subscribers WHERE billing_account_id = '".mysql_real_escape_string($billing_account_id)."' LIMIT 1");
      return $subscriber_id !== null ? Subscriber::load($subscriber_id) : null;
    }
  }
}
?>