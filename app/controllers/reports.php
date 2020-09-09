<?php
class ReportsController extends ApplicationController
{
  function index()
  {
    check_access('reports');

    $this->title = 'Отчеты';
  }

  function city()
  {
    check_access('report/city');

    $this->title = $this->factory->connection->execute_scalar("SELECT name FROM cities WHERE id = ".mysql_real_escape_string($this->id));
  }

  function regions()
  {
    check_access('report/regions');

    $this->title = 'Регионы';
  }

  function events()
  {
    check_access('report/events');

    $this->title = 'События';
    $this->subtitle = 'за 24 часа';
  }

  function video()
  {
    check_access('report/video');

    $this->title = 'Видео';
  }

  function billing()
  {
    check_access('report/billing');

    $this->title = 'Биллинг';
  }

  function subscribers()
  {
    check_access('report/subscribers');
    
    $this->mode = get_field_value('mode');
    $this->from_date = get_field_value('from');
    $this->to_date = get_field_value('to');
    if (!$this->mode) $this->mode = 'delta';
    if (!$this->to_date) $this->to_date = date(DATE_FORMAT);
    if (!$this->from_date) $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time())."-1 month +1 day"));
    
    $this->title = 'Отчет по абонентам';
    $this->subtitle = 'с '.$this->from_date.' по '.$this->to_date;

    $this->primary_menu_item = '/reports';

    if ($this->mode == 'delta') $this->subscribers = Subscriber::report_delta(prepare_date($this->from_date), prepare_date($this->to_date));
    if ($this->mode == 'starts') $this->subscribers = Subscriber::report_starts(prepare_date($this->from_date), prepare_date($this->to_date));
    if ($this->mode == 'ends') $this->subscribers = Subscriber::report_ends(prepare_date($this->from_date), prepare_date($this->to_date));
    $this->term_reasons = Subscriber::report_term_reasons(prepare_date($this->from_date), prepare_date($this->to_date));
    $this->competitors = Subscriber::report_competitors(prepare_date($this->from_date), prepare_date($this->to_date));
  }
  
  function competitors()
  {
    check_access('report/competitors');
    
    $this->houses = House::competitors_report();
    $this->subscribers = Subscriber::competitors_report();
    
    $this->title = 'Конкуренты';
    $this->subtitle = null;
  }
}
?>