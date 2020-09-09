<?php
class Subscriber extends ModelBase {
  var $table_name = 'subscribers';
  public static $page_size = 10;
  public static $debt_limit = 2; // > 2 month's debt

  static function generate_query($operation, $id = null, $filter = null, $page = 1, $limit = null, $mode = null)
  {
    $query = 'SELECT ';
    if ($operation == 'count') $query.= "count(s.id)";
    else if ($operation == 'sum') $query.= "sum(ba.actual_balance)";
    else if ($operation == 'indexes') $query.= "s.id";
    else $query.= "s.*, 
      cities.id city_id, cities.name city,
      streets.id street_id, streets.name street,
      houses.id house_id, houses.house, houses.building,
      ba.id billing_account_id, ba.lookup_code, ba.actual_balance, s.billing_tariff_id,
      bt.name billing_tariff, bt.with_justification, bt.with_ends_on, bah.id house_billing,
      tr.name termination_reason, c.name competitor";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM subscribers s 
      LEFT JOIN houses ON houses.id = s.house_id
      LEFT JOIN houses bah ON bah.billing_account_id = s.billing_account_id
      LEFT JOIN streets ON streets.id = houses.street_id
      LEFT JOIN cities ON cities.id = streets.city_id
      LEFT JOIN billing_accounts ba ON s.billing_account_id = ba.id
      LEFT JOIN billing_tariffs bt ON s.billing_tariff_id = bt.id
      LEFT JOIN termination_reasons tr ON tr.id = s.termination_reason_id
      LEFT JOIN competitors c ON c.id = s.competitor_id ";
    if ($id) $query.= " LEFT JOIN users cu ON s.created_by = cu.id LEFT JOIN users uu ON s.updated_by = uu.id"; 
    $query.=" WHERE ";
    
    if ($mode == 'relatives')
      $query.= ($id != null ? " s.id <> '".mysql_real_escape_string($id)."'" : " 1=1");
    else
      $query.= ($id != null ? " s.id = '".mysql_real_escape_string($id)."'" : " 1=1");

    if ($id == null)
      if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
        $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
        if (isset($selected_region['city_district_id'])) $query.= " AND houses.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
        if (isset($selected_region['city_microdistrict_id'])) $query.= " AND houses.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
      }
    
    if ($filter && $mode != 'relatives') {
      $cond = array();
      
      $words = explode(' ', $filter);
      foreach($words as $word) {
        $word = str_replace(',', '', $word);
        $safe_word = mysql_real_escape_string($word);
        $numeric = is_numeric($word);
      
        $c = array();
        $c[] = "s.first_name LIKE '%".$safe_word."%'";
        $c[] = "s.last_name LIKE '%".$safe_word."%'";
        $c[] = "s.middle_name LIKE '%".$safe_word."%'";
        $c[] = "cities.name LIKE '%".$safe_word."%'";
        $c[] = "streets.name LIKE '%".$safe_word."%'";        
        
        if (!$numeric) {
          preg_match('/^(\d+)(([\\/\\\\]([^\-\\/\\\\]+))|([^\d\-\\/\\\\]+))?(-(\d+))?$/', $word, $address);
          $ac = array();
          if (isset($address[1]) && $address[1]) $ac[] = "houses.house = '".mysql_real_escape_string($address[1])."'";
          if (isset($address[4]) && $address[4]) $ac[] = "houses.building = '".mysql_real_escape_string($address[4])."'";
          if (isset($address[5]) && $address[5]) $ac[] = "houses.building = '".mysql_real_escape_string($address[5])."'";
          if (isset($address[7]) && $address[7]) $ac[] = "s.apartment = '".mysql_real_escape_string($address[7])."'";
          if ($ac) $c[] = '('.implode(' AND ', $ac).')';
        }
        
        if ($numeric) {
          $c[] = "houses.house = '".$safe_word."'";
          $c[] = "s.apartment = '".$safe_word."'";
          $c[] = "ba.lookup_code = '".$safe_word."'";
        }

        $cond[] = "(".implode(' OR ', $c).")";
      }

      $phone_number = prepare_phone_for_search($filter);
      if ($phone_number) {
        $c = array();
        $c[] = "home_phone LIKE '".mysql_real_escape_string($phone_number)."'";
        $c[] = "cell_phone LIKE '".mysql_real_escape_string($phone_number)."'";
        $query.= ' AND (('.implode(' AND ', $cond).') OR ('.implode(' OR ', $c).'))';
      } else {
        $c = array();
        $c[] = "bt.name = '".mysql_real_escape_string($filter)."'";
        $query.= ' AND (('.implode(' AND ', $cond).') OR ('.implode(' OR ', $c).'))';
      }
    }
    
    if ($mode == 'arrears') $query.= " AND -ba.actual_balance > bt.subscription_fee * ".prepare_float(Subscriber::$debt_limit)." AND bt.subscription_fee > 0 AND s.active = true";
    if ($filter && $mode == 'relatives') $query.= ' AND ('.$filter.')';
    
    if ($operation == 'select' || $operation == 'indexes') $query.= " ORDER BY s.last_name, s.first_name, s.middle_name, streets.name, houses.house, houses.building, s.apartment";    
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null, $arrears = false)
  {
    global $factory;
    $query = Subscriber::generate_query('count', null, $filter, 0, 0, $arrears ? 'arrears' : null);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function total_debt()
  {
    global $factory;
    $query = Subscriber::generate_query('sum', null, null, 0, 0, 'arrears');
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function load($id = null, $filter = null, $page = 1, $limit = null, $arrears = false)
  {
    global $factory;
    $query = Subscriber::generate_query('select', $id, $filter, $page, $limit, $arrears ? 'arrears' : null);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Subscriber($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Subscriber($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function arrear_indexes($filter)
  {
    global $factory;
    $query = Subscriber::generate_query('indexes', null, $filter, null, null, true);
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = $row['id'];
    unset($rows);
    
    return $result;
  }
  
  static function total($city_id)
  {
    global $factory;
    
    return $factory->connection->execute_scalar("
      SELECT count(s.id) 
      FROM subscribers s 
      JOIN houses h on h.id = s.house_id
      JOIN streets ss on ss.id = h.street_id
      WHERE s.active = true AND ss.city_id = '".mysql_real_escape_string($city_id)."'");
  }

  static function filtered_subscribers($house_id)
  {
    global $factory;
  
    $result = array();
    $rows = $factory->connection->execute("SELECT apartment, s.id FROM subscribers s
    JOIN billing_accounts ba ON s.billing_account_id = ba.id
    JOIN billing_tariffs bt ON bt.id = s.billing_tariff_id
    WHERE ba.active = true and house_id = '".mysql_real_escape_string($house_id)."' and bt.filter = true
    ORDER BY apartment");
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    return $result;
  }
  
  static function good_subscribers($house_id)
  {
    return Subscriber::load_subscribers_by_billing_filter($house_id, "ba.actual_balance >= 0 OR bt.subscription_fee <= 0");
  }

  static function risk_subscribers($house_id)
  {
    return Subscriber::load_subscribers_by_billing_filter($house_id, "ba.actual_balance < 0 AND ba.actual_balance >= -bt.subscription_fee * ".prepare_float(Subscriber::$debt_limit)." AND bt.subscription_fee > 0");
  }
  
  static function evil_subscribers($house_id)
  {
    return Subscriber::load_subscribers_by_billing_filter($house_id, "ba.actual_balance < -bt.subscription_fee * ".prepare_float(Subscriber::$debt_limit)." AND bt.subscription_fee > 0");
  }
  
  static function load_subscribers_by_billing_filter($house_id, $condition)
  {
    global $factory;
    
    $result = array();
    $rows = $factory->connection->execute("SELECT apartment, s.id FROM subscribers s
    JOIN billing_accounts ba ON s.billing_account_id = ba.id
    JOIN billing_tariffs bt ON bt.id = s.billing_tariff_id
    WHERE ba.active = true and house_id = '".mysql_real_escape_string($house_id)."' and (".$condition.")
    ORDER BY apartment");
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    return $result;
  }
  
  static function total_apartments($city_id)
  {
    global $factory;
    
    $val = $factory->connection->execute_scalar("
      SELECT sum(h.apartments) FROM houses h
      JOIN streets s on s.id = h.street_id
      WHERE s.city_id = '".mysql_real_escape_string($city_id)."'");
    return $val == null ? 0 : $val;
  }
  
  static function online_apartments($city_id)
  {
    global $factory;
    
    $val = $factory->connection->execute_scalar("
      SELECT sum(h.apartments)
      FROM houses h
      JOIN streets s on s.id = h.street_id
      WHERE s.city_id = '".mysql_real_escape_string($city_id)."' AND h.is_online = true");
    return $val == null ? 0 : $val;
  }
  
  static function new_subscribers($days, $city_id = null, $user_id = null)
  {
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
    
    $sql = "SELECT count(s.id)
      FROM subscribers s
      JOIN houses h ON h.id = s.house_id
      JOIN streets ss ON ss.id = h.street_id
      WHERE s.created_at >= '".$datetime."'";
            
    if ($city_id) $sql .= " AND ss.city_id = '".mysql_real_escape_string($city_id)."'";
    if ($user_id) $sql .= " AND s.created_by = '".mysql_real_escape_string($user_id)."'";
    
    global $factory;
    return $factory->connection->execute_scalar($sql);
  }
  
  static function new_subscribers_stats($days, $city_id)
  {
    global $factory;
    
    $datetime = date(MYSQL_DATE, strtotime('- '.($days - 1).' days')).' 00:00:00';
  
    $result = array();
    $rows = $factory->connection->execute("
      SELECT count(s.id) `count`, ss.name street, h.house, h.building
      FROM subscribers s
      JOIN houses h ON h.id = s.house_id
      JOIN streets ss ON ss.id = h.street_id
      WHERE ss.city_id = '".mysql_real_escape_string($city_id)."' AND
            s.created_at >= '".$datetime."'
      GROUP BY s.house_id
      ORDER BY count(s.id) DESC
    LIMIT 20");
    
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    return $result;
  }
  
  static function load_by_address($house_id, $adartment)
  {
    global $factory;
    
    $id = $factory->connection->execute_scalar("SELECT id FROM subscribers
        WHERE house_id = '".mysql_real_escape_string($house_id)."' and apartment = '".mysql_real_escape_string($adartment)."' and active = true");
    
    return $id ? Subscriber::load($id) : null;
  }
  
  static function load_for_house($house_id)
  {
    global $factory;
    
    $result = array();
    $rows = $factory->connection->execute(
"SELECT id, apartment, billing_account_id, billing_tariff_id, tariff_justification, tariff_ends_on
FROM subscribers
WHERE house_id = '".mysql_real_escape_string($house_id)."' AND active = true
ORDER BY apartment"
    );
    
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    return $result;
  }
  
  function validate()
  {
    global $factory;
  
    if ($this['last_name'] == null) $this->errors['last_name'] = ERROR_BLANK;
    if ($this['first_name'] == null) $this->errors['first_name'] = ERROR_BLANK;
    if ($this['middle_name'] == null) $this->errors['middle_name'] = ERROR_BLANK;
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
      if ($factory->connection->execute_scalar("SELECT id FROM subscribers
          WHERE house_id = '".mysql_real_escape_string($this['house_id'])."' and apartment = '".mysql_real_escape_string($this['apartment'])."' and active = true".($id != null ? " and id <> '".mysql_real_escape_string($id)."'": '')) != null)
          $this->errors['address'] = ERROR_EXIST;
    }
    
    if ($this['cell_phone'] != null && !preg_match(CELL_PHONE_FORMAT, $this['cell_phone'])) $this->errors['cell_phone'] = ERROR_FORMAT;
    if ($this['home_phone'] != null && !preg_match(HOME_PHONE_FORMAT, $this['home_phone'])) $this->errors['home_phone'] = ERROR_FORMAT;
  
    if (has_access('subscriber/passport')) {
      if (!$this['passport_identifier']) $this->errors['passport'] = ERROR_BLANK;
      if (!$this['passport_issued_by']) $this->errors['passport'] = ERROR_BLANK;
      if (!$this['passport_issued_on']) $this->errors['passport'] = ERROR_BLANK;
      else if (parse_db_date($this['passport_issued_on']) >= time()) $this->errors['passport'] = ERROR_FUTURE;
      else if (parse_db_date($this['passport_issued_on']) < 0 ) $this->errors['passport'] = ERROR_PAST;
    } else {
      unset($this['passport_identifier']);
      unset($this['passport_issued_by']);
      unset($this['passport_issued_on']);
    }

    if ($this['id'] == null) {
      if ($this['starts_on'] == null) $this->errors['starts_on'] = ERROR_BLANK;
      else if (parse_db_date($this['starts_on']) >= time()) $this->errors['starts_on'] = ERROR_FUTURE;
      else if (parse_db_date($this['starts_on']) <= time() - 30 * 24 * 3600) $this->errors['starts_on'] = ERROR_PAST;
    }
    
    if ($this->attributes['billing_tariff_id'] == null) $this->errors['billing_tariff_id'] = ERROR_BLANK;
  }

  function valid_for_house_billing()
  {
    $this->errors = array();
    
    if ($this->attributes['billing_tariff_id'] == null) $this->errors['billing_tariff_id'] = ERROR_BLANK;
    
    return count($this->errors) == 0;
  }
  
  function valid_termination()
  {
    $this->errors = array();
    
    if ($this['termination_reason_id'] == null) $this->errors['termination_reason_id'] = ERROR_BLANK;
  
    return count($this->errors) == 0;
  }

  
  function relatives()
  {
    $filter = array();
  
    if ($this['cell_phone']) $filter[] = "s.cell_phone = '".mysql_real_escape_string($this['cell_phone'])."'";
    if ($this['home_phone']) $filter[] = "s.home_phone = '".mysql_real_escape_string($this['home_phone'])."'";
    $filter[] = "s.passport_identifier = '".mysql_real_escape_string($this['passport_identifier'])."'";
    $filter[] = "ba.lookup_code = '".mysql_real_escape_string($this['lookup_code'])."'";
    $filter[] = "s.house_id = '".mysql_real_escape_string($this['house_id'])."' AND s.apartment = '".mysql_real_escape_string($this['apartment'])."'";
    
    $filter = implode(' OR ', $filter);
    
    $query = Subscriber::generate_query('select', $this['id'], $filter, null, null, 'relatives');
    
    global $factory;
    $rows = $factory->connection->execute($query);
    
    $result = array();
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'billing_tariff_id',
        'last_name',
        'first_name',
        'middle_name',
        'passport_identifier',
        'passport_issued_by',
        'passport_issued_on',
        'home_phone',
        'cell_phone',
        'house_id',
        'apartment',
        'tariff_justification',
        'tariff_ends_on',
        'starts_on',
        'allow_calls',
        'allow_sms',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['last_name'] = prepare_name($this['last_name']);
    $this['first_name'] = prepare_name($this['first_name']);
    $this['middle_name'] = prepare_name($this['middle_name']);
    $this['passport_identifier'] = $this['passport_identifier'] ? mb_strtoupper($this['passport_identifier'], "UTF-8") : $this['passport_identifier'];
    $this['passport_issued_on'] = prepare_date($this['passport_issued_on']);
    $this['home_phone'] = prepare_phone($this['home_phone']);
    $this['cell_phone'] = prepare_phone($this['cell_phone']);
    $this['tariff_ends_on'] = prepare_date($this['tariff_ends_on']);

    if ($id != null) unset($this['starts_on']);
    else $this['starts_on'] = prepare_date($this['starts_on']);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  function load_attributes_for_termination($attributes, $id = null)
  {
    //$this->attributes = array();

    $attributes = $this->allow_attributes($attributes,
      array(
        'termination_reason_id',
        'competitor_id',
        'termination_comment',
      )
    );
    
    $this->map_attributes($attributes);
  
    $this['id'] = $id;
    $this['ends_on'] = date(MYSQL_DATE, time());
    $this['competitor_id'] = $this['competitor_id'] ? intval($this['competitor_id']) : null;
    $this['active'] = 0;
  
    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  function billing_tariff()
  {
    return $this['billing_tariff_id'] ? BillingTariff::load($this['billing_tariff_id']) : null;
  }
    
  function terminate()
  {
    $this->update();
    
    $s = Subscriber::load($this['id']);
    $billing_account = BillingAccount::load($s['billing_account_id']);
    $billing_account->change_actual_balance(0, 'termination', $this['ends_on'], 'Отключение абонента', $this['id'], null, null);
    
    global $factory;
    $factory->deactivate('billing_accounts', $billing_account['id']);
  }
  
  static function reactivate($id)
  {
    global $factory;
  
    $s = new Subscriber();
    $s['id'] = $id;
    $s['ends_on'] = null;
    $s['active'] = 1;
    $s['termination_reason_id'] = null;
    $s['competitor_id'] = null;
    $s['termination_comment'] = null;
    $s->add_userstamp();
    $s->add_timestamp();
    $s->update();

    $s = Subscriber::load($id);
    $billing_account = BillingAccount::load($s['billing_account_id']);
    if (!$billing_account['active']) {
      $factory->reactivate('billing_accounts', $s['billing_account_id']);
      $billing_account = BillingAccount::load($s['billing_account_id']);
      $billing_account->change_actual_balance(0, 'reactivation', date(MYSQL_DATE, time()), 'Восстановление абонента', $id, null, null);
    }
  }

  static function termination_reasons()
  {
    global $factory;
  
    $result = array();
    $rows = $factory->connection->execute("SELECT * FROM termination_reasons WHERE active = true ORDER BY `order`, name");
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    return $result;
  }

  static function regions() {
    $query = "SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, COUNT(1) online_s, SUM(bt.subscription_fee) subscription_fee
FROM subscribers s
JOIN billing_accounts ba ON s.billing_account_id = ba.id
JOIN billing_tariffs bt ON s.billing_tariff_id = bt.id
JOIN houses h ON s.house_id = h.id
WHERE s.active = true
GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function count_terminations() {
    $query = "select sum(t.sub_ends) sub_ends, sum(t.req_ends) req_ends from
    
    (select count(id) sub_ends, 0 req_ends
    from subscribers
    where ends_on = CURRENT_DATE()
      and active = false

    union all
    
    select 0 sub_ends, count(id) req_ends
    from requests
    where date(created_at) = CURRENT_DATE()
      and active = true
      and request_type_id = 3
    ) t";
  
    global $factory;
    return new Subscriber($factory->connection->execute_row($query));
  }
  
  static function report_delta($from_date, $to_date) {
    global $factory;
    
    $query = "select t.actual_date, sum(t.sub_starts) sub_starts, sum(t.sub_ends) sub_ends, sum(t.req_starts) req_starts, sum(t.req_ends) req_ends from (
    
    select starts_on actual_date, count(id) sub_starts, 0 sub_ends, 0 req_starts, 0 req_ends
    from subscribers
    where starts_on >= '".mysql_real_escape_string($from_date)."' 
      and starts_on <= '".mysql_real_escape_string($to_date)."'
      and active = true
    group by actual_date
    
    union all
    
    select ends_on actual_date, 0 sub_starts, count(id) sub_ends, 0 req_starts, 0 req_ends
    from subscribers
    where ends_on >= '".mysql_real_escape_string($from_date)."' 
      and ends_on <= '".mysql_real_escape_string($to_date)."'
      and active = false
    group by actual_date
    
    union all
    
    select date(created_at) actual_date, 0 sub_starts, 0 sub_ends, count(id) req_starts, 0 req_ends
    from requests
    where created_at >= '".mysql_real_escape_string($from_date)." 00:00:00' 
      and created_at <= '".mysql_real_escape_string($to_date)." 23:59:59'
      and active = true
      and request_type_id = 1
    group by actual_date
    
    union all
    
    select date(created_at) actual_date, 0 sub_starts, 0 sub_ends, 0 req_starts, count(id) req_ends
    from requests
    where created_at >= '".mysql_real_escape_string($from_date)." 00:00:00' 
      and created_at <= '".mysql_real_escape_string($to_date)." 23:59:59'
      and active = true
      and request_type_id = 3
    group by actual_date
    ) t
    group by actual_date DESC";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function report_starts($from_date, $to_date) {
    global $factory;
    
    $query = "SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, str.name street, s.house_id, h.house, h.building, 0 reqs, COUNT(1) subs
    FROM subscribers s
      JOIN houses h ON s.house_id = h.id
      JOIN streets str ON h.street_id = str.id
    WHERE starts_on >= '".mysql_real_escape_string($from_date)."' 
      AND starts_on <= '".mysql_real_escape_string($to_date)."'
      AND s.active = true
    GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id, h.id
    
    UNION ALL
    
    SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, str.name street, r.house_id, h.house, h.building, COUNT(1) reqs, 0 subs
    FROM requests r
      JOIN houses h ON r.house_id = h.id
      JOIN streets str ON h.street_id = str.id
    WHERE r.created_at >= '".mysql_real_escape_string($from_date)." 00:00:00' 
      AND r.created_at <= '".mysql_real_escape_string($to_date)." 23:59:59'
      AND r.active = true
      AND r.request_type_id = 1
    GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id, h.id
    
    ORDER BY subs+reqs DESC, street, house, building";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function report_ends($from_date, $to_date) {
    global $factory;
    
    $query = "SELECT city_id, city_district_id, city_microdistrict_id, street, house_id, house, building, SUM(reqs) reqs, SUM(subs) subs FROM
    
    (SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, str.name street, s.house_id, h.house, h.building, 0 reqs, COUNT(1) subs
    FROM subscribers s
      JOIN houses h ON s.house_id = h.id
      JOIN streets str ON h.street_id = str.id
    WHERE ends_on >= '".mysql_real_escape_string($from_date)."' 
      AND ends_on <= '".mysql_real_escape_string($to_date)."'
      AND s.active = false
    GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id, h.id
    
    UNION ALL
    
    SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, str.name street, r.house_id, h.house, h.building, COUNT(1) reqs, 0 subs
    FROM requests r
      JOIN houses h ON r.house_id = h.id
      JOIN streets str ON h.street_id = str.id
    WHERE r.created_at >= '".mysql_real_escape_string($from_date)." 00:00:00' 
      AND r.created_at <= '".mysql_real_escape_string($to_date)." 23:59:59'
      AND r.active = true
      AND r.request_type_id = 3
    GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id, h.id) t
    
    GROUP BY house_id
    ORDER BY subs+reqs DESC, street, house, building";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function report_term_details($house_id, $from_date, $to_date) {
    global $factory;
    
    $query = "SELECT ends_on, apartment, tr.name termination_reason, c.name competitor, termination_comment
    FROM subscribers s
    JOIN termination_reasons tr ON s.termination_reason_id = tr.id
    LEFT JOIN competitors c ON s.competitor_id = c.id
    WHERE s.ends_on >= '".mysql_real_escape_string($from_date)."' 
      AND s.ends_on <= '".mysql_real_escape_string($to_date)."'
      AND s.house_id = '".mysql_real_escape_string($house_id)."'
      AND s.active = false
    ORDER BY s.ends_on";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Subscriber($row);
    unset($rows);
    
    return $result;
  }
  
  static function report_term_reasons($from_date, $to_date) {
    $query = "select s.termination_reason_id, tr.name termination_reason, count(s.id) total_count
from subscribers s
join termination_reasons tr on s.termination_reason_id = tr.id
where s.ends_on >= '".mysql_real_escape_string($from_date)."' 
  and s.ends_on <= '".mysql_real_escape_string($to_date)."'
  and s.active = false
group by s.termination_reason_id
order by total_count DESC";
    
    global $factory;
    return $factory->connection->execute_table($query);
  }
  
  static function report_competitors($from_date, $to_date) {
    $query = "select s.competitor_id, c.name competitor, count(s.id) total_count
from subscribers s
join competitors c on s.competitor_id = c.id
where s.ends_on >= '".mysql_real_escape_string($from_date)."' 
  and s.ends_on <= '".mysql_real_escape_string($to_date)."'
  and s.active = false
group by s.competitor_id
order by total_count DESC";
    
    global $factory;
    return $factory->connection->execute_table($query);
  }
  
  static function occupied_address($id) {
    $query = "SELECT 1
      FROM subscribers s1
      JOIN subscribers s2 ON s1.house_id = s2.house_id AND s1.apartment = s2.apartment
      WHERE s2.active = true AND s1.id <> s2.id AND s1.id = '".mysql_real_escape_string($id)."'";

    global $factory;
    return $factory->connection->execute_scalar($query);
  }
  
  static function competitors_report() {
    $query = "select t.competitor_id, count(t.house_id) total, count(s_pas.id) lost, count(s_act.id) won, count(s_pas.id+s_act.id) returned from
(select competitor_id, house_id, apartment
from subscriber_note_types snt
join subscriber_notes sn on sn.subscriber_note_type_id = snt.id
where snt.competitor_id is not null
union
select competitor_id, house_id, apartment
from subscribers
where competitor_id is not null) t
left join subscribers s_act on s_act.house_id = t.house_id and s_act.apartment = t.apartment and s_act.active = true
left join subscribers s_pas on s_pas.house_id = t.house_id and s_pas.apartment = t.apartment and s_pas.active = false
group by t.competitor_id";

    global $factory;
    return $factory->connection->execute_table($query);
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