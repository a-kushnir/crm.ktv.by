<?php 
if (!$form->read_only) {
  echo $form->text('last_name', 'Фамилия', array('required' => true));
  echo $form->text('first_name', 'Имя', array('required' => true));
  echo $form->text('middle_name', 'Отчество', array('required' => true));
}
echo $form->text('address', 'Адрес', array('required' => true), array('class' => 'input-xlarge'));
echo $form->phone('home_phone', 'Домашний', array('hide_blank' => true));
echo $form->phone('cell_phone1', 'Мобильный 1', array('required' => true));
echo $form->phone('cell_phone2', 'Мобильный 2', array('hide_blank' => true));
echo passport_field('worker', 'Паспорт', array('required' => true, 'read_only' => $form->read_only));
echo $form->date('birth_date', 'Дата рождения', array('required' => true));
echo $form->text('multiply', 'Множитель', array('hide_blank' => true), array('class' => 'input-mini', 'value' => (isset($worker['multiply']) && $worker['multiply'] ? format_float($worker['multiply'], 2) : null)));
if (!$form->read_only) {
  echo $form->check_box('show_timesheet', 'Показывать в табеле');
  echo $form->check_box('show_requests', 'Показывать в заявках');
}
echo $form->text_area('comment', 'Комментарий', array('hide_blank' => true), array('class' => 'input-xlarge', 'rows' => '5'));

if (has_access('worker/user') && (!$read_only || get_object_value($worker, 'user_id'))) {
  $u_form = new FormBuilder('user', $form->read_only);
  echo $u_form->begin_fieldset('Пользователь');
  
  echo $u_form->text('login', 'Логин', array('required' => get_object_value($worker, 'user_id')));
  if (!$u_form->read_only) {
    echo $u_form->password('new_password', 'Пароль', null, array('autocomplete' => 'off'));
    echo $u_form->password('password_confirm', 'Подтверждение', array('autocomplete' => 'off'));
  }
  echo $u_form->check_box('enabled', 'Вход разрешен', null, array('checked' => !get_object_value($user, 'disabled_at')));

  echo $u_form->end_fieldset();
}

if ($form->read_only && has_access('worker/audit')) {
  $audit_record = $worker;
  include APP_ROOT.'/app/views/layouts/_audit.php';
}
?>