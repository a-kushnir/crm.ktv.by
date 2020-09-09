<?php
/////////////////
// APP VITALS  //

function include_if_exists($_current_file, &$_environment_vars = null) {
  $exists = file_exists($_current_file);
  if ($exists) {
    unset($exists);
    
    if ($_environment_vars) // Set variables from hash
      foreach($_environment_vars as $_environment_var => $_environment_value) {
        global $$_environment_var;
        $$_environment_var = $_environment_value;
      }
    
    include($_current_file);
    
    if ($_environment_vars) // Update hash from variables
      foreach($_environment_vars as $_environment_var => $_environment_value) {
        $_environment_vars[$_environment_var] = $$_environment_var;
      }
    
    return true;
  }
  return false;
}

function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
  // error was suppressed with the @-operator
  if (0 === error_reporting()) return false;
  
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('error_handler');

// APP VITALS  //
/////////////////

set_time_limit(900); // 15 minutes

define('APP_ROOT',dirname(dirname(dirname(__FILE__))));

// Load routing
include APP_ROOT.'/lib/engine/routing.php';
include APP_ROOT.'/config/routes.php';

// Load ends_with function
include APP_ROOT.'/lib/engine/functions/string.php';

// Load /config/*.php
foreach (glob(APP_ROOT.'/config/*.php') as $filename)
  if (!ends_with($filename, 'routes.php'))
    include $filename;

// Load /lib/engine/*.php
foreach (glob(APP_ROOT.'/lib/engine/functions/*.php') as $filename)
  if (!ends_with($filename, 'string.php'))
    include $filename;

// Load /lib/engine/*.php
foreach (glob(APP_ROOT.'/lib/engine/database/*.php') as $filename)
  include $filename;
    
foreach (glob(APP_ROOT.'/lib/engine/*.php') as $filename)
  if (!ends_with($filename, 'startup.php') && 
      !ends_with($filename, 'routing.php') && 
      !ends_with($filename, 'application.php'))
    include $filename;

// Load /lib/*.php
foreach (glob(APP_ROOT.'/lib/*.php') as $filename)
  include $filename;
  
// Load /app/models/*.php
foreach (glob(APP_ROOT.'/app/models/*.php') as $filename)
  include $filename;

?>