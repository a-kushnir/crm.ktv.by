<?php
class Camera extends ModelBase {
  var $table_name = 'cameras';
  
  static function first($id) {
    global $factory;
    $query = "SELECT * FROM cameras WHERE id = '".mysql_real_escape_string($id)."' LIMIT 1";
    $row = $factory->connection->execute_row($query);
    return $row ? new Camera($row) : null;
  }
  
  static function all() {
    global $factory;
    $query = "SELECT * FROM cameras ORDER BY name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Camera($row);
    unset($rows);
    
    return $result;
  }

  function validate()
  {
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;
    if ($this['url'] == null) $this->errors['url'] = ERROR_BLANK;
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'name',
        'url',
        'active',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    $this['active'] = $this['active'] ? 1 : 0;
  }
}
?>