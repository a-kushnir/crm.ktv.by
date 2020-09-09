<?php
class Sms extends ModelBase {
  var $table_name = 'sms_queue';

  static function send_to_admin($cell_phone, $message)
  {
    if (!$cell_phone) return -1;
  
    $sms = new Sms();
    
    $sms['phone_number'] = $cell_phone;
    $sms['message'] = $message;
    
    if (isset($_SESSION['user_id'])) $sms['created_by'] = $_SESSION['user_id'];
    $sms['created_at'] = date(MYSQL_TIME, time());
    
    return $sms->save();
  }
  
  static function send($subscriber, $message)
  {
    if (!$subscriber['cell_phone']) return -1;
  
    $sms = new Sms();
    
    $sms['subscriber_id'] = $subscriber['id'];
    $sms['phone_number'] = $subscriber['cell_phone'];
    $sms['message'] = $message;
    
    if (isset($_SESSION['user_id'])) $sms['created_by'] = $_SESSION['user_id'];
    $sms['created_at'] = date(MYSQL_TIME, time());
    
    return $sms->save();
  }

  static function available($subscriber_id)
  {
    $query = "SELECT id FROM subscribers WHERE active = true AND cell_phone <> '' AND allow_sms = true AND id = '".mysql_real_escape_string($subscriber_id)."'";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
  
  static function count_subscribers()
  {
    $query = "SELECT COUNT(id) FROM subscribers WHERE active = true AND cell_phone <> '' AND allow_sms = true";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
  
  static function load_subscribers($subscriber_id = null)
  {
    $query = "SELECT id, cell_phone FROM subscribers WHERE active = true AND cell_phone <> '' AND allow_sms = true";
    if ($subscriber_id) $query.= " AND id = '".mysql_real_escape_string($subscriber_id)."'";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function load_for_subscriber($subscriber_id)
  {
    $query = "SELECT sq.phone_number, sq.message, null as sent_at, NOW() sort_order1, sq.id sort_order2
    FROM sms_queue sq
    WHERE sq.subscriber_id = '".mysql_real_escape_string($subscriber_id)."'";
    
    $query.= " UNION ";
    
    $query.= "SELECT sh.phone_number, sh.message, sh.sent_at, sh.sent_at sort_order1, sh.id sort_order2
    FROM sms_history sh
    WHERE sh.subscriber_id = '".mysql_real_escape_string($subscriber_id)."'";

    $query.= "ORDER BY sort_order1 DESC, sort_order2 DESC
    LIMIT 10";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Sms($row);
    unset($rows);
    
    return $result;
  }
  
  static function stats($mode = 'all'){
    $query = "SELECT COUNT(id) cnt, MAX(datediff(NOW(), created_at)) max FROM sms_queue";
    if ($mode == 'admin') $query .= " WHERE subscriber_id IS NULL";
    if ($mode == 'subs') $query .= " WHERE subscriber_id IS NOT NULL";    
    global $factory;
    return $factory->connection->execute_row($query);
  }
}

?>