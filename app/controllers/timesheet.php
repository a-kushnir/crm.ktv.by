<?php
class TimesheetController extends ApplicationController
{
  function index()
  {
    check_access('timesheet');

    $this->title = 'Табель';
    if (has_access('timesheet/new')) $this->primary_menu_create_url = url_for('timesheet', 'new');

    $this->worker_id = get_field_value('worker');
    $this->from_date = get_field_value('from');
    $this->to_date = get_field_value('to');
    $this->include_paid = get_field_value('include');

    if (!$this->to_date) $this->to_date = date(DATE_FORMAT);
    if (!$this->from_date) $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time())."-1 month +1 day"));
    
    if (!has_access('timesheet/full_history') && strtotime(date(DATE_FORMAT, time())."-1 month +1 day") > parse_db_date(prepare_date($this->from_date))) {
      $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time())."-1 month +1 day"));
    }
    
    $this->time_entries = $this->worker_id ? TimeEntry::load(null, 
      $this->worker_id == 'all' ? null : $this->worker_id, 
      $this->from_date ? prepare_date($this->from_date) : null,
      $this->to_date ? prepare_date($this->to_date) : null,
      $this->include_paid
      ) : null;
      
    $this->workers = TimeEntry::workers();
  }

  function add()
  {
    check_access('timesheet/new');

    $this->title = 'Затраченное время';

    $this->time_entry = new TimeEntry(array(
      'actual_date' => date(MYSQL_DATE, time()),
      'grade' => 5,
    ));
    
    $this->workers = TimeEntry::workers();
  }
  
  function create()
  {
    check_access('timesheet/new');
    
    $this->time_entry = new TimeEntry();
    $this->time_entry->load_attributes($_POST['time_entry']);

    if ($this->time_entry->valid()) {
      $id = $this->time_entry->create();
      
      $this->time_entry = TimeEntry::load($id);
      if (!$this->time_entry) show_404();
      
      User::log_event('добавил часы', format_float($this->time_entry['hours'], 2).' за '.format_date($this->time_entry['actual_date']).' для '.$this->time_entry['name'], url_for('timesheet','edit',$id));
      
      flash_notice('Часы были добавлены');
      redirect_to(url_for('timesheet', 'new'));
    } else {
      $this->title = 'Затраченное время';
      $this->workers = TimeEntry::workers();
      render_action('add');
    }
  }
  
  function edit()
  {
    check_access('timesheet/edit');

    $this->time_entry = TimeEntry::load($this->id);
    if (!$this->time_entry) show_404();

    $this->title = 'Редактирование';
    $this->subtitle = $this->time_entry['name'];
    
    $this->workers = TimeEntry::workers();
  }

  function update()
  {
    check_access('timesheet/edit');
    
    $this->time_entry = new TimeEntry();
    $this->time_entry->load_attributes($_POST['time_entry'], $this->id);

    if ($this->time_entry->valid()) {
      $this->time_entry->update();
      
      $this->time_entry = TimeEntry::load($this->id);
      User::log_event('отредактировал часы', format_float($this->time_entry['hours'], 2).' за '.format_date($this->time_entry['actual_date']).' для '.$this->time_entry['name'], url_for('timesheet','edit',$this->id));
      flash_notice('Часы были обновлены');
      
      redirect_to(url_for('timesheet', 'index'));
    } else {
      $this->this->title = 'Редактирование';
      $this->this->subtitle = $this->time_entry['name'];
      $this->workers = TimeEntry::workers();
      render_action('edit');
    }
  }
  
  function set_paid()
  {
    check_access('timesheet/mark_paid');

    global $factory;
    
    if ($_POST)
      $time_entries = get_field_value('time_entries');
      foreach($time_entries as $time_entry_id => $flag) {
        TimeEntry::set_paid($time_entry_id);
      }
    
    redirect_to(TimesheetController::url_with_filter('/timesheet'));
  }

  function destroy()
  {
    check_access('timesheet/destroy');

    if ($_POST)
    {
      $time_entries = get_field_value('time_entries');
      foreach($time_entries as $time_entry_id => $flag) {
        $this->factory->deactivate('time_entries', $time_entry_id);
      }
      flash_notice('Часы были удалены');
    }
    
    redirect_to(TimesheetController::url_with_filter('/timesheet'));
  }

  function report()
  {
    check_access('report/timesheet');

    $this->from_date = get_field_value('from');
    $this->to_date = get_field_value('to');
    if (!$this->to_date) $this->to_date = date(DATE_FORMAT);
    if (!$this->from_date) $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time()).'-1 month +1 day'));
    
    $this->title = 'Табель';
    $this->subtitle = 'с '.$this->from_date.' по '.$this->to_date;
    $this->primary_menu_item = '/reports';
    
    $this->daily_time_entries = TimeEntry::daily_report($this->from_date, $this->to_date);
    $this->worker_time_entries = TimeEntry::worker_report($this->from_date, $this->to_date);
  }

  static function url_with_filter($url) {
    $url .= '?worker='.get_field_value('worker');
    $url .= '&from='.get_field_value('from');
    $url .= '&to='.get_field_value('to');
    return $url;
  }
}
?>