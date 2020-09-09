<?php
  $user_hit = array();
  
  $user_hit['request_method'] = $_SERVER['REQUEST_METHOD'];
  $user_hit['request_uri'] = $_SERVER['REQUEST_URI'];
  
  global $controller, $action, $layout;
  $user_hit['controller'] = $controller;
  $user_hit['action'] = $action;
  $user_hit['layout'] = $layout;
  
  global $benchmark;
  if (isset($benchmark)) {
    $user_hit['view_time'] = round($benchmark->view_time*1000);
    $user_hit['mysql_time'] = round($benchmark->query_time*1000);
    $user_hit['action_time'] = round($benchmark->action_time*1000);
    $user_hit['total_time'] = round($benchmark->total_time*1000);
  }
  
  if (isset($_SESSION['user_session_id'])) $user_hit['user_session_id'] = $_SESSION['user_session_id'];
  if (isset($_SESSION['user_id'])) $user_hit['created_by'] = $_SESSION['user_id'];
  $user_hit['created_at'] = date(MYSQL_TIME, time());
  
  global $factory;
  if (isset($factory)) $factory->create('user_hits', $user_hit);
?>