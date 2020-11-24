<?php
class BillingDetail extends ModelBase {
  var $table_name = 'billing_details';
  public static $page_size = 12;

  static function load($id = null, $limit = 'default limit', $filter = null)
  {
    global $factory;
    
    $query = "SELECT bd.*, ba.lookup_code,
    bt.name billing_tariff, bdt.name billing_detail_type, bfl.theirs, bf.file_name, bfl.billing_file_id,
    s.first_name, s.last_name, s.middle_name, s.apartment";
    $query .= " FROM billing_details bd";
    $query .= " LEFT JOIN subscribers s ON bd.subscriber_id = s.id";
    $query .= " LEFT JOIN billing_accounts ba ON bd.billing_account_id = ba.id";
    $query .= " LEFT JOIN billing_tariffs bt ON bd.billing_tariff_id = bt.id";
    $query .= " LEFT JOIN billing_detail_types bdt ON bd.billing_detail_type_id = bdt.id";
    $query .= " LEFT JOIN billing_file_logs bfl ON bfl.billing_detail_id = bd.id";
    $query .= " LEFT JOIN billing_files bf ON bfl.billing_file_id = bf.id";
    
    if ($id != null) $query.= " WHERE bd.id = '".mysql_real_escape_string($id)."'";
    if ($filter != null) $query.= " WHERE ".$filter;
    
    $query .= " ORDER BY ba.lookup_code ASC, bd.actual_date ASC, apartment ASC, id ASC";
    if ($limit == 'default limit') $limit = BillingDetail::$page_size;
    if ($limit) $query .= " LIMIT ".$limit;
    
    if ($id != null || $limit == 1) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new BillingDetail($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingDetail($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function manual_types()
  {
    global $factory;
    
    $query = "SELECT * FROM billing_detail_types WHERE manual = true";
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new BillingDetail($row);
    unset($rows);
    
    return $result;
  }
  
  function validate()
  {
    global $factory;
  
    if ($this['billing_detail_type_id'] == null) $this->errors['billing_detail_type_id'] = ERROR_BLANK;
    else { 
      $this['billing_detail_type'] = $factory->connection->execute_scalar("SELECT lookup_code FROM billing_detail_types where id = '".mysql_real_escape_string($this['billing_detail_type_id'])."'");
      if ($this['billing_detail_type'] == null) $this->errors['billing_detail_type_id'] = ERROR_BLANK;
    }
    
    if ($this['value'] == null) $this->errors['value'] = ERROR_BLANK;
    else if (!isfloat($this['value'])) $this->errors['value'] = ERROR_NUMBER;
    else if ($this['billing_detail_type'] == 'payment' && tofloat($this['value']) <= 0) $this->errors['value'] = ERROR_LOW;
    
    if ($this['actual_date'] == null) $this->errors['actual_date'] = ERROR_BLANK;
    else if (parse_db_date($this['actual_date']) >= time()) $this->errors['actual_date'] = ERROR_FUTURE;
    else if (parse_db_date($this['actual_date']) <= time() - 90 * 24 * 3600) $this->errors['actual_date'] = ERROR_PAST;
    
    if ($this['comment'] == null) $this->errors['comment'] = ERROR_BLANK;
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'id',
        'value',
        'actual_date',
        'billing_detail_type_id',
        'comment',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['actual_date'] = prepare_date($this['actual_date']);
    $this['value'] = prepare_float($this['value']);
  }

                                                                // sum or count // all, manual or auto // sum, plus or minus
  static function new_payments($days, $billing_detail_type_id = null, $select = 'sum', $mode = 'all', $sign = 'all') 
  {
    global $factory;
    
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    $query = "SELECT ".($select == 'sum' ? 'sum(value)' : 'count(1)')."
      FROM billing_details
      WHERE billing_detail_type_id = '".mysql_real_escape_string($billing_detail_type_id)."' AND
            created_at >= '".$datetime."'".
      ($mode == 'manual' ? " and billing_period_id is null and billing_file_id is null" : '').
      ($mode == 'auto' ? " and (billing_period_id is not null or billing_file_id is not null)" : '').
      ($sign == 'plus' ? " and value > 0" : '').
      ($sign == 'minus' ? " and value < 0" : '');
    return 0;//$factory->connection->execute_scalar($query);
  }
  
                                          // all, manual or auto // sum, plus or minus
  static function new_payments_sum_and_count($days, $billing_detail_type_id = null, $mode = 'all', $sign = 'all') 
  {
    global $factory;
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    $query = "SELECT ".($sign == 'plus_and_minus' ? "sum(if(value>0,value,0)) `sum_plus`, sum(if(value<0,value,0)) `sum_minus`, count(id) `count`" : "sum(value) `sum`, count(id) `count`")."
      FROM billing_details
      WHERE billing_detail_type_id = '".mysql_real_escape_string($billing_detail_type_id)."' AND
            created_at >= '".$datetime."'".
      ($mode == 'manual' ? " and billing_period_id is null and billing_file_id is null" : '').
      ($mode == 'auto' ? " and (billing_period_id is not null or billing_file_id is not null)" : '').
      ($sign == 'plus' ? " and value > 0" : '').
      ($sign == 'minus' ? " and value < 0" : '');
    return $factory->connection->execute_row($query);
  }
  
  function rollbackable()
  {
    if (!$this['billing_period_id'] && !$this['billing_file_id'] &&
      isset($_SESSION) && $this['created_by'] == $_SESSION['user_id']) {
      $ts = strtotime($this['created_at']);
      return $ts >= time() - 86400;
    } else {
      return false;
    }
  }
}

?>