<?php
class Organization extends ModelBase {
  var $table_name = 'organizations';
  public static $page_size = 10;

  static function generate_query($operation, $id = null, $filter = null, $page = 1, $limit = null)
  {
    $query = $operation == 'count' ? "SELECT count(1)" : "SELECT o.*";
    if ($id) $query.= ", cu.name creator, uu.name updator"; 
    $query.= " FROM organizations o";
    if ($id) $query.= " LEFT JOIN users cu ON o.created_by = cu.id LEFT JOIN users uu ON o.updated_by = uu.id";
    $query.= " WHERE 1=1".
    ($id != null ? " AND o.id = ".mysql_real_escape_string($id) : " AND active = true");
    
    if ($filter) {
      $cond = array();
      
      $words = explode(' ', $filter);
      foreach($words as $word) {
        $word = str_replace(',', '', $word);
        $safe_word = mysql_real_escape_string($word);
        $numeric = is_numeric($word);
      
        $c = array();
        $c[] = "o.name LIKE '%".$safe_word."%'";
        $c[] = "o.keywords LIKE '%".$safe_word."%'";
        $c[] = "o.requisites LIKE '%".$safe_word."%'";

        $cond[] = "(".implode(' OR ', $c).")";
      }
      
      $query.= ' AND (('.implode(' AND ', $cond).') OR ('.implode(' OR ', $c).'))';
    }
    
    $query.= " ORDER BY o.name";
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null)
  {
    global $factory;
    $query = Organization::generate_query('count', null, $filter, 0, 0);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function load($id = null, $filter = null, $page = 1, $limit = null)
  {
    global $factory;
    $query = Organization::generate_query('select', $id, $filter, $page, $limit);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Organization($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Organization($row);
      unset($rows);
    }
    
    return $result;
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'name',
        'keywords',
        'requisites',
      )
    );
    
    $this->map_attributes($attributes);
  
    $this['id'] = $id;

    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  function validate()
  {
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;
  }
}

?>