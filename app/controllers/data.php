<?php
class DataController extends ApplicationController
{
  var $suppress_authorization = true;

  function index()
  {
    check_access('data');
    $this->title = 'Данные';
  }
  
  function export()
  {
    check_access('data');
    $this->title = 'Экспорт данных';
    include(APP_ROOT.'/lib/data/export.php');
    $this->export_trace = export_data();
  }

  function import()
  {
    // Embedded auth
    $this->layout = null;
    include(APP_ROOT.'/lib/data/import.php');
    import_data();
  }

  function notify()
  {
    // Embedded auth
    $this->layout = null;
    include(APP_ROOT.'/lib/data/notify.php');
    notify_data();
  }
  
  function cleanup()
  {
    check_access('data');
    $this->title = 'Очистка данных';
    include(APP_ROOT.'/lib/data/cleanup.php');
    $this->cleanup_trace = cleanup_data();
  }
}
?>