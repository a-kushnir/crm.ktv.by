<?php $javascripts[] = '/javascripts/subscribers.js'; ?>

<?php if ($request->is_new()) {

  echo apartment_address_field('request', "Адрес", array('required' => true, 'read_only' => $form->read_only));
  echo '<div id="subscriber_container">';
  include '_subscriber.php';
  echo '</div>';
  echo $form->select('request_type_id', 'Тип', get_field_collection(RequestType::load(), 'id', 'name', ''), array('required' => true));
  echo $form->select('request_priority_id', 'Приоритет', get_field_collection(Request::priorities(), 'id', 'name'), array('required' => true));
  echo $form->text_area('comment', 'Примечание', null, array('class' => 'input-xxlarge'));

} else {
  if ($form->read_only) {
 
    $now = time();
    $css_class = '';
    $created_at = parse_db_datetime($request['created_at']);
    if ($created_at > $now - Request::$wait_normal * 86400) {
      $css_class = 'wait_short';
    } else if ($created_at > $now - Request::$wait_long * 86400) {
      $css_class = 'wait_normal';
    } else {
      $css_class = 'wait_long';
    }
    
  echo $form->text('created_at', 'Принята', null, array('value' => '<span class="'.$css_class.'">'.human_datetime($request['created_at']).'</span>'));
}

if (isset($request['subscriber_id']) && $request['subscriber_id']) {
  $subscriber = Subscriber::load($request['subscriber_id']);
  $sub_form = new FormBuilder('subscriber', true);
  echo $sub_form->read_only('subscriber', 'Абонент', null, array('value' => link_to('/subscribers/'.$request['subscriber_id'], $request->name())));
  echo $sub_form->read_only('actual_balance', 'Баланс', null, array('value' => format_money($subscriber['actual_balance'])));
}
  echo $form->phone('home_phone', 'Домашний', array('hide_blank' => true));
  echo $form->phone('cell_phone', 'Мобильный', array('hide_blank' => true));
  
  if ($form->read_only) {
    echo $form->read_only('request_type', 'Тип');
    echo $form->read_only('request_priority', 'Приоритет');
  } else {
    echo $form->select('request_type_id', 'Тип', get_field_collection(RequestType::load(), 'id', 'name'), array('required' => true));
    echo $form->select('request_priority_id', 'Приоритет', get_field_collection(Request::priorities(), 'id', 'name'), array('required' => true));
  }
  
  echo $form->text_area('comment', 'Примечание', array('hide_blank' => true), array('class' => 'input-xxlarge'));

  if ($form->read_only)
  {
    if (has_access('request/audit')) {
      $audit_record = $request;
      include APP_ROOT.'/app/views/layouts/_audit.php';
    }
  
    if (has_access('request/close')) {
      echo '<fieldset>';
      echo '<legend>Закрытие заявки</legend>';

      $form->read_only = $request['handled_on'];
      $workers = Request::workers();
      echo $form->read_only ? $form->date('handled_on', 'Закрыта') : null;
      echo $form->read_only ? 
        $form->read_only('worker', 'Ответственный'):
        $form->select('handled_by', 'Ответственный', get_field_collection($workers, 'id', 'name', ''), array('required' => true));
      echo $form->text_area('handled_comment', 'Отчет', array('required' => true, 'hint' => 'Выполнено или Исправлен абонентский кабель и т.д.'), array('class' => 'input-xxlarge'));
      
      echo '</fieldset>';
    }
  }
  
} ?>
