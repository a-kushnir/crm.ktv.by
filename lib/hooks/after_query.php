<?php
  if($query_time > 1) {
    $error_backtrace = format_backtrace(debug_backtrace(), 1);
    global $factory;
    $id = $factory->create('errors', array(
    'created_at' => date(MYSQL_TIME, time()),
    'server_name' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '<script>',
    'process' => 'crm.ktv.by',
    'message' => 'Slow Query ('.number_format($query_time, 3).'s)!',
    'exception_message' => $query,
    'exception_backtrace' => print_r($error_backtrace, true),
    'exception_class' => 'SlowQuery',
    'request' => isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'] : '<script>',
    'session' => isset($_SESSION) ? print_r($_SESSION, true) : null,
    'unhandled' => 1,
    'environment' => (defined('ENVIRONMENT') ? ENVIRONMENT : null),
    'created_by' => (isset($_SESSION) && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null),
    ));
  }
?>