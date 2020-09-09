<?php
class TimeEntry extends ModelBase {
  var $table_name = 'time_entries';

  static function generate_query($count_only, $id = null, $worker_id = null, $from_date = null, $to_date = null, $include_paid = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(te.id)" : "te.*, CONCAT_WS(' ', w.first_name, w.last_name) name, w.multiply";
     if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM time_entries te 
    JOIN workers w on w.id = te.worker_id ";
    if ($id) $query.= " LEFT JOIN users cu ON te.created_by = cu.id LEFT JOIN users uu ON te.updated_by = uu.id "; 
    $query.= "WHERE te.active = true";
    if ($id != null) $query.= " and te.id = '".mysql_real_escape_string($id)."'";
    if ($worker_id != null) $query.= " and w.id = '".mysql_real_escape_string($worker_id)."'";
    if ($from_date != null) $query.= " and te.actual_date >= '".mysql_real_escape_string($from_date)."'";
    if ($to_date != null) $query.= " and te.actual_date <= '".mysql_real_escape_string($to_date)."'";
    if (!$include_paid) $query.= " and te.is_paid = false";
    if (!$count_only) $query.= " ORDER BY te.is_paid, te.actual_date DESC, name";
    
    return $query;
  }
  
  /*static function records($filter = null)
  {
    global $factory;
    $query = TimeEntry::generate_query(true, null, $filter);
    
    return $factory->connection->execute_scalar($query);
  }*/
  
  static function load($id = null, $worker_id = null, $from_date = null, $to_date = null, $include_paid = null)
  {
    global $factory;
    $query = TimeEntry::generate_query(false, $id, $worker_id, $from_date, $to_date, $include_paid);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new TimeEntry($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new TimeEntry($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function workers()
  {
    global $factory;
    
    $query = "SELECT id, CONCAT_WS(' ', first_name, last_name) name FROM workers WHERE active = true AND show_timesheet ORDER BY name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Worker($row);
    unset($rows);
    
    return $result;
  }
  
  static function total()
  {
    global $factory;
    
    return $factory->connection->execute_scalar("SELECT count(id) FROM workers WHERE active = true");
  }
  
  function validate()
  {
    global $factory;
  
    if ($this['worker_id'] == null) $this->errors['worker_id'] = ERROR_BLANK;
    if ($this['actual_date'] == null) $this->errors['actual_date'] = ERROR_BLANK;
    
    if ($this['hours'] == null) $this->errors['hours'] = ERROR_BLANK;
    else if (tofloat($this['hours']) > 12) $this->errors['hours'] = ERROR_BIG;
    else if (tofloat($this['hours']) <= 0) $this->errors['hours'] = ERROR_LOW;
  
  if ($this['grade'] == null) $this->errors['grade'] = ERROR_BLANK;
  if ($this['time_activity_id'] == null) $this->errors['time_activity_id'] = ERROR_BLANK;
  else {
    $with_comment = $factory->connection->execute_scalar("SELECT with_comment FROM time_activities WHERE id = '".mysql_real_escape_string($this['time_activity_id'])."'");
    if ($with_comment) if ($this['comment'] == null) $this->errors['comment'] = ERROR_BLANK;
  }
  
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'worker_id',
        'actual_date',
        'hours',
        'grade',
        'time_activity_id',
        'comment',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['actual_date'] = prepare_date($this['actual_date']);
    $this['hours'] = prepare_float($this['hours']);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  static function set_paid($id) {
    global $factory;
    
    return $factory->connection->execute("UPDATE time_entries SET is_paid = true WHERE id = '".mysql_real_escape_string($id)."'");
  }
  
  static function hours_added($days)
  {
    global $factory;
    
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    return $factory->connection->execute_scalar("SELECT sum(hours) FROM time_entries where actual_date >= '".$datetime."'");
  }

  static function grades()
  {
  return array(
    array('0', '0 - Нанес ущерб'),
    array('1', '1 - Мешал другим'),
    array('2', '2 - Бездельничал'),
    array('3', '3 - Плохо работал'),
    array('4', '4 - Хуже обычного'),
    array('5', '5 - Нормально'),
    array('6', '6 - Лучше обычного'),
    array('7', '7 - Проявил инициативу'),
    array('8', '8 - Зачетно'),
    array('9', '9 - Отличился'),
    array('10', '10 - Героизм'),
  );
  }
  
  static function time_activities()
  {
    global $factory;
  
    $result = array();
    $rows = $factory->connection->execute("SELECT * FROM time_activities ORDER BY position, name");
    while($row = mysql_fetch_array($rows))
      $result[] = new TimeEntry($row);
    unset($rows);
    return $result;
  }

  static function daily_report($from_date, $to_date)
  {
    $query = "SELECT te.*, CONCAT_WS(' ', w.first_name, w.last_name) name, w.multiply, ta.name time_activity, 
(SELECT COUNT(r.id) FROM requests r WHERE r.handled_by = w.id AND r.handled_on = te.actual_date) requests
FROM time_entries te 
JOIN workers w ON w.id = te.worker_id
LEFT JOIN time_activities ta ON ta.id = te.time_activity_id
WHERE te.actual_date >= '".mysql_real_escape_string(prepare_date($from_date))."' AND te.actual_date <= '".mysql_real_escape_string(prepare_date($to_date))."'
ORDER BY te.actual_date DESC";
  
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new TimeEntry($row);
    unset($rows);
    return $result;
  }

  static function worker_report($from_date, $to_date)
  {
    $query = "SELECT te.*, CONCAT_WS(' ', w.first_name, w.last_name) name, w.multiply, ta.name time_activity, 
    (SELECT COUNT(r.id) FROM requests r WHERE r.handled_by = w.id AND r.handled_on = te.actual_date) requests
FROM time_entries te 
JOIN workers w ON w.id = te.worker_id
LEFT JOIN time_activities ta ON ta.id = te.time_activity_id
WHERE te.actual_date >= '".mysql_real_escape_string(prepare_date($from_date))."' AND te.actual_date <= '".mysql_real_escape_string(prepare_date($to_date))."'
ORDER BY name ASC, te.actual_date DESC";
  
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new TimeEntry($row);
    unset($rows);
    return $result;
  }

}

?>