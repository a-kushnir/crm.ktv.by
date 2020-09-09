<?php
class BillingTariff extends ModelBase {
  var $table_name = 'billing_tariffs';

  static function load($id = null, $city_id = null, $inlcude_private = false)
  {
    global $factory;
    
    $query = "SELECT bt.*, cities.name city";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM billing_tariffs bt 
    LEFT JOIN cities on cities.id = bt.city_id";
    if ($id) $query.= " LEFT JOIN users cu ON bt.created_by = cu.id LEFT JOIN users uu ON bt.updated_by = uu.id";
    $query.= " WHERE 1=1".
    ($id != null ? " AND bt.id = '".mysql_real_escape_string($id)."'" : " AND active = true").
    ($city_id != null ? " AND bt.active = true and (bt.city_id = '".mysql_real_escape_string($city_id)."' OR bt.city_id is null)" : "").
    ($id != null || $inlcude_private ? '' : " AND public = true").
    " ORDER BY cities.name, bt.name";
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new BillingTariff($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingTariff($row);
      unset($rows);
    }
    
    return $result;
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'name',
        'description',
        'activation_fee',
        'subscription_fee',
        'city_id',
        'public',
        'default',
        'filter',
        'with_justification',
        'with_ends_on',
      )
    );
    
    $this->map_attributes($attributes);
  
    $this['id'] = $id;
    $this['public'] = prepare_phone($this['public']);
    if (!$this['city_id']) $this['city_id'] = null;
    
    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  function validate()
  {
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;
    if ($this['description'] == null) $this->errors['description'] = ERROR_BLANK;
    
    if ($this['activation_fee'] == null) $this->errors['activation_fee'] = ERROR_BLANK;
    else if (!isfloat($this['activation_fee'])) $this->errors['activation_fee'] = ERROR_NUMBER;
    else $this['activation_fee'] = prepare_float($this['activation_fee']);

    if ($this['subscription_fee'] == null) $this->errors['subscription_fee'] = ERROR_BLANK;
    else if (!isfloat($this['subscription_fee'])) $this->errors['subscription_fee'] = ERROR_NUMBER;
    else $this['subscription_fee'] = prepare_float($this['subscription_fee']);
  }
  
  function has_subscribers()
  {
    $query = "SELECT 1
FROM subscribers
WHERE billing_tariff_id = '".mysql_real_escape_string($this['id'])."'";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
  
  static function save_history($billing_period_id)
  {
    global $factory;
  
    $query = "SELECT actual_date 
FROM billing_periods
WHERE id = '".mysql_real_escape_string($billing_period_id)."'";
    $actual_date = $factory->connection->execute_scalar($query);
    $ends_date = date(MYSQL_DATE,strtotime($actual_date."-1 month -1 day"));
    $starts_date = date(MYSQL_DATE,strtotime($actual_date."-1 month"));
  
    $query = "SELECT bd.billing_tariff_id id, -bd.`value` subscription_fee
FROM billing_details bd
JOIN billing_detail_types bdt ON bdt.id = bd.billing_detail_type_id
WHERE bd.billing_period_id = '".mysql_real_escape_string($billing_period_id)."'
AND bdt.lookup_code = 'subscription_fee'
GROUP BY bd.billing_tariff_id";
  
    $tariffs = $factory->connection->execute_table($query);
    foreach ($tariffs as $tariff) {
    
      $query = "SELECT * 
FROM billing_tariff_history
WHERE billing_tariff_id = '".mysql_real_escape_string($tariff['id'])."' AND ends_on IS NULL
LIMIT 1";
      $history = $factory->connection->execute_row($query);
      
      if (!$history || $history['subscription_fee'] != $tariff['subscription_fee']) {
        if ($history)
          $factory->update('billing_tariff_history', $history['id'], array(
            'ends_on' => $ends_date,
          ));
      
        $factory->create('billing_tariff_history', array(
          'billing_tariff_id' => $tariff['id'],
          'starts_on' => $starts_date,
          'subscription_fee' => prepare_float($tariff['subscription_fee']),
        ));
      }
      
    }
  }
  
  function load_history()
  {
    $query = "SELECT * 
FROM billing_tariff_history
WHERE billing_tariff_id = '".mysql_real_escape_string($this['id'])."'
ORDER BY starts_on DESC";
    global $factory;
    return $factory->connection->execute_table($query);
  }
}

?>