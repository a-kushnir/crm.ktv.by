<?php 
  echo $form->date('actual_date', 'Дата', array('required' => true));
  echo $form->select('worker_id', 'Работник', get_field_collection($workers, 'id', 'name', ''), array('required' => true));
  echo $form->text('hours', 'Часы', array('required' => true), array('class' => 'span1', 'value' => format_float(get_object_value($time_entry, 'hours'), 2)));
  echo $form->select('grade', 'Оценка', get_field_collection(TimeEntry::grades(), 0, 1), array('required' => true));
  echo $form->select('time_activity_id', 'Деятельность', get_field_collection(TimeEntry::time_activities(), 'id', 'name', ''), array('required' => true));
  echo $form->text_area('comment', 'Комментарий', null, array('rows' => 4, 'class' => 'input-xxlarge'));

  if (has_access('timesheet/audit')) {
    $audit_record = $time_entry;
    include APP_ROOT.'/app/views/layouts/_audit.php';
  }
?>
