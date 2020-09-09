<?php
class BillingPeriod extends ModelBase {
  var $table_name = 'billing_periods';
  public static $page_size = 12;

  static function load($id = null, $limit = 'default limit')
  {
    global $factory;
    
    $query = "SELECT * FROM billing_periods";
    if ($id != null) $query.= " WHERE id = '".mysql_real_escape_string($id)."'";
    $query .= " ORDER BY actual_date DESC";
    
    if ($limit == 'default limit') $limit = BillingPeriod::$page_size;
    if ($limit) $query .= " LIMIT ".$limit;
    
    if ($id != null || $limit == 1) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new BillingPeriod($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingPeriod($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function last()
  {
    global $factory;

    $finished_at = strtotime(date(MYSQL_TIME, time())."-1 day");
    $finished_at = date(MYSQL_TIME, $finished_at);
    
    $query = "SELECT * 
    FROM billing_periods 
    WHERE (finished_at > '".mysql_real_escape_string($finished_at)."' AND sms_sent_at IS NULL) OR finished_at IS NULL
    ORDER BY actual_date 
    DESC LIMIT 1";
    
    $row = $factory->connection->execute_row($query);
    return $row ? new BillingPeriod($row) : null;
  }
  
  function rollback()
  {
    global $factory;
  
    $query = "SELECT id FROM billing_periods WHERE actual_date > '".mysql_real_escape_string($this->attributes['actual_date'])."'";
    if ($factory->connection->execute_scalar($query) == null) { // if this period is the latest
      $bp = new BillingPeriod();
      $bp['id'] = $this->attributes['id'];
      $bp['finished_at'] = null;
      $bp->update(); // Corrupt this period

      $query = "SELECT id FROM billing_details WHERE billing_period_id='".mysql_real_escape_string($this->attributes['id'])."'";
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows)) {
        BillingAccount::rollback($row['id']);
      }
      
      $this->destroy(); // Destroy this period
      return true;
    }
    
    return false;
  }
  
  static function mark_sms_sent($billing_period_id)
  {
    $bp = new BillingPeriod();
    $bp['id'] = $billing_period_id;
    $bp['sms_sent_at'] = date(MYSQL_TIME);
    $bp->update();
  }
  
  static function load_new_memos($billing_period_id)
  {
    global $factory;
    
    $query = "SELECT ba.lookup_code, s.*, houses.house, houses.building, streets.name street, cities.name city
    FROM billing_accounts ba
    JOIN billing_details bd ON bd.billing_account_id = ba.id AND bd.value <> 0
    LEFT JOIN billing_details bd2 ON bd2.billing_account_id = ba.id AND bd2.billing_period_id is not null AND bd.id > bd2.id
    JOIN subscribers s ON s.billing_account_id = ba.id
    JOIN houses ON houses.id = s.house_id
    JOIN streets ON streets.id = houses.street_id
    JOIN cities ON cities.id = streets.city_id
    WHERE ba.active = true and s.active = true AND bd2.id is null
    AND bd.billing_period_id = '".mysql_real_escape_string($billing_period_id)."'
    ORDER BY cities.name, streets.name, houses.house, houses.building, s.apartment";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new BillingPeriod($row);
    unset($rows);
    
    return $result;
  }
  
  static function inc_client_memo_downloads($billing_period_id)
  {
    global $factory;
    $query = "UPDATE billing_periods SET client_cards_downloads = client_cards_downloads + 1 WHERE id = '".mysql_real_escape_string($billing_period_id)."'";
    $factory->connection->execute_void($query);
  }
  
  static function get_last_period_info()
  {
    $query = "SELECT id, actual_date, sms_sent_at, client_cards_downloads
    FROM billing_periods
    ORDER BY actual_date DESC
    LIMIT 1";
    
    global $factory;
    $row = $factory->connection->execute_row($query);
    return $row ? new BillingPeriod($row) : null;
  }

}

?>