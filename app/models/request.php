<?php
class Request extends ModelBase {
  var $table_name = 'requests';
  public static $wait_normal = 3;
  public static $wait_long = 7;
  
  static function generate_query($count_only, $id = null, $house_id = null, $request_type_id = null, $include_closed = null, $selection = null)
  {
    $created_at = strtotime(date(MYSQL_DATE, time())."-7 day");
    $created_at = date(MYSQL_DATE, $created_at);
  
    $query = 'SELECT ';
    $query.= $count_only ? "count(r.id)" : "r.*, rt.name request_type, rp.name request_priority,
      s.first_name, s.middle_name, s.last_name,
      cities.id city_id, cities.name city, streets.id street_id, streets.name street, h.id house_id, h.house, h.building,
      ba.id billing_account_id, ba.lookup_code, ba.actual_balance, bah.id house_billing,
      CONCAT(w.first_name,' ',w.last_name) worker,
      h.apartment_schema, h.apartments, h.entrances, h.floors, h.is_online";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
     $query.= " FROM requests r
    JOIN request_types rt on rt.id = r.request_type_id
    JOIN request_priorities rp on rp.id = r.request_priority_id
    LEFT JOIN subscribers s on r.subscriber_id = s.id
    LEFT JOIN houses bah ON bah.billing_account_id = s.billing_account_id
    JOIN houses h on h.id = r.house_id
    LEFT JOIN streets on streets.id = h.street_id
    LEFT JOIN cities on cities.id = streets.city_id
    LEFT JOIN billing_accounts ba on s.billing_account_id = ba.id 
    LEFT JOIN workers w on w.id = r.handled_by";
    if ($id) $query.= " LEFT JOIN users cu ON r.created_by = cu.id LEFT JOIN users uu ON r.updated_by = uu.id"; 
    $query.= " WHERE 1=1";
  
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
      if (isset($selected_region['city_district_id'])) $query.= " AND h.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
      if (isset($selected_region['city_microdistrict_id'])) $query.= " AND h.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
     }

    if ($id == null) $query.= " and r.active = true and (r.handled_on is null or r.handled_on > '".mysql_real_escape_string($created_at)."' or r.subscriber_id is null)";
    if ($id == null && !$include_closed) $query.= " and r.handled_on is null";
    if ($id != null) $query.= " and r.id = '".mysql_real_escape_string($id)."'";
    if (is_numeric($house_id)) $query.= " and r.house_id = '".mysql_real_escape_string($house_id)."'";
    if ($house_id == 'online') $query.= " and h.is_online = true";
    if ($house_id == 'offline') $query.= " and h.is_online = false";
    if ($house_id == 'selection' && $selection) {
      $h_ids = array_filter(explode(',', $selection), 'strlen');
      foreach($h_ids as $index => $h_id) $h_ids[$index] = (int)$h_id;
      $selection = implode(',', $h_ids);
      $query.= " and h.id in (".$selection.")";
    }
    if (is_numeric($request_type_id)) $query.= " and r.request_type_id = '".mysql_real_escape_string($request_type_id)."'";
    else if ($request_type_id == 'technician') $query.= " and r.request_type_id IN (1,2,3,4,5,6)";
    else if ($request_type_id == 'dispatcher') $query.= " and r.request_type_id IN (8)";
    
    if (!$count_only) $query.= " ORDER BY cities.name, streets.name, h.house, h.building, apartment";    
    return $query;
  }
  
  static function load($id = null, $house_id = null, $request_type_id = null, $include_closed = null, $selection = null)
  {
    global $factory;
    $query = Request::generate_query(false, $id, $house_id, $request_type_id, $include_closed, $selection);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Request($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Request($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function priorities()
  {
    global $factory;
    $rows = $factory->connection->execute('SELECT * FROM request_priorities');

    $result = array();
    while($row = mysql_fetch_array($rows))
      $result[] = new Request($row);
    unset($rows);
    
    return $result;
  }
  
  static function default_priority()
  {
    global $factory;
    return $factory->connection->execute_scalar('SELECT id FROM request_priorities WHERE `default` = true LIMIT 1');
  }
  
  static function load_for_subscriber($subscriber_id)
  {
    global $factory;
    
    $query = "SELECT r.*, rt.name request_type,
      s.first_name, s.middle_name, s.last_name,
      CONCAT(w.first_name,' ',w.last_name) worker,
      bah.id house_billing
    FROM requests r
    JOIN request_types rt on rt.id = r.request_type_id
    LEFT JOIN subscribers s on r.subscriber_id = s.id
    LEFT JOIN houses bah ON bah.billing_account_id = s.billing_account_id
    LEFT JOIN workers w on w.id = r.handled_by
    WHERE r.active = true and r.subscriber_id = '".mysql_real_escape_string($subscriber_id)."'
    ORDER BY r.created_at DESC
    LIMIT 10";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Request($row);
    unset($rows);
    
    return $result;
  }
  
  static function load_by_address($house_id, $apartment)
  {
    global $factory;
    
    $query = "SELECT r.*, rt.name request_type,
      CONCAT(w.first_name,' ',w.last_name) worker
    FROM requests r
    JOIN request_types rt on rt.id = r.request_type_id
    LEFT JOIN workers w on w.id = r.handled_by
    WHERE r.active = true and r.subscriber_id IS NULL
     and r.house_id = '".mysql_real_escape_string($house_id)."'
     and r.apartment = '".mysql_real_escape_string($apartment)."'
    ORDER BY r.created_at DESC";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Request($row);
    unset($rows);
    
    return $result;
  }
  
  static function houses($include_closed = null)
  {
    global $factory;
  
    $created_at = strtotime(date(MYSQL_DATE, time())."-7 day");
    $created_at = date(MYSQL_DATE, $created_at);
  
    $query = "SELECT h.*, cities.name city, streets.name street, COUNT(requests.id) request_count
    FROM houses h 
    LEFT JOIN requests on requests.house_id = h.id
    LEFT JOIN cities on cities.id = h.city_id
    LEFT JOIN streets on streets.id = h.street_id
    WHERE active = true and (handled_on is null or handled_on > '".mysql_real_escape_string($created_at)."' or subscriber_id is null)";
    if (!$include_closed) $query.= " and handled_on is null";
  
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
      if (isset($selected_region['city_district_id'])) $query.= " AND h.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
      if (isset($selected_region['city_microdistrict_id'])) $query.= " AND h.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
    }
  
    $query.= " GROUP BY h.id
    ORDER BY cities.name, streets.name, h.house, h.building";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new House($row);
    unset($rows);
    
    return $result;
  }

  static function high_priority_requests() {
    $query = 'SELECT r.*, rt.name request_type,
      cities.id city_id, cities.name city, streets.id street_id, streets.name street, h.id house_id, h.house, h.building,
      h.apartment_schema, h.apartments, h.entrances, h.floors, h.is_online
    FROM requests r
    JOIN request_types rt on rt.id = r.request_type_id
    JOIN houses h on h.id = r.house_id
    LEFT JOIN streets on streets.id = h.street_id
    LEFT JOIN cities on cities.id = streets.city_id
    WHERE handled_on is null and request_priority_id <= 1
    ORDER BY cities.name, streets.name, h.house, h.building, apartment';

    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Request($row);
    unset($rows);
    
    return $result;
  }

  static function assign_requests($subscriber) {
    global $factory;
    $factory->connection->execute("UPDATE requests 
      SET subscriber_id = '".mysql_real_escape_string($subscriber['id'])."'
      WHERE subscriber_id is null and house_id = '".mysql_real_escape_string($subscriber['house_id'])."' and apartment = '".mysql_real_escape_string($subscriber['apartment'])."'");
  }
  
  static function request_stats($operation, $condition, $city_id = null, $days = null, $user_id = null)
  {
    $query = "select ";
    if ($operation == 'count') {
      $query .= "count(r.id)";
    } else if ($operation == 'quality' && $condition == 'closed') {
      $query .= "count(r.id) cnt, AVG(datediff(r.handled_on, r.created_at)) avg, MAX(datediff(r.handled_on, r.created_at)) max";
    } else if ($operation == 'quality' && $condition != 'closed') {
      $query .= "count(r.id) cnt, AVG(datediff(NOW(), r.created_at)) avg, MAX(datediff(NOW(), r.created_at)) max";
    } else if ($operation == 'details') {
      $query .= "count(r.id) `count`, s.name street, h.house, h.building, request_type_id, rt.name request_type";
    }
    
    $query .= " from requests r
      join houses h on r.house_id = h.id";
    
    if ($operation == 'details') {
      $query .= " join streets s on h.street_id = s.id
        join request_types rt on r.request_type_id = rt.id";
    }
    
    $query .= " where r.active = true";
    if ($city_id) $query .= "  and h.city_id = '".mysql_real_escape_string($city_id)."'";

    if ($condition == 'open') {
      $query .= " and r.handled_on is null";
    } else if ($condition == 'online') {
      $query .= " and r.handled_on is null and h.is_online = true";
    } else if ($condition == 'offline') {
      $query .= " and r.handled_on is null and h.is_online = false";
    } else {
      $date = date(MYSQL_DATE, strtotime('- '.($days - 1).' days'));
      if ($condition == 'new') $date.= ' 00:00:00';
      $date = mysql_real_escape_string($date);
      
      if ($condition == 'new') {
        $query .= " and r.created_at >= '".$date."'";
        if ($user_id) $query .= "  and r.created_by = '".mysql_real_escape_string($user_id)."'";
      } else if ($condition == 'closed') {
        $query .= " and r.handled_on >= '".$date."'";
        if ($user_id) $query .= "  and r.updated_by = '".mysql_real_escape_string($user_id)."'";
      } else if ($condition == 'handled') {
        $query .= " and r.handled_on >= '".$date."'";
        if ($user_id) $query .= "  and r.handled_by = '".mysql_real_escape_string($user_id)."'";
      }
    }

    if ($operation == 'details') {
      $query .= " group by r.request_type_id, r.house_id
        order by r.request_type_id, count(r.id) DESC";
    }

    global $factory;    
    
    $result;
    if ($operation == 'count') {
      $result = $factory->connection->execute_scalar($query);
    } else if ($operation == 'quality') {
      $result = $factory->connection->execute_row($query);
    } else if ($operation == 'details') {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Request($row);
      unset($rows);
    }
    return $result;
  }
  
  function validate()
  {
    global $factory;
  
    if (isset($this['handled_on'])) {
      
      if ($this['handled_by'] == null) $this->errors['handled_by'] = ERROR_BLANK;    
      if ($this['handled_comment'] == null) $this->errors['handled_comment'] = ERROR_BLANK;    
    
    } else if ($this['id'] == null) {
    
      if ($this['house_id'] == null) $this->errors['address'] = ERROR_BLANK;    
      
      if ($this['apartment'] == null) $this->errors['address'] = ERROR_BLANK;
      else if (!is_numeric($this['apartment'])) $this->errors['address'] = ERROR_NUMBER;
      else {
        $house = House::load($this['house_id']);
        $schema = House::generate_schema($house);
        if (!House::has_arartment($schema,(int)$this['apartment'])) $this->errors['address'] = ERROR_NOEXIST;
      }
      
      if (!isset($this->errors['address'])) {
        $id = isset($this['id']) ? $this['id'] : null;
        if ($factory->connection->execute_scalar("SELECT id FROM requests
            WHERE house_id = '".mysql_real_escape_string($this['house_id'])."' and apartment = '".mysql_real_escape_string($this['apartment'])."' and active = true and handled_by is null".($id != null ? " and id <> '".mysql_real_escape_string($id)."'": '')) != null)
            $this->errors['address'] = ERROR_EXIST;
      }
    
      if ($this['request_type_id'] == null) $this->errors['request_type_id'] = ERROR_BLANK;    
      if ($this['request_priority_id'] == null) $this->errors['request_priority_id'] = ERROR_BLANK;    
    }
    
    if ($this['cell_phone'] != null && !preg_match(CELL_PHONE_FORMAT, $this['cell_phone'])) $this->errors['cell_phone'] = ERROR_FORMAT;
    if ($this['home_phone'] != null && !preg_match(HOME_PHONE_FORMAT, $this['home_phone'])) $this->errors['home_phone'] = ERROR_FORMAT;
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'request_type_id',
        'request_priority_id',
        'house_id',
        'apartment',
        'home_phone',
        'cell_phone',
        'comment',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
  
    if ($this['home_phone']) $this['home_phone'] = prepare_phone($this['home_phone']);
    if ($this['cell_phone']) $this['cell_phone'] = prepare_phone($this['cell_phone']);
    
    if ($id == null) {
      if ($this['house_id'] != '' && $this['apartment'] != '') {
        $subscriber = Subscriber::load_by_address($this['house_id'], $this['apartment']);
        if ($subscriber) $this['subscriber_id'] = $subscriber['id'];
      }
    }
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  function load_attributes_for_subscriber($subscriber, $attributes)
  {
    $this->attributes = array();
    $this['id'] = null;

    $this['subscriber_id'] = $subscriber['id'];
    $this['home_phone'] = $subscriber['home_phone'];
    $this['cell_phone'] = $subscriber['cell_phone'];
    $this['house_id'] = $subscriber['house_id'];
    $this['apartment'] = $subscriber['apartment'];

    $attributes = $this->allow_attributes($attributes,
      array(
        'request_type_id',
        'request_priority_id',
        'comment',
      )
    );
    $this->map_attributes($attributes);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  function load_attributes_for_close($id = null)
  {
    $this->attributes = array();
    
    $this['id'] = $id;
    $this['handled_by'] = get_field_value('request', 'handled_by');
    $this['handled_on'] = date(MYSQL_TIME, time());
    $this['handled_comment'] = get_field_value('request', 'handled_comment');
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  static function workers()
  {
    global $factory;
    
    $query = "SELECT id, CONCAT_WS(' ', first_name, last_name) name FROM workers WHERE active = true AND show_requests ORDER BY name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Worker($row);
    unset($rows);
    
    return $result;
  }
  
  static function daily_report($type, $from_date, $to_date)
  {
    $from_date = prepare_date($from_date);
    $to_date = prepare_date($to_date);
  
    $sql = "SELECT r.*, rt.name request_type, ";
    
    if ($type == 'creator') {
      $sql.= "DATE(r.created_at) actual_date,";
    } else if ($type == 'updator') {
      $sql.= "DATE(r.updated_at) actual_date,";
    } else if ($type == 'worker') {
      $sql.= "r.handled_on actual_date,";
    } else {
      $sql.= "null actual_date,";
    }
    
    $sql .= " cities.id city_id, cities.name city, streets.id street_id, streets.name street, h.id house_id, h.house, h.building,
      cu.name creator, uu.name updator,
      CONCAT(w.first_name,' ',w.last_name) worker,
      IF(r.handled_on IS NULL, NULL, datediff(r.handled_on, r.created_at)) wait_time
      FROM requests r
      JOIN request_types rt on rt.id = r.request_type_id
      JOIN houses h on h.id = r.house_id
      LEFT JOIN streets on streets.id = h.street_id
      LEFT JOIN cities on cities.id = streets.city_id
      LEFT JOIN users cu ON r.created_by = cu.id
      LEFT JOIN users uu ON r.updated_by = uu.id
      LEFT JOIN workers w on w.id = r.handled_by ";
      
    if ($type == 'creator') {
      $sql.= "WHERE r.created_at >= '".mysql_real_escape_string($from_date)." 00:00:00' AND r.created_at <= '".mysql_real_escape_string($to_date)." 23:59:59' ";
    } else if ($type == 'updator') {
      $sql.= "WHERE r.updated_at >= '".mysql_real_escape_string($from_date)." 00:00:00' AND r.updated_at <= '".mysql_real_escape_string($to_date)." 23:59:59' AND handled_on IS NOT NULL ";
    } else if ($type == 'worker') {
      $sql.= "WHERE r.handled_on >= '".mysql_real_escape_string($from_date)."' AND r.handled_on <= '".mysql_real_escape_string($to_date)."' ";
    } else {
      $sql.= "WHERE 1 <> 1 ";
    }
    
    if ($type == 'creator') {
      $sql.= "ORDER BY actual_date DESC, creator, city, street, house, building";
    } else if ($type == 'updator') {
      $sql.= "ORDER BY actual_date DESC, updator, city, street, house, building";
    } else if ($type == 'worker') {
      $sql.= "ORDER BY actual_date DESC, worker, city, street, house, building";
    }
  
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($sql);
    while($row = mysql_fetch_array($rows))
      $result[] = new TimeEntry($row);
    unset($rows);
    return $result;
  }
  
  function name() {
    return $this->self_billing() ? format_name($this) : 'Кооперативный абонент';
  }
 
  function self_billing() {
    return !$this->house_billing();
  }
  
  function house_billing() {
    return $this['house_billing'] !== null;
  }
}

?>