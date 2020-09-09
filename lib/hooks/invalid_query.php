<?php
$error_message = mysql_error();
$error_backtrace = format_backtrace(debug_backtrace(), 1);

if (!defined('ENVIRONMENT') || ENVIRONMENT == 'development') {
?>
<html lang="ru">
<head>
  <meta charset="utf-8">
</head>
<body>
  <h1>Oops, query is invalid! :(</h1></br>
  <b>Query</b>:</br>
  <?php echo $query; ?></br>
  </br>
  <b>Error</b>:</br>
  <?php echo $error_message; ?></br>
  </br>
  <b>Backtrace</b>:</br>
  <pre>
<?php echo print_r($error_backtrace, true); ?>
  </pre>
</body>
</html>
<?php
  exit;
} else {
  global $factory;
  $id = $factory->create('errors', array(
  'created_at' => date(MYSQL_TIME, time()),
  'server_name' => $_SERVER['SERVER_NAME'],
  'process' => 'crm.ktv.by',
  'message' => 'Oops, query is invalid! :(',
  'exception_message' => $error_message ,
  'exception_backtrace' => print_r($error_backtrace, true),
  'exception_class' => 'MySQL',
  'request' => $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'],
  'session' => print_r($_SESSION, true),
  'unhandled' => 1,
  'environment' => (defined('ENVIRONMENT') ? ENVIRONMENT : null),
  'created_by' => (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null),
  ));
  if (!headers_sent()){
  redirect_to('/500');
  exit;
  } else {
  echo 'Извините, что-то пошло не так :(</br>';
  echo 'Ошибка сохранена под номером <b>'.$id.'.</b></br>';
  exit;
  }
}
?>