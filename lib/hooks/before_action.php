<?php
global $controller_obj;

if (isset($_SESSION['user_session_id'])) {
  User::update_session($_SESSION['user_session_id']);
}

if (!isset($controller_obj->suppress_authorization) || !$controller_obj->suppress_authorization){
  if (!logged_in()) show_sign_in();
}

if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] != $_SERVER['REMOTE_ADDR']) {
  show_sign_in();
}

$controller_obj->mobile_version = isset($_SERVER['HTTP_USER_AGENT']) && 
  (stripos(strtolower($_SERVER['HTTP_USER_AGENT']),'android') !== false ||
   stripos(strtolower($_SERVER['HTTP_USER_AGENT']),'iphone') !== false ||
   stripos(strtolower($_SERVER['HTTP_USER_AGENT']),'symbos') !== false);

if (!isset($controller_obj->format)) {
  $controller_obj->format = $controller_obj->layout = 'html';
} else if ($controller_obj->format == 'print') {
  $controller_obj->layout = 'print';
} else if ($controller_obj->format == 'ajax') {
  $controller_obj->layout = 'ajax';
} else {
  $controller_obj->layout = 'html';
}

load_capabilities();
?>