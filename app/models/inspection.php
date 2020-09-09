<?php
class Inspection extends ModelBase {
  var $table_name = 'inspections';
  public static $page_size = 10;
  
  static function generate_query($count_only, $id = null, $filter = null, $raw_filter = null, $sort_order = null, $page = 1, $limit = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(i.id)" : "i.*,
h.house, h.building,
CONCAT(w.first_name,' ',w.last_name) worker,
cities.name city, streets.name street";

    if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM inspections i
JOIN houses h ON h.id = i.house_id
JOIN workers w ON w.id = i.handled_by
JOIN streets on streets.id = h.street_id
JOIN cities on cities.id = h.city_id";

    if ($id) $query.= " LEFT JOIN users cu ON i.created_by = cu.id LEFT JOIN users uu ON i.updated_by = uu.id"; 
    $query.= " WHERE 1=1";
    if ($id != null) $query.= " and i.id = '".mysql_real_escape_string($id)."'";
    
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
    
    if (!$count_only) $query.= " ORDER BY ".($sort_order ? $sort_order : "i.id DESC");
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null, $raw_filter = null)
  {
    global $factory;
    $query = Inspection::generate_query(true, null, $filter, $raw_filter);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function first($id)
  {
    global $factory;
    $query = Inspection::generate_query(false, $id);
    $row = $factory->connection->execute_row($query);
    $result = $row ? new Inspection($row) : null;
    return $result;
  }
  
  static function all($filter = null, $raw_filter = null, $sort_order = null, $page = 1, $limit = null)
  {
    global $factory;
    $query = Inspection::generate_query(false, null, $filter, $raw_filter, $sort_order, $page, $limit);
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Inspection($row);
    unset($rows);
    
    return $result;
  }
  
  function subscriber_notes() {
    global $factory;
    $query = "SELECT sn.*, snt.name, snt.code, s.active
FROM subscriber_notes sn
JOIN subscriber_note_types snt ON sn.subscriber_note_type_id = snt.id
LEFT JOIN subscribers s ON sn.subscriber_id = s.id
WHERE sn.inspection_id = '".mysql_real_escape_string($this['id'])."'
ORDER BY snt.position, sn.apartment";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = $row;
    unset($rows);
    
    return $result;
  }
  
  function notes() {
    global $factory;
    $query = "SELECT apartment, subscriber_note_type_id
FROM subscriber_notes
WHERE inspection_id = '".mysql_real_escape_string($this['id'])."'";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows)) {
      $apartment = strval($row['apartment']);
      if (!isset($result[$apartment])) $result[$apartment] = array();
      $result[$apartment][] = strval($row['subscriber_note_type_id']);
    }
    unset($rows);
    
    return $result;
  }
  
  function amplifier_scans() {
    global $factory;
    $query = "SELECT *
FROM amplifier_scans
WHERE inspection_id = '".mysql_real_escape_string($this['id'])."'";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows)) {
      $result[$row['amplifier_id']] = $row;
    }
    unset($rows);
    
    return $result;
  }
}
?>