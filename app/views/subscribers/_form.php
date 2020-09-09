<?php $javascripts[] = '/javascripts/subscribers.js'; ?>

<?php 
$ba_form = new FormBuilder('billing_account');

if (!$form->read_only && $subscriber->self_billing()) {
  echo $form->text('last_name', 'Фамилия', array('required' => true));
  echo $form->text('first_name', 'Имя', array('required' => true));
  echo $form->text('middle_name', 'Отчество', array('required' => true));
}
echo apartment_address_field('subscriber', 'Адрес', array('required' => true, 'read_only' => !$subscriber->self_billing() || $form->read_only));
echo $form->phone('home_phone', 'Домашний', array('hide_blank' => true));
echo $form->phone('cell_phone', 'Мобильный', array('hide_blank' => true));
if ($subscriber->self_billing() && (has_access('subscriber/passport') || !$subscriber['id']))
  echo passport_field('subscriber', 'Паспорт', array('required' => true, 'read_only' => $form->read_only));

if ($subscriber->is_new()) {
  $billing_tariff = null;
  foreach($billing_tariffs as $bt)
    if ($bt['id'] == get_object_value($subscriber, 'billing_tariff_id')) {
      $billing_tariff = $bt;
      break;
    }
  
  echo $form->date('starts_on', 'Подключен', array('required' => true));
  echo billing_tariff_field('subscriber', 'billing_tariff_id', 'Тарифный план', $billing_tariffs, array('required' => true));
  echo '<div class="tariff_justification_container" '.(get_object_value($billing_tariff, 'with_justification') != 1 ? 'style="display:none;"' : '').'>'.$form->text('tariff_justification', 'Обоснование тарифа', null, array('class' => 'span4')).'</div>';
  echo '<div class="tariff_ends_on_container" '.(get_object_value($billing_tariff, 'with_ends_on') != 1 ? 'style="display:none;"' : '').'>'.$form->date('tariff_ends_on', 'Завершение тарифа').'</div>';
  echo $ba_form->text('lookup_code', '№ договора', null, array('class' => 'input-small contract_number hide-if-necessary'));
} else if (!$form->read_only) {
  echo billing_tariff_field('subscriber', 'billing_tariff_id', 'Тарифный план', $billing_tariffs, array('required' => true));
  echo '<div class="tariff_justification_container" '.(get_object_value($subscriber, 'with_justification') != 1 ? "style='display:none;'" : '').'>'.$form->text('tariff_justification', 'Обоснование тарифа', null, array('class' => 'span4')).'</div>';
  echo '<div class="tariff_ends_on_container" '.(get_object_value($subscriber, 'with_ends_on') != 1 ? "style='display:none;'" : '').'>'.$form->date('tariff_ends_on', 'Завершение тарифа').'</div>';
  if ($subscriber->self_billing()) {
    echo $ba_form->text('lookup_code', 'Лицевой счет', array('required' => true), array('class' => 'input-small contract_number'));
    echo $form->check_box('allow_calls', 'Разрешены звонки');
    echo $form->check_box('allow_sms', 'Разрешены сообщения');
  }
}

if ($form->read_only) {
  echo $form->date('starts_on', 'Подключен');

  if (count($relatives) > 0) {
    echo '<fieldset><legend>Похожие договора <small>(с тем же телефоном, адресом, лицевым счетом или номером паспорта)</small></legend>';
?>
<br>
<table class='table table-bordered table-striped table-condensed' style='margin-bottom:0;'>
  <thead>
  <th>ФИО</th>
  <th>Адрес</th>
  <th>Тарифный план</th>
  <th class='align-right'>Лицевой счет</th>
  <th class='align-right'>Баланс</th>
  <th class="align-right">Подписан</th>
  <th class="align-right">Расторгнут</th>
  </thead>
  <tbody>
<?php 
foreach($relatives as $relative) {
  echo '<tr>'.
  '<td>'.link_to('/subscribers/'.$relative['id'], $relative->name(), array('class' => ($relative['active'] ? 'record-active' : 'record-terminated'))).'</td>'.
  '<td>'.format_address($relative).'</td>'.
  '<td>'.format_tariff($relative->billing_tariff()).'</td>'.
  '<td class="align-right">'.$relative['lookup_code'].'</td>'.
  '<td class="align-right" nowrap>'.format_money($relative['actual_balance']).'</td>'.
  '<td class="align-right" nowrap>'.human_date($relative['starts_on']).'</td>'.
  '<td class="align-right" nowrap>'.human_date($relative['ends_on']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php
    echo '</fieldset>';
  }
  
  if (!get_object_value($subscriber, 'active')) {
    echo '<fieldset><legend>Расторжение</legend>';
    echo $form->date('ends_on', 'Дата');
    echo $form->text('termination_reason', 'Причина', array('hide_blank' => true));
    echo $form->text('competitor', 'Перешел на', array('hide_blank' => true));
    echo $form->text('termination_comment', 'Комментарий', array('hide_blank' => true));
    echo '</fieldset>';
  }
  
  if (has_access('subscriber/audit')) {
    $audit_record = $subscriber;
    include APP_ROOT.'/app/views/layouts/_audit.php';
  }
?>
<?php
}
?>