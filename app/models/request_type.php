<?php
class RequestType extends ModelBase {
  var $table_name = 'request_types';

  static function load()
  {
    global $factory;
    
    $query = "SELECT * FROM request_types ORDER BY position";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new RequestType($row);
    unset($rows);
    
    return $result;
    return $rows;
  }
}

?>