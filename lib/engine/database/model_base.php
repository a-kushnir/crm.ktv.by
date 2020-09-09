<?php
abstract class ModelBase extends ArrayObject {
  var $table_name;  
  var $attributes;
  var $errors;

  public function __construct($attributes = array())
  {
    $this->attributes = $attributes;
  }

  function valid() {
    $this->errors = array();
    $this->validate();
    return count($this->errors) == 0;
  }

  function is_new()
  {
    return !isset($this->attributes['id']) || $this->attributes['id'] == null;
  }
  
  function save()
  {
    if ($this->is_new())
      $this->create();
    else
      $this->update();
  }
  
  function create()
  {
    global $factory;
    
    $this->attributes['id'] = $factory->create($this->table_name, $this->attributes);
    return $this->attributes['id'];
  }
  
  function update($conditions = null)
  {
    global $factory;
    
    return $factory->update($this->table_name, $this->attributes['id'], $this->attributes, $conditions);
  }
  
  function deactivate()
  {
    global $factory;
    
    $id = $this->attributes['id'];
    
    $this->attributes = array();
    $this->attributes['id'] = $id;
    $this->attributes['active'] = 0;
    $this->add_userstamp();
    $this->add_timestamp();
    
    return $factory->update($this->table_name, $id, $this->attributes);
  }
  
  function destroy()
  {
    global $factory;
    
    return $factory->destroy($this->table_name, $this->attributes['id']);
  }
  
  function add_userstamp()
  {
    $value = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($this->is_new()) $this['created_by'] = $value;
    $this['updated_by'] = $value;
  }
  
  function add_timestamp()
  {
    $value = date(MYSQL_TIME, time());
    if ($this->is_new()) $this['created_at'] = $value;
    $this['updated_at'] = $value;
  }

  function allow_attributes($attributes, $allowed_names)
  {
    $result = array();
    
    foreach($attributes as $key => $value)
      if (in_array($key, $allowed_names))
        $result[$key] = $value;
    
    return $result;
  }
  
  function deny_attributes($attributes, $allowed_names)
  {
    $result = array();
    
    foreach($attributes as $key => $value)
      if (!in_array($key, $allowed_names))
        $result[$key] = $value;
    
    return $result;
  }
  
  function map_attributes($attributes)
  {
    foreach($attributes as $key => $value)
      $this[$key] = $value;
  }
  
  function offsetExists($offset) { return isset($this->attributes) && isset($this->attributes[$offset]); }
  function offsetGet($offset) { return isset($this->attributes) && isset($this->attributes[$offset]) ? $this->attributes[$offset] : null; }
  function offsetSet($offset, $value) { if(!isset($this->attributes)) $this->attributes = array(); $this->attributes[$offset] = $value; }
  function offsetUnset($offset) { if (isset($this->attributes)) unset($this->attributes[$offset]); }
}
?>