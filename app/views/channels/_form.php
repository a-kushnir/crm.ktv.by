<?php
  if (!$form->read_only) {
    if ($channel->is_new()) echo $form->select('station_id', 'Станция', get_field_collection(Channel::stations(), 'id', 'name', ''), array('required' => true));
    echo $form->text('name', 'Название', array('required' => true));
  }

  echo $form->select('type', 'Тип', get_field_collection(Channel::types(), 'code', 'name', ''), array('hide_blank' => true));
  if ($form->read_only) {
    echo $form->text('channel_code', 'Канал', array('hide_blank' => true));
    echo $form->text('frequency', 'Частота', array('hide_blank' => true), array('value' => format_float(get_object_value($channel, 'frequency'), 2).' МГц'));
  } else {
    echo '<div id="analog_frequency">'.$form->select('frequency_id', 'Канал', get_field_collection(Channel::analog_frequencies(), 'id', 'title', '')).'</div>';
    echo '<div id="digital_frequency">'.$form->select('frequency_id', 'Канал', get_field_collection(Channel::digital_frequencies(), 'id', 'title', '')).'</div>';
  }
  echo $form->text('satellite', 'Спутник', array('hide_blank' => true));
  echo $form->text('transponder', 'Транспордер', array('hide_blank' => true));
  echo $form->text('tuner', 'Тюнер', array('hide_blank' => true));
  echo $form->text('tuner_channel', 'Канал тюнера', array('hide_blank' => true), array('class' => 'input-small'));
  echo $form->text('access_key', 'Ключ доступа', array('hide_blank' => true));
  echo $form->text_area('description', 'Примечание', array('hide_blank' => true));
  echo $form->check_box('enabled', 'Включен');
?>
