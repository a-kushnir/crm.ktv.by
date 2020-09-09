<?php
function export_data() {
  $tables = array(
    'billing_detail_types' => array(
      'columns' => array('id', 'name'),
    ),
    'billing_tariffs' => array(
      'columns' => array('id', 'name', 'description', 'subscription_fee'),
      'conditions' => 'active = true',
    ),
    'subscribers' => array(
      'columns' => array('id', 'first_name', 'last_name', 'middle_name'),
      'conditions' => 'active = true AND (SELECT subscription_fee FROM billing_accounts ba JOIN billing_tariffs bf ON ba.billing_tariff_id = bf.id WHERE subscribers.billing_account_id = ba.id) > 0',
    ),
    'billing_accounts' => array(
      'columns' => array('id', 'lookup_code', 'subscriber_id', 'billing_tariff_id', 'actual_balance'),
      'conditions' => 'active = true',
    ),
    'billing_details' => array(
      'columns' => array('id', 'billing_account_id', 'billing_detail_type_id', 'actual_date', 'value', 'actual_balance', 'comment'),
      'conditions' => "actual_date >= '".date(MYSQL_DATE,strtotime(date(MYSQL_DATE, time())."-3 month"))."'",
    ),
    'users' => array(
      'columns' => array('id', 'login', 'password', 'subscriber_id'),
      'select' => "SELECT id, cell_phone login, MD5(RIGHT(passport_identifier,7)) password, id subscriber_id
      FROM subscribers WHERE active = true AND cell_phone <> ''",
    ),
  );
  
  $result = array();
  foreach($tables as $table_name => $table_params) {
    $result[] = export_table($table_name, $table_params['columns'], 
      isset($table_params['select']) ? $table_params['select'] : '',
      isset($table_params['conditions']) ? $table_params['conditions'] : ''
    );
  }
  
  return $result;
}

function export_table($table, $columns, $select = '', $conditions = '') {
  global $factory;
  
  $data = array();
  
  $first = true;
  $rows = $select ?
    $factory->connection->execute($select) :
    $factory->select($table, $columns, $conditions);
    
  while($row = mysql_fetch_array($rows)){
    $values = array();
    
    if ($first)
    {
      $columns = array();
      foreach ($row as $key => $value)
        if (!is_int($key))
          $columns[] = $key;
      $data[] = $table;
      $data[] = join("|", $columns);
      $first = false;
    }
    
    foreach ($row as $key => $value)
      if (!is_int($key))
        $values[] = $value;
    $data[] = join("|", $values);
  }
  unset($rows);

  $result = join("\n", $data);
  $result = gzcompress($result);
  
  return send_post_request(DS_URL, array('data'=>$result,'auth'=>generate_auth())).' ';
}

function generate_auth()
{
   $login = time();
   $password = md5($login.DS_SECRET);
   return $login.':'.$password;
}

function send_post_request($url, $params)
{
  $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => http_build_query($params),
      'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
    )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  return $result;
}
?>