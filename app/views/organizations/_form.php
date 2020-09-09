<?php
  $javascripts[] = '/javascripts/subscribers.js';
  if (!$form->read_only)
    echo $form->text('name', 'Название', array('required' => true));

  echo $form->text('keywords', 'Ключевые слова');
  echo $form->text_area('requisites', 'Реквизиты', array('required' => true), array('class' => 'input-xxlarge', 'rows' => 10));;

  if ($form->read_only && $layout != 'print') {
    $audit_record = $organization;
    include APP_ROOT.'/app/views/layouts/_audit.php';
  }
?>