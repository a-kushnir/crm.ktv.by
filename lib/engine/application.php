<?php
function shutdown_handler() {
  if(isset($_SESSION)) session_write_close();

  global $benchmark, $total_benchmark;
  if (isset($benchmark) && isset($total_benchmark)) {
    $benchmark->total_time = Benchmark::end($total_benchmark);
  }

  $file_name = defined('APP_ROOT') ? APP_ROOT.'/lib/hooks/on_shutdown.php' : '../hooks/on_shutdown.php';
  include_if_exists($file_name);
}
register_shutdown_function('shutdown_handler');

/////////////////
// APP STARTUP //
try {
if (file_exists('../hooks/before_startup.php'))
  include('../hooks/before_startup.php');

include('startup.php');
$total_benchmark = Benchmark::begin();

include_if_exists(APP_ROOT.'/lib/hooks/after_startup.php');

// APP STARTUP //
/////////////////
// CONTROLLER  //

global $controller, $action, $layout;
$routing->parse($_SERVER['REQUEST_METHOD'], isset($_GET['uri']) ? $_GET['uri'] : '');
$controller = $routing->controller;
$render_action = $action = $routing->action;

if(!isset($_SESSION)) session_start();

if (include_if_exists(APP_ROOT.'/app/controllers/'.$controller.'.php')) {
  $controller_cls = constanize($controller).'Controller';
  $controller_obj = new $controller_cls($factory, $controller, $action, $routing->params);
} else {
  include_if_exists(APP_ROOT.'/lib/hooks/missing_controller.php');
}
include_if_exists(APP_ROOT.'/lib/hooks/before_action.php');

$action_benchmark = Benchmark::begin();

if ($action && method_exists($controller_obj, $action)) {
  call_user_func(array($controller_obj, $action));
} else {
  include_if_exists(APP_ROOT.'/lib/hooks/missing_action.php');
}

$controller_vars = get_object_vars($controller_obj);

$benchmark->action_time = Benchmark::end($action_benchmark);

include_if_exists(APP_ROOT.'/lib/hooks/after_action.php', $controller_vars);

// ACTION      //
/////////////////
// VIEW        //

include_if_exists(APP_ROOT.'/lib/hooks/before_view.php', $controller_vars);

$view_benchmark = Benchmark::begin();

if ($controller_obj) $layout = $controller_obj->layout;
if ($layout != null) include_if_exists(APP_ROOT.'/app/views/layouts/'.$layout.'_header.php', $controller_vars);

if (!include_if_exists(APP_ROOT.'/app/views/'.$controller.'/'.$render_action.'.php', $controller_vars)) {
  include_if_exists(APP_ROOT.'/lib/hooks/missing_view.php', $controller_vars);
};

if ($layout != null) include_if_exists(APP_ROOT.'/app/views/layouts/'.$layout.'_footer.php', $controller_vars);

$benchmark->view_time = Benchmark::end($view_benchmark);

include_if_exists(APP_ROOT.'/lib/hooks/after_view.php', $controller_vars);

// VIEW        //
/////////////////
} catch (ErrorException $exception) {
  $args = array('exception' => $exception);
  include_if_exists(APP_ROOT.'/lib/hooks/app_exception.php', $args);
}
?>