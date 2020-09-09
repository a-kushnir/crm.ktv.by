<?php
class House extends ModelBase {
  var $table_name = 'houses';
  var $house_competitors = array();
  public static $page_size = 10;
  
  static function generate_query($count_only, $id = null, $filter = null, $raw_filter = null, $sort_order = null, $page = 1, $limit = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(h.id)" : "h.*, cities.name city, streets.name street";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
     $query.= " FROM houses h";
    $query.= " LEFT JOIN cities on cities.id = h.city_id
    LEFT JOIN streets on streets.id = h.street_id";
    if ($id) $query.= " LEFT JOIN users cu ON h.created_by = cu.id LEFT JOIN users uu ON h.updated_by = uu.id"; 
    $query.= " WHERE 1=1";
    if ($id != null) $query.= " and h.id = '".mysql_real_escape_string($id)."'";
    
    if ($id == null)
      if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
        $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
        if (isset($selected_region['city_district_id'])) $query.= " AND h.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
        if (isset($selected_region['city_microdistrict_id'])) $query.= " AND h.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
      }

    if($raw_filter) $query.= " AND ".$raw_filter;
  
    if ($filter || $filter=='0') {
      $words = explode(' ', $filter);
      foreach($words as $word) {
        $word = str_replace(',', '', $word);
        $safe_word = mysql_real_escape_string($word);
        $numeric = is_numeric($word);
      
        $cond = array();

        if ($numeric) {
          $cond[] = "h.activation_fee = '".$safe_word."'";
        }
          
        if ($filter!='0') {
          $cond[] = "cities.name LIKE '%".$safe_word."%'";
          $cond[] = "streets.name LIKE '%".$safe_word."%'";

          $cond[] = strpos($word,'/') > 0 ?
            "CONCAT(h.house,'/',h.building) = '".$safe_word."'" :
            "h.house = '".$safe_word."'";

          $cond[] = "h.owner_name LIKE '%".$safe_word."%'";
          $cond[] = "h.owner_description LIKE '%".$safe_word."%'";
        }
        
        $query.= " and (".implode(' or ', $cond).")";
      }
    }
    
    if (!$count_only) $query.= " GROUP BY h.id";
    if (!$count_only) $query.= " ORDER BY ".($sort_order ? $sort_order : "cities.name, streets.name, h.house, h.building");
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null, $raw_filter = null)
  {
    global $factory;
    $query = House::generate_query(true, null, $filter, $raw_filter);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function online_count($city_id)
  {
    global $factory;
    $query = "select count(h.id) 
    from houses h
    JOIN streets s on s.id = h.street_id
    WHERE s.city_id = '".mysql_real_escape_string($city_id)."' AND h.is_online = true";
    return $factory->connection->execute_scalar($query);
  }  
  
  static function total_count($city_id)
  {
    global $factory;
    $query = "select count(h.id) 
    from houses h
    JOIN streets s on s.id = h.street_id
    WHERE s.city_id = '".mysql_real_escape_string($city_id)."'";
    return $factory->connection->execute_scalar($query);
  } 
  
  static function load($id = null, $filter = null, $raw_filter = null, $sort_order = null, $page = 1, $limit = null)
  {
    global $factory;
    $query = House::generate_query(false, $id, $filter, $raw_filter, $sort_order, $page, $limit);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new House($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new House($row);
      unset($rows);
    }
    
    return $result;
  }

  function count_subscribers()
  {
    global $factory;
    return $factory->connection->execute_scalar("SELECT COUNT(1) FROM subscribers WHERE house_id = '".mysql_real_escape_string($this['id'])."' AND active = true LIMIT 1");
  }
  
  function has_subscribers()
  {
    global $factory;
    return $factory->connection->execute_scalar("SELECT 1 FROM subscribers WHERE house_id = '".mysql_real_escape_string($this['id'])."' AND active = true LIMIT 1");
  }
  
  static function get_activation_fee($id)
  {
    global $factory;
    return $factory->connection->execute_scalar("SELECT activation_fee FROM houses WHERE id = '".mysql_real_escape_string($id)."'");
  }
  
  function validate()
  {
    global $factory;
  
    if ($this['apartment_schema']) {
      $schema = House::generate_schema($this);
      $si = House::schema_info($schema);
      $this['entrances'] = $si['entrances'];
      $this['floors'] = $si['floors'];
      $this['apartments'] = $si['apartments'];
    }
  
    if ($this['city_id'] == null) $this->errors['address'] = ERROR_BLANK;
    if ($this['street_id'] == null) $this->errors['address'] = ERROR_BLANK;
    
    if ($this['house'] == null) $this->errors['address'] = ERROR_BLANK;
    else if (!is_numeric($this['house'])) $this->errors['address'] = ERROR_NUMBER;
    else if ((int)$this['house'] <= 0) $this->errors['address'] = ERROR_LOW;
    
    if ($this['apartments'] == null) $this->errors['apartments'] = ERROR_BLANK; 
    else if (!is_numeric($this['apartments'])) $this->errors['apartments'] = ERROR_NUMBER;
    else if ((int)$this['apartments'] <= 0) $this->errors['apartments'] = ERROR_LOW;
    
    if ($this['floors'] == null) $this->errors['floors'] = ERROR_BLANK; 
    else if (!is_numeric($this['floors'])) $this->errors['floors'] = ERROR_NUMBER;
    else if ((int)$this['floors'] <= 0) $this->errors['floors'] = ERROR_LOW;
    
    if ($this['entrances'] == null) $this->errors['entrances'] = ERROR_BLANK; 
    else if (!is_numeric($this['entrances'])) $this->errors['entrances'] = ERROR_NUMBER;
    else if ((int)$this['entrances'] <= 0) $this->errors['entrances'] = ERROR_LOW;
    
    if (!isset($this->errors['apartments']) && !isset($this->errors['floors']) && !isset($this->errors['entrances'])) {
      if ((int)$this['apartments'] > (int)$this['floors'] * (int)$this['entrances'] * 100)
        $this->errors['apartments'] = ERROR_BIG;
    }
    
    if (!isset($this->errors['address'])) {
      $id = isset($this['id']) ? $this['id'] : null;
      if ($factory->connection->execute_scalar("SELECT id FROM houses".
      " WHERE city_id = '".mysql_real_escape_string($this['city_id'])."' and street_id = '".mysql_real_escape_string($this['street_id'])."'".
      " and house = '".mysql_real_escape_string($this['house'])."' and building = '".mysql_real_escape_string($this['building'])."'".
      ($id != null ? " and id <> '".mysql_real_escape_string($id)."'": '')) != null)
          $this->errors['address'] = ERROR_EXIST;
    }
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'city_id',
        'street_id',
        'house',
        'building',
        'apartments',
        'floors',
        'entrances',
        'apartment_schema',
        'owner_name',
        'owner_description',
        'activation_fee',
        'is_online',
        'has_competitors',
        'city_district_id',
        'city_microdistrict_id',
        'house_competitors',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['activation_fee'] = prepare_float($this['activation_fee']);
    if (!$this['city_district_id']) $this['city_district_id'] = null;
    if (!$this['city_microdistrict_id']) $this['city_microdistrict_id'] = null;
    
    $this->house_competitors = $this['house_competitors'];
    unset($this['house_competitors']);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  // requires apartment_schema OR apartments, entrances and floors
  static function generate_schema($house) {
    $schema = array();
    if (isset($house['apartment_schema']) && $house['apartment_schema'] != null || trim($house['apartment_schema']) != "") {
      // Custom Schema
      $apartment = 1;
      $entrances = explode("\n", $house['apartment_schema']);
      for($e = 0; $e < count($entrances); $e++) {
        $schema[] = array();
        $floors = explode(',', $entrances[$e]);
        for($f = 0; $f < count($floors); $f++) {

          if (strpos($floors[$f], ':')) {
            $floor_info = explode(':', $floors[$f]);
            $apartment = (int)$floor_info[0];
            $floor_aps = (int)$floor_info[1];
          } else {
            $floor_aps = (int)$floors[$f];
          }
          
          $schema[$e][] = array();
          for($a = 0; $a < $floor_aps; $a++) {
            $schema[$e][$f][] = $apartment;
            $apartment++;
          }        
        }
      }
    } else {
      // Default Schema
      $apartment = 1;
      $apartments_per_float = $house['apartments'] / ($house['entrances'] * $house['floors']);
      for($e = 0; $e < $house['entrances']; $e++) {
        $schema[] = array();
        for($f = 0; $f < $house['floors']; $f++) {
          $schema[$e][] = array();
          for($a = 0; $a < $apartments_per_float; $a++) {
            if ($apartment <= $house['apartments']) {
              $schema[$e][$f][] = $apartment;
              $apartment++;
            }
          }
        }
      }
    }
    return $schema;
  }

  static function has_arartment($schema, $apartment)
  {
    for($e = 0; $e < count($schema); $e++)
      for($f = count($schema[$e]) - 1; $f >= 0; $f--)
        for($a = 0; $a < count($schema[$e][$f]); $a++)
          if ($schema[$e][$f][$a] == $apartment) {
            return true;
          }
    return false;
  }

  static function search_arartment($schema, $apartment) {
    $si = array(
      'entrance' => null,
      'floor' => null,
    );
    
    for($e = 0; $e < count($schema); $e++)
      for($f = count($schema[$e]) - 1; $f >= 0; $f--)
        for($a = 0; $a < count($schema[$e][$f]); $a++)
          if ($schema[$e][$f][$a] == $apartment) {
            $si['entrance'] = $e + 1;
            $si['floor'] = $f + 1;
            return $si;
          }

    return $si;
  }
  
  static function schema_info($schema) {
    $si = array(
      'entrances' => count($schema),
      'floors' => 0,
      'apartments' => 0
    );
    
    for($e = 0; $e < count($schema); $e++) {
      if ($si['floors'] < count($schema[$e]))
        $si['floors'] = count($schema[$e]);
      for($f = count($schema[$e]) - 1; $f >= 0; $f--)
        $si['apartments'] += count($schema[$e][$f]);
    }
    
    return $si;
  }
 
  static function regions() {
    $query = "SELECT h.city_id, h.city_district_id, h.city_microdistrict_id, COUNT(1) total_h, COUNT(IF(is_online <> 0, 1, NULL)) online_h, SUM(h.apartments) total_s
    FROM houses h
    GROUP BY h.city_id, h.city_district_id, h.city_microdistrict_id";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new House($row);
    unset($rows);
    
    return $result;
  }
  
  static function competitors() {
    $query = "SELECT id, `name` FROM competitors ORDER by `name`";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new House($row);
    unset($rows);
    
    return $result;
  }
  
  function save_house_competitors()
  {
    global $factory;
    if ($this->house_competitors) {
      // Deletes old
      $ids = array();
      
      foreach($this->house_competitors as $id)
        $ids[] = mysql_real_escape_string($id);
      $ids = implode("','", $ids);
      
      $factory->connection->execute("DELETE FROM houses_competitors WHERE house_id = '".mysql_real_escape_string($this['id'])."' AND competitor_id not in ('".$ids."')");

      // Creates new
      $hcs = $this->load_house_competitors();
      
      $ids = array();
      foreach($this->house_competitors as $id)
        if (!in_array($id, $hcs)) {
          $factory->create('houses_competitors', 
            array(
              'house_id' => $this['id'],
              'competitor_id' => $id,
              'created_at' => date(MYSQL_TIME, time()),
            )
          );
        }
    } else {
      // Delete all
      $factory->connection->execute("DELETE FROM houses_competitors WHERE house_id = '".mysql_real_escape_string($this['id'])."'");
    }
  }
  
  function load_house_competitors() {
    $query = "SELECT competitor_id FROM houses_competitors WHERE house_id = '".mysql_real_escape_string($this['id'])."'";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = $row['competitor_id'];
    unset($rows);
    
    return $result;
  }
  
  function recalc_inspected_on() 
  {
    $query = "UPDATE houses h SET
inspected_on = (SELECT MAX(actual_date) FROM inspections i WHERE i.active = true AND i.house_id = h.id)
WHERE h.id = '".mysql_real_escape_string($this['id'])."'";

    global $factory;
    $factory->connection->execute_void($query);
  }
  
  static function competitors_report() {
    $query = "select hc.competitor_id, c.name, count(hc.id) houses, sum(h.apartments) apartments
    from houses_competitors hc
    join competitors c on c.id = hc.competitor_id
    join houses h on hc.house_id = h.id
    group by hc.competitor_id";

    global $factory;
    return $factory->connection->execute_table($query);
  }
  
  function update_subscribers($subscribers)
  {
    global $factory;
    $today = date(MYSQL_TIME, time());
    $billing_account = BillingAccount::load($this['billing_account_id']);
  
    // LOAD EXISTING
    $rows = $factory->connection->execute_table(
"SELECT id, apartment, billing_account_id
FROM subscribers
WHERE house_id = '".mysql_real_escape_string($this['id'])."' AND active = true"
    );
    
    $existing_subs = array();
    foreach ($rows as $row)
      $existing_subs[$row['apartment']] = $row;
  
    // DELETE
    $deleted = 0;
    foreach($subscribers as $apartment => $subscriber)
      if (!$subscriber['billing_tariff_id'] && 
          isset($existing_subs[$apartment]) && 
          $existing_subs[$apartment]['billing_account_id'] == $this['billing_account_id']) {

        $s = new Subscriber();
        $s['id'] = $existing_subs[$apartment]['id'];
        $s['ends_on'] = $today;
        $s['active'] = 0;
        $s->update();
        $deleted++;
        
        $billing_account->change_actual_balance(0, 'termination', $s['ends_on'], 'Отключение абонента', $s['id'], null, null);
        $s = Subscriber::load($s['id']);
        User::log_event('отключил абонента', $s->name().' '.format_address($s), url_for('subscribers','show',$s['id']));
      }
    
    // UPDATE
    $updated = 0;
    foreach($subscribers as $apartment => $subscriber)
      if ($subscriber['billing_tariff_id'] && 
          isset($existing_subs[$apartment]) && 
          $existing_subs[$apartment]['billing_account_id'] == $this['billing_account_id']) {

        $s = new Subscriber();
        $s['id'] = $existing_subs[$apartment]['id'];
        $s['billing_tariff_id'] = $subscriber['billing_tariff_id'];
        $s['tariff_justification'] = $subscriber['tariff_justification'];
        $s['tariff_ends_on'] = prepare_date($subscriber['tariff_ends_on']);
        $s->add_userstamp();
        $s->add_timestamp();
        
        $sub = Subscriber::load($s['id']);
        if (
          $s['billing_tariff_id'] != $sub['billing_tariff_id'] ||
          $s['tariff_justification'] != $sub['tariff_justification'] ||
          $s['tariff_ends_on'] != $sub['tariff_ends_on']
        ) {
          $s->update();
          
          $s = Subscriber::load($s['id']);
          User::log_event('отредактировал абонента', $s->name().' '.format_address($s), url_for('subscribers','show',$s['id']));
          
          $updated++;
        }
      }

    // INSERT
    $activation_fee = House::get_activation_fee($this['id']);
    
    $inserted = 0;
    foreach($subscribers as $apartment => $subscriber)
      if ($subscriber['billing_tariff_id'] && !isset($existing_subs[$apartment])) {
        $s = new Subscriber();
        $s['house_id'] = $this['id'];
        $s['apartment'] = $apartment;
        $s['billing_tariff_id'] = $subscriber['billing_tariff_id'];
        $s['tariff_justification'] = $subscriber['tariff_justification'];
        $s['tariff_ends_on'] = prepare_date($subscriber['tariff_ends_on']);
        $s['billing_account_id'] = $this['billing_account_id'];
        $s['starts_on'] = $today;
        $s['allow_calls'] = 0;
        $s['allow_sms'] = 0;
        $s->add_userstamp();
        $s->add_timestamp();
        $sub_id = $s->create();
        
        if (is_null($activation_fee)) {
          $billing_tariff = $s->billing_tariff();
          $activation_fee = $billing_tariff['activation_fee'];
        }
        
        $billing_account->change_actual_balance(-$activation_fee, 'activation_fee', $s['starts_on'], 'Плата за подключение', $sub_id, null, null);
        
        $s = Subscriber::load($s['id']);
        User::log_event('добавил абонента', $s->name().' '.format_address($s), url_for('subscribers','show',$s['id']));

        $inserted ++;
      }
      
    return array('deleted' => $deleted, 'updated' => $updated, 'inserted' => $inserted);
  }
  
}

?>