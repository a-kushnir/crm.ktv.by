<?php
class PollsController extends ApplicationController
{
  function index()
  {
    $this->title = 'Опросы';
    $this->subtitle = null;

    $filter = trim(get_field_value('filter'));
    
    if (has_access('subscriber/full_index')) {
      $this->subscribers = Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size);
      $this->records = Subscriber::records($filter);
    } else {
      $this->subscribers = $filter ? Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size) : array();
      $this->records = $filter ? Subscriber::records($filter) : 0;
    }
  }

  function add()
  {
    $this->title = 'Опрос';
    $this->subtitle = null;
  }

  function show()
  {
    $this->title = 'Абонент';
    $this->subtitle = $subscriber->name();
  }
}
?>