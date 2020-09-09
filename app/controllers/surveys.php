<?php
function action_index()
{
  global $title, $subtitle;
  $title = 'Опросы';
  $subtitle = null;

  global $factory;
  global $subscribers, $records;

  $filter = trim(get_field_value('filter'));
  
  if (has_access('subscriber/full_index')) {
    $subscribers = Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size);
    $records = Subscriber::records($filter);
  } else {
    $subscribers = $filter ? Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size) : array();
    $records = $filter ? Subscriber::records($filter) : 0;
  }
}

function action_new()
{
  global $request, $subscriber;

  if ($request_id = get_field_value('request')) {
    $subscriber = $request = Request::load($request_id);
  } else if ($subscriber_id = get_field_value('subscriber')) {
    $subscriber = Subscriber::load($subscriber_id);
  } else {
    flash_alert('Неправильные параметры');
  }

  global $survey_form, $survey, $can_answer;
  $can_answer = get_field_value('can_answer');
  $survey_form = Survey::load_form();

  if ($_POST) {
    $survey = new Survey();
    $survey->load_attributes();
    if ($survey->valid()) {
      $id = $survey->create();
      flash_notice('Опрос был сохранен');
      redirect_to(url_for('surveys', 'show', $id));
    }
  }
  
  global $title, $subtitle;
  $title = 'Опрос';
  $subtitle = format_name($subscriber);
}

function action_show()
{
  global $title, $subtitle;
  $title = 'Опрос';
  $subtitle = format_name($subscriber);
}

?>