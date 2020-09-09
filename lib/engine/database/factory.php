<?php
DEFINE('MYSQL_DATE', 'Y-m-d');
DEFINE('MYSQL_TIME', 'Y-m-d H:i:s');

class Factory
{
    var $connection;
    
    public function __construct()
    {
        $this->connection = new DbConnection();
    }

  public function record_exists($table, $id)
  {
    $sql = "SELECT id FROM `".$table."` WHERE id = '".mysql_real_escape_string($id)."'";
    return $this->connection->execute_scalar($sql);
  }
  
  public function select($table, $columns, $conditions = '')
  {
    $cs = array();
    foreach ($columns as $column) {
      $cs[] = "`".$column."`";
    }
    
    $sql = "SELECT ".join(", ", $cs)." FROM `".$table."`";    
    if ($conditions) $sql.= " WHERE ".$conditions;
    return $this->connection->execute($sql);
  }
  
  public function create($table, $attributes)
  {
    $columns = array();
    $values = array();
    foreach ($attributes as $column => $value) {
      $columns[] = "`".$column."`";
      $values[] =  is_null($value) ? "NULL" : "'".mysql_real_escape_string($value)."'";
    }
    
    $sql = "INSERT ".$table."(".join(", ", $columns).") VALUES (".join(", ", $values).")";    
    return $this->connection->execute_scalar($sql, null, 'SELECT LAST_INSERT_ID()');
  }
  
  public function update($table, $id, $attributes, $conditions = null)
  {
    $values = array();
    foreach ($attributes as $column => $value)
      if ($column != 'id')
        $values[] = "`".$column."`=".(is_null($value) ? "NULL" : "'".mysql_real_escape_string($value)."'");
    
    $sql = "UPDATE ".$table." SET ".join(", ", $values)." WHERE id='".mysql_real_escape_string($id)."'";
    if ($conditions) $sql .= " AND ".$conditions;
    
    return $this->connection->execute_scalar($sql, null, 'SELECT ROW_COUNT()');
  }
  
  public function reactivate($table, $id)
  {
    return $this->update($table, $id, array(
      'active' => 1,
      'updated_at' => date(MYSQL_TIME, time()),
      'updated_by' => $_SESSION['user_id'])
    );
  }
  
  public function deactivate($table, $id)
  {
    return $this->update($table, $id, array(
      'active' => 0,
      'updated_at' => date(MYSQL_TIME, time()),
      'updated_by' => $_SESSION['user_id'])
    );
  }
  
  public function destroy($table, $id)
  {
    $sql = "DELETE FROM ".$table." WHERE id='".mysql_real_escape_string($id)."'";
    return $this->connection->execute_scalar($sql, null, 'SELECT ROW_COUNT()');
  }
  
  public function update_all($table, $rule, $conditions = '')
  {
    $sql = "UPDATE `".$table."` SET ".$rule;    
    if ($conditions) $sql.= " WHERE ".$conditions;
    return $this->connection->execute_scalar($sql, null, 'SELECT ROW_COUNT()');
  }
  
  public function delete_all($table, $conditions = '')
  {
    $sql = "DELETE FROM `".$table."`";    
    if ($conditions) $sql.= " WHERE ".$conditions;
    return $this->connection->execute_scalar($sql, null, 'SELECT ROW_COUNT()');
  }
}

$factory = new Factory();
?>