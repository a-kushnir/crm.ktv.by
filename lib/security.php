<?php
function logged_in() {
  return isset($_SESSION['user_id']);
}

function show_sign_in() {
  session_destroy();
  $url = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REFERER'];
  $url = $url && $url != '/' && $url != '/home' ? '?for='.base64_encode($url) : '';
  redirect_to('/sign_in'.$url);
}

function load_capabilities() {
  global $cached_capabilities_hash;
  $cached_capabilities_hash = isset($_SESSION['role_id']) ?
    Role::get_capabilities($_SESSION['role_id']) : null;
}

if (defined ('CHECK_CAPABILITY_EXISTENCE') && CHECK_CAPABILITY_EXISTENCE == 'true') {
  function has_access() {
    global $cached_capabilities_hash;
    if (!$cached_capabilities_hash) return false;
    
    foreach (func_get_args() as $capability)
      if (isset($cached_capabilities_hash[$capability])) {
        if ($cached_capabilities_hash[$capability])
          return true;
        else
          continue;
      } else {
        throw new ErrorException("Security issue: ".$capability." capability doesn't exist");
      }
        
    return false;
  }
} else { // don't check capability existence
  function has_access() {
    global $cached_capabilities_hash;
    if (!$cached_capabilities_hash) return false;
    
    foreach (func_get_args() as $capability)
      if (isset($cached_capabilities_hash[$capability]))
        return true;
        
    return false;
  }
}

function check_access() {
  $arguments = func_get_args();
  $has_access = call_user_func_array('has_access', $arguments);
  if (!$has_access) show_403();
}

function show_403() {
  redirect_to('/403');
}

function show_404() {
  redirect_to('/404');
}
?>