<?php
class Attachment extends ModelBase {
  var $table_name = 'attachments';

  static function load($id = null, $owner_type = null, $owner_id = null)
  {
    global $factory;
    $query = 'SELECT * FROM attachments a WHERE';
    $query.= ($id != null ? " a.id = '".mysql_real_escape_string($id)."'" : " a.active = true");
    if ($id == null) $query.= ($owner_type != null ? " AND a.owner_type = '".mysql_real_escape_string($owner_type)."'" : " AND a.owner_type IS NULL");
    if ($id == null) $query.= ($owner_id != null ? " AND a.owner_id = '".mysql_real_escape_string($owner_id)."'" : " AND a.owner_id IS NULL");
    $query.= ' ORDER BY name';
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Attachment($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Attachment($row);
      unset($rows);
    }
    
    return $result;
  }  
}

?>