<?php $javascripts[] = '/javascripts/subscribers.js'; ?>

<?php
  if ($form->read_only) {
    echo '<div class="row"><div class="offset2 span8">';
    echo '<p>'.
    $billing_tariff['description'].'<br>'.
    ($billing_tariff['city'] ? 'Только для <b>'.$billing_tariff['city'].'</b>'.($billing_tariff['default'] ? ' <i>(по умолчанию)</i>' : '').'<br>' : '').
    '</p><p>'.
    'Подключение: <b>'.format_money($billing_tariff['activation_fee']).'</b><br>'.
    'Ежемесячный платеж: <b>'.format_money($billing_tariff['subscription_fee']).'</b><br>'.
    '</p><p>'.
    'Доступен: <b>'.($billing_tariff['public'] ? 'администратору и операторам' : 'только администратору').'</b><br>'.
    ($billing_tariff['filter'] ? '<b>С низкочастойной фильтрацией</b><br>' : 'Полный набор частот<br>');

    $fields = array();
    if ($billing_tariff['with_justification']) $fields[] = 'Обоснование тарифа';
    if ($billing_tariff['with_ends_on']) $fields[] = 'Завершение тарифа';
    
    echo (count($fields) > 0 ? 'Дополнительные поля: <b>'.(implode(', ',$fields)).'</b><br>' : 'Без дополнительных полей').
    '</p>';
    echo '</div></div>';
  
    if ($layout != 'print') {
      $audit_record = $billing_tariff;
      include APP_ROOT.'/app/views/layouts/_audit.php';
    }

  } else {
    echo $form->text('name', 'Название', array('required' => true));
    echo $form->text('description', 'Описание', array('required' => true), array('class' => 'input-xxlarge'));
    
    echo $form->select('city_id', 'Город', get_field_collection(Address::get_cities(), 'id', 'name', '- Действует во всех -'), array('class' => 'span2'));

    echo $form->check_box('public', 'Доступен оператору');
    echo $form->check_box('default', 'Выбирается по умолчанию');
    echo $form->check_box('filter', 'Низкочастотный фильтр');
    
    echo $form->begin_fieldset('Прейскурант');
    echo $form->money('activation_fee', 'Подключение', array('required' => true), array('class' => 'input-small'));
    echo $form->money('subscription_fee', 'Абонентская плата', array('required' => true), array('class' => 'input-small'));
    echo $form->end_fieldset();
    
    echo $form->begin_fieldset('Дополнительные поля');
    echo $form->check_box('with_justification', 'Обоснование тарифа');
    echo $form->check_box('with_ends_on', 'Завершение тарифа');
    echo $form->end_fieldset();
  }
?>