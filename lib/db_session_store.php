<?php
session_set_save_handler(
  'db_session_store_open',
  'db_session_store_close',
  'db_session_store_read',
  'db_session_store_write',
  'db_session_store_destroy',
  'db_session_store_clean'
);

function db_session_store_open($savePath, $sessionName)
{
}

function db_session_store_close()
{
}

function db_session_store_read($sessionId)
{
  global $factory;
  if (!$factory) return;
  $datetime = date(MYSQL_TIME, strtotime("-60 minutes"));
  $query = "SELECT * FROM sessions WHERE lookup_code = '".mysql_real_escape_string($sessionId)."' and updated_at >= '".mysql_real_escape_string($datetime)."' LIMIT 1";
  $row = $factory->connection->execute_row($query);
  return $row != null ? $row['data'] : null;
}

function db_session_store_write($sessionId, $data)
{
  global $factory;
  if (!$factory) return;

  $query = "SELECT id FROM sessions WHERE lookup_code = '".mysql_real_escape_string($sessionId)."' LIMIT 1";
  $id = $factory->connection->execute_scalar($query);
  
  $attributes = array();
  $attributes['data'] = $data;
  $attributes['updated_at'] = date(MYSQL_TIME, time());
  if ($id) {
    $factory->update('sessions', $id, $attributes);
  } else {
    $attributes['lookup_code'] = $sessionId;
    $attributes['user_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $attributes['user_session_id'] = isset($_SESSION['user_session_id']) ? $_SESSION['user_session_id'] : null;
    $attributes['created_at'] = $attributes['updated_at'];
    $factory->create('sessions', $attributes);
  }
}

function db_session_store_destroy($sessionId)
{
  global $factory;
  if (!$factory) return;
  $query = "DELETE FROM sessions WHERE lookup_code = '".mysql_real_escape_string($sessionId)."'";
  $factory->connection->execute_void($query);
}

function db_session_store_clean($lifetime)
{
}

function db_session_reset_old()
{
  global $factory;
  if (!$factory) return;
  $datetime = date(MYSQL_TIME, strtotime("-60 minutes"));
  $query = "DELETE FROM sessions WHERE updated_at < '".mysql_real_escape_string($datetime)."'";
  $factory->connection->execute_void($query);
}

function db_session_reset_user($user_id)
{
  global $factory;
  if (!$factory) return;
  $query = "DELETE FROM sessions WHERE user_id = '".mysql_real_escape_string($user_id)."'";
  $factory->connection->execute_void($query);
}

function db_session_show_info()
{
  global $factory;
  if (!$factory) return;

  $query = "SELECT s.id, s.user_id, s.created_at, s.updated_at, u.name, us.user_agent, us.ip_address
  FROM sessions s JOIN users u ON user_id = u.id JOIN user_sessions us ON user_session_id = us.id";
  
  $result = array();
  $rows = $factory->connection->execute($query);
  while($row = mysql_fetch_array($rows))
    $result[] = $row;
  unset($rows);
  
  return $result;
}
?>