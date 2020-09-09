<?php 
  echo building_address_field('house', 'Адрес', array('required' => true, 'read_only' => $form->read_only));
  echo $form->text('owner_name', 'Владелец');
  echo $form->text_area('owner_description', 'Дополнительно', null, array('class' => 'input-xlarge','rows' => '4'));
  echo $form->check_box('is_online', 'Онлайн');
  
  echo $form->check_boxes('house_competitors', 'Конкуренты', get_field_collection(House::competitors(), 'id', 'name'), null, array('value' => $house->load_house_competitors()));
  
  echo $form->money('activation_fee', 'Плата за подключение', array('hint' => 'Вводить только для домов с льготными подключениями'), array('class' => 'input-small'));
  
  $ba_form = new FormBuilder('billing_account');
  echo $ba_form->text('lookup_code', 'Лицевой счет', array('required' => get_object_value('billing_account', 'lookup_code') ? true : false, 'hint' => 'Вводить только для домов, которые собирают оплату самостоятельно'), array('class' => 'input-small contract_number'));

  $custom_schema = get_object_value($house, 'apartment_schema');
?>
<ul class='nav nav-tabs' id='myTab'>
  <li class='<?php if (!$custom_schema) echo 'active' ?>'><a href='#standard_schema'>Стандартная схема</a></li>
  <li class='<?php if ($custom_schema) echo 'active' ?>'><a href='#custom_schema'>Особая схема</a></li>
</ul>
 
<div class='tab-content'>
  <div class='tab-pane <?php if (!$custom_schema) echo 'active' ?>' id='standard_schema'>
  <?php
    echo $form->text('floors', 'Этажей', array('required' => true), array('class' => 'input-small'));
    echo $form->text('entrances', 'Подъездов', array('required' => true), array('class' => 'input-small'));
    echo $form->text('apartments', 'Квартир', array('required' => true), array('class' => 'input-small'));
  ?>
  </div>
  <div class='tab-pane <?php if ($custom_schema) echo 'active' ?>' id='custom_schema'>
  <?php
    echo $form->text_area('apartment_schema', 'Расположение квартир', array('hint' => "Например, '4,4,99:3' - на 1-м и 2-м этаже по 4 кв, на последнем - 3 кв (99, 100 и 101)", 'required' => true), array('class' => 'input-xlarge', 'rows' => 4));
  ?>
  </div>
</div>