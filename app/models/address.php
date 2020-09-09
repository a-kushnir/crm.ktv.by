<?php
class Address extends ModelBase {

  static function city_name($city_id)
  {
    global $factory;
    $query = "SELECT name FROM cities where id='".mysql_real_escape_string($city_id)."'";
    return $factory->connection->execute_scalar($query);
  }

  static function get_cities()
  {
    global $factory;
    
    $query = "SELECT * FROM cities order by name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }
  
  static function get_districts($city_id)
  {
    global $factory;
    
    $query = "SELECT * FROM city_districts where city_id='".mysql_real_escape_string($city_id)."' order by name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }
  
  static function get_microdistricts($city_district_id)
  {
    global $factory;
    
    $query = "SELECT * FROM city_microdistricts where city_district_id='".mysql_real_escape_string($city_district_id)."' order by name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }
  
  
  static function get_streets($city_id, $all_streets = false)
  {
    global $factory;
    
    $query = "SELECT DISTINCT streets.* 
    FROM streets
    ".($all_streets ? 'LEFT' : '')." JOIN houses ON streets.id = houses.street_id
    WHERE streets.city_id='".mysql_real_escape_string($city_id)."'";
    
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
      if (!$all_streets && isset($selected_region['city_district_id'])) $query.= " AND houses.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
      if (!$all_streets && isset($selected_region['city_microdistrict_id'])) $query.= " AND houses.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
    }
    
    $query.= " ORDER BY name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }
  
  static function get_houses($street_id)
  {
    global $factory;
    
    $query = "SELECT houses.*, IF(building <> '', CONCAT(house,'/',building), house) name 
    FROM houses
    JOIN streets ON streets.id = houses.street_id
    WHERE street_id='".mysql_real_escape_string($street_id)."'";
    
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $query.= " AND streets.city_id = '".mysql_real_escape_string($selected_region['city_id'])."'";
      if (isset($selected_region['city_district_id'])) $query.= " AND houses.city_district_id = '".mysql_real_escape_string($selected_region['city_district_id'])."'";
      if (isset($selected_region['city_microdistrict_id'])) $query.= " AND houses.city_microdistrict_id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'";
    }
    
    $query .= " ORDER BY house, building";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }

  static function regions() {
    $query = "SELECT c.id city_id, null city_district_id, null city_microdistrict_id, c.`name` city, null city_district, null city_microdistrict
FROM cities c

UNION

SELECT c.id city_id, cd.id city_district_id, null city_microdistrict_id, c.`name` city, cd.`name` city_district, null city_microdistrict
FROM cities c
LEFT JOIN city_districts cd ON c.id = cd.city_id

UNION

SELECT c.id city_id, cd.id city_district_id, cmd.id city_microdistrict_id, c.`name` city, cd.`name` city_district, cmd.`name` city_microdistrict
FROM cities c
LEFT JOIN city_districts cd ON c.id = cd.city_id
LEFT JOIN city_microdistricts cmd ON cd.id = cmd.city_district_id

ORDER BY city, city_district, city_microdistrict";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Address($row);
    unset($rows);
    
    return $result;
  }
}

?>