<?php
global $render_action;
$file_name = '/app/views/'.$controller.'/'.$render_action.'.php';
$error_backtrace = format_backtrace(debug_backtrace(), 1);

if (!defined('ENVIRONMENT') || ENVIRONMENT == 'development') {
  echo '<h1>Oops, missing view! :(</h1></br>';
  echo '<b>View path</b>:</br>';
  echo $file_name.'</br>';
} else {
  global $factory;
  $id = $factory->create('errors', array(
  'created_at' => date(MYSQL_TIME, time()),
  'server_name' => $_SERVER['SERVER_NAME'],
  'process' => 'crm.ktv.by',
  'message' => 'Oops, missing view! :(',
  'exception_message' => $file_name.' not found',
  'exception_backtrace' => print_r($error_backtrace, true),
  'exception_class' => 'FileNotFound',
  'request' => $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'],
  'session' => print_r($_SESSION, true),
  'unhandled' => 0,
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