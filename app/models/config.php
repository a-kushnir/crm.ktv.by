<?php
class Config extends ModelBase {

  static function get($name)
  {
    global $factory;
    
    $query = "SELECT `value` FROM config WHERE `key`='".mysql_real_escape_string($name)."'";
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function set($name, $value)
  {
    global $factory;
    
    $query = "SELECT id FROM config WHERE `key`='".mysql_real_escape_string($name)."'";
    
    $exists = $factory->connection->execute_scalar($query);
    if ($exists)
    {
      $query = "UPDATE config SET `value` = '".mysql_real_escape_string($value)."' WHERE `key`='".mysql_real_escape_string($name)."'";
      $factory->connection->execute($query);
    }
    else
    {
      $query = "INSERT config(`key`, `value`) VALUES ('".mysql_real_escape_string($name)."', '".mysql_real_escape_string($value)."')";
      $factory->connection->execute($query);
    }
    return $value;
  }
}

?>