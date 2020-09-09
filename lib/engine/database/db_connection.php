<?php
class DbConnection
{
  var $link;
  
  public function __construct()
  {
    if (USE_PCONNECT == 'true') {
      $this->link = mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD);
      // Fixes "server has gone away" error
      if (!mysql_ping($this->link)) {
        $this->link = mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD);
      }
    } else {
      $this->link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    }
    
    if (!$this->link)
    {
      echo "<p> В настоящий момент сервер базы данных не доступен, поэтому корректное отображение страницы невозможно.</p>";
      exit;
    }
    if (!mysql_select_db(DB_DATABASE, $this->link))
    {
      echo "<p> В настоящий момент база данных не доступна, поэтому корректное отображение страницы невозможно.</p>";
      exit;
    }

    // UTF-8
    mysql_query("SET NAMES utf8");
    mysql_query("set character_set_client='utf8'");
    mysql_query("set character_set_results='utf8'");
    mysql_query("set collation_connection='utf8_general_ci'");
  }
  
  public function execute($query, $exam_query = null)
  {
    $variables = array('query' => $query);
    include_if_exists(APP_ROOT.'/lib/hooks/before_query.php', $variables);
  
    global $benchmark;
    $query_benchmark = Benchmark::begin();
  
    $result = mysql_query($query, $this->link);
        
    $query_time = Benchmark::end($query_benchmark);
    $benchmark->query_time += $query_time;
    
    if ($exam_query) { // Loads IDs, affected rows count etc.
      if (!$result) {
        $variables = array('query' => $query);
        include_if_exists(APP_ROOT.'/lib/hooks/invalid_query.php', $variables);
      }
      unset($result);
      $result = mysql_query($exam_query, $this->link);
    }
    
    if ($result) {
      $variables = array('query' => $query, 'query_time' => $query_time);
      include_if_exists(APP_ROOT.'/lib/hooks/after_query.php', $variables);
    } else {
      $variables = array('query' => $query);
      include_if_exists(APP_ROOT.'/lib/hooks/invalid_query.php', $variables);
    };
    
    return $result;
  }
  
  public function execute_table($query)
  {
    $rows = array();
    
    $result = $this->execute($query);
    while($row = mysql_fetch_array($result))
      $rows[] = $row;
    unset($result);
    
    return $rows;
  }
  
  public function execute_row($query, $default_value = null)
  {
    $result = $this->execute($query);
    
    if (mysql_num_rows($result) == 0) {
      return $default_value;
    } else {
      $row = mysql_fetch_array($result);
      unset($result);
      return $row;
    }
  }
  
  public function execute_column($query, $default_value = null)
  {
    $rows = array();
    
    $result = $this->execute($query);
    while($row = mysql_fetch_array($result))
      $rows[] = $row[0];
    unset($result);
    
    return $rows;
  }
  
  public function execute_scalar($query, $default_value = null, $exam_query = null)
  {
    $result = $this->execute($query, $exam_query);
    
    if (mysql_num_rows($result) == 0) {
      return $default_value;
    } else {
      return mysql_result($result, 0);
    }
  }
  
  public function execute_void($query)
  {
     $result = $this->execute($query);
     unset($result);
  }
  
  public function escape_string($value)
  {
    return mysql_real_escape_string($value);
  }
}

?>