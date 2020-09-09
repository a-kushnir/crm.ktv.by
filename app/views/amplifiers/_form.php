<?php 
  echo $form->text('name', 'Название', array('required' => true));
  echo $form->select('parent_amplifier_id', 'Исходный сигнал', Amplifier::parent_amplifiers($house['id'], (isset($amplifier) ? $amplifier : null), $form->read_only, 'Оптический приемник/Станция'), null, array('class' => 'input-xxlarge'));
  echo $form->text_area('description', 'Дополнительно', array('hide_blank' => true), array('class' => 'input-xlarge', 'rows' => '4'));
  echo $form->select('entrance', 'Подъезд', Amplifier::entrances($house['entrances'], ''), array('required' => true), array('class' => 'input-small'));
  echo $form->select('floor', 'Этаж', Amplifier::floors($house['floors'], ''), array('required' => true), array('class' => 'input-small'));
?>
