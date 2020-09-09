<?php
function import_data() {
  $tables_allowed = array(
    'billing_accounts',
    'billing_details',
    'subscribers',
    'users',
  );

  if (!isset($_POST['auth'])) {
  echo 'ERROR: No auth header';
  
  } else if (!try_auth($_POST['auth'])){
  echo 'ERROR: Auth failed';
  
  } else if (isset($_POST['data'])) {
  
    $data = $_POST['data'];
    $data = gzuncompress($data);
    $data = explode("\n", $data);
    
    if (count($data) >= 2) {
      $table = array_shift($data);
      
      if (in_array($table, $tables_allowed)) {
        $columns = explode("|", array_shift($data));
        $rows = array();
        foreach($data as $row)
          $rows[] = explode("|", $row);
        
        import_table($table.'_777', $columns, $rows);
        
        echo count($rows).' '.$table.' imported successfully';
      } else {
        echo 'ERROR: Forbidden table';
      }
      
    } else {
      echo 'ERROR: Incorrect data';
    }
  } else {
    echo 'ERROR: No data to import';
  }
}

function try_auth($auth)
{
  $values = explode(':', $auth);
  $login = $values[0];
  $password = $values[1];
  return (abs(time() - $login) <= DS_TIMEOUT) && $password == md5($login.DS_SECRET);
}

function import_table($table, $columns, $rows) {
  global $factory;
  
  $factory->delete_all($table);
  
  $column_count = count($columns);
  foreach($rows as $row) {
  
    $attributes = array();
    for($i = 0; $i < $column_count; $i++)
      $attributes[$columns[$i]] = $row[$i];
    
    /*if ($factory->record_exists($table, $attributes['id'])) {
      $factory->update($table, $attributes['id'], $attributes);
    } else {*/
      $factory->create($table, $attributes);
    //}
  }
}
?>