<?php
class Worker extends ModelBase {
  var $table_name = 'workers';
  public static $page_size = 10;

  static function generate_query($count_only, $id = null, $filter = null, $page = 1, $limit = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(w.id)" : "w.*, u.login, u.disabled_at";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM workers w 
      LEFT JOIN users u on u.id = w.user_id ";
    if ($id) $query.= " LEFT JOIN users cu ON w.created_by = cu.id LEFT JOIN users uu ON w.updated_by = uu.id"; 
    $query.= " WHERE ";
    $query.= ($id != null ? "w.id = '".mysql_real_escape_string($id)."'" : "w.active = true");
    
    if ($filter) {
      $words = explode(' ', $filter);
      foreach($words as $word) {
        $safe_word = mysql_real_escape_string($word);
        $query.= " and (";
        $query.= "w.first_name LIKE '%".$safe_word."%' or ";
        $query.= "w.last_name LIKE '%".$safe_word."%' or ";
        $query.= "w.middle_name LIKE '%".$safe_word."%' or ";
        if ($phone = prepare_phone($word)) {
          $safe_phone = mysql_real_escape_string($phone);
          $query.= "w.cell_phone1 LIKE '%".$safe_phone."%' or ";
          $query.= "w.cell_phone2 LIKE '%".$safe_phone."%' or ";
          $query.= "w.cell_phone3 LIKE '%".$safe_phone."%' or ";
          $query.= "w.home_phone LIKE '%".$safe_phone."%' or ";
        }
        $query.= "w.comment LIKE '%".$safe_word."%' or ";
        $query.= "u.login LIKE '%".$safe_word."%'";
        $query.= ")";
      }
    }
    
    if (!$count_only) $query.= " ORDER BY w.first_name, w.last_name";
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null)
  {
    global $factory;
    $query = Worker::generate_query(true, null, $filter);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function load($id = null, $filter = null, $page = 1, $limit = null)
  {
    global $factory;
    $query = Worker::generate_query(false, $id, $filter, $page, $limit);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Worker($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Worker($row);
      unset($rows);
    }
    
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
  
    if ($this['last_name'] == null) $this->errors['last_name'] = ERROR_BLANK;
    if ($this['first_name'] == null) $this->errors['first_name'] = ERROR_BLANK;
    if ($this['middle_name'] == null) $this->errors['middle_name'] = ERROR_BLANK;
    if ($this['address'] == null) $this->errors['address'] = ERROR_BLANK; 
    if ($this['cell_phone1'] == null) $this->errors['cell_phone1'] = ERROR_BLANK; 
    
    if ($this['passport_identifier'] == null) $this->errors['passport'] = ERROR_BLANK;
    if ($this['passport_issued_by'] == null) $this->errors['passport'] = ERROR_BLANK;
    if ($this['passport_issued_on'] == null) $this->errors['passport'] = ERROR_BLANK;
    else if (parse_db_date($this['passport_issued_on']) >= time()) $this->errors['passport'] = ERROR_FUTURE;
    
    if ($this['birth_date'] == null) $this->errors['birth_date'] = ERROR_BLANK;
    else if (parse_db_date($this['birth_date']) >= time()) $this->errors['birth_date'] = ERROR_FUTURE;
    
    if ($this['multiply'] == null) { /* Do nothing */}
    else if (!is_numeric($this['multiply'])) $this->errors['multiply'] = ERROR_NUMBER;
    
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'last_name',
        'first_name',
        'middle_name',
        'address',
        'passport_identifier',
        'passport_issued_by',
        'passport_issued_on',
        'home_phone',
        'cell_phone1',
        'cell_phone2',
        'birth_date',
        'multiply',
        'comment',
        'show_timesheet',
        'show_requests',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['last_name'] = prepare_name($this['last_name']);
    $this['first_name'] = prepare_name($this['first_name']);
    $this['middle_name'] = prepare_name($this['middle_name']);
    $this['passport_identifier'] = mb_strtoupper($this['passport_identifier'], "UTF-8");
    $this['passport_issued_on'] = prepare_date($this['passport_issued_on']);
    $this['home_phone'] = prepare_phone($this['home_phone']);
    $this['cell_phone1'] = prepare_phone($this['cell_phone1']);
    $this['cell_phone2'] = prepare_phone($this['cell_phone2']);
    $this['birth_date'] = prepare_date($this['birth_date']);
    $this['multiply'] = prepare_float($this['multiply']);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  static function worker_id_for_user($user_id) {
    global $factory;
    return $factory->connection->execute_scalar(
      "SELECT id FROM workers WHERE user_id = '".mysql_real_escape_string($user_id)."'"
    );
  }
  
  static function birth_dates() {
    $result = array();
    
    $query = "SELECT *, DATEDIFF(next_birthday, NOW()) AS days, YEAR(next_birthday) - YEAR(birth_date) years FROM (
    SELECT *, ADDDATE(birthday, INTERVAL birthday < DATE(NOW()) YEAR) AS next_birthday
    FROM (
        SELECT *, ADDDATE(birth_date, INTERVAL YEAR(NOW()) - YEAR(birth_date) YEAR) AS birthday
        FROM workers
        WHERE active = true
      ) AS T1
    ) AS T2
    WHERE DATEDIFF(next_birthday, NOW()) < 5
    ORDER BY days ASC";
    
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Worker($row);
    unset($rows);
    
    return $result;
  }
}

?>