<?php 
  echo $form->text('name', 'Название', array('required' => true), array('class' => 'input-xxlarge'));
  echo $form->text('url', 'Адрес', array('required' => true), array('class' => 'input-xxlarge'));
  echo $form->check_box('active', 'Активно');
?>