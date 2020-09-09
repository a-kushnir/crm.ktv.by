<?php
function cleanup_data() {
  $delete = array();
  $update = array();
  
  $datetime = strtotime(date(MYSQL_DATE, time())."-".(int)Channel::$forget_connection." day");
  $datetime = date(MYSQL_DATE, $datetime);
  $update[] = array('stations', 'notified_at = NULL', "notified_at < '".mysql_real_escape_string($datetime)."'");
  
  $datetime = strtotime(date(MYSQL_DATE, time())."-".(int)Channel::$cleanup_pings." day");
  $datetime = date(MYSQL_DATE, $datetime);
  $delete[] = array('station_logs', "`event` = 'ping' AND created_at < '".mysql_real_escape_string($datetime)."'");
  
  $datetime = strtotime(date(MYSQL_DATE, time())."-".(int)Channel::$cleanup_channels." day");
  $datetime = date(MYSQL_DATE, $datetime);
  $delete[] = array('station_logs', "`event` <> 'ping' AND created_at < '".mysql_real_escape_string($datetime)."'");
  
  $datetime = strtotime(date(MYSQL_DATE, time())."-1 year");
  $datetime = date(MYSQL_DATE, $datetime);
  $delete[] = array('user_hits', "created_at < '".mysql_real_escape_string($datetime)."'");
  $delete[] = array('user_sessions', "updated_at < '".mysql_real_escape_string($datetime)."'");
  $delete[] = array('errors', "created_at < '".mysql_real_escape_string($datetime)."'");
  $delete[] = array('user_events', "created_at < '".mysql_real_escape_string($datetime)."'");
  
  $result = array();
  
  global $factory;
  foreach($update as $table_info) {
    $row_count = $factory->update_all($table_info[0], $table_info[1], $table_info[2]);
    $result[] = 'Updated '.$row_count.' '.$table_info[0].' (as '.$table_info[1].') (for '.$table_info[2].')';
  }
  foreach($delete as $table_info) {
    $row_count = $factory->delete_all($table_info[0], $table_info[1]);
    $result[] = 'Deleted '.$row_count.' '.$table_info[0].' (for '.$table_info[1].')';
  } 
  return $result;
}
?>