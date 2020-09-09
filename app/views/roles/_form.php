<?php 
  echo $form->text('name', 'Название', array('required' => true));
  echo $form->text_area('description', 'Описание', null, array('class' => 'input-xlarge','rows' => '4'));
?>