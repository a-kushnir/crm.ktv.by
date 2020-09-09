<?php 
if (!isset($subscriber))
  $subscriber = null;
  $billing_file_log_id = get_object_value($_GET, 'billing_file_log_id'); 

if ($subscriber) {
  echo '<div class="control-group readonly_string">
<label class="control-label">Абонент</label>
<div class="controls"><div class="input">'.link_to('/subscribers/'.$subscriber['id'], $subscriber->name()).'</div></div>
</div>';
  
  echo '<div class="control-group readonly_string">
<label class="control-label">Тарифный план</label>
<div class="controls"><div class="input">'.format_tariff($subscriber->billing_tariff()).'</div></div>
</div>';

  echo money_field('subscriber', "actual_balance", "Баланс", array('read_only' => true));  
}  
include '_requests.php';
if (!isset($_GET['readonly'])) {
  echo phone_field('request', 'home_phone', 'Домашний', null, array('value' => $subscriber['home_phone']));
  echo phone_field('request', 'cell_phone', 'Мобильный', null, array('value' => $subscriber['cell_phone']));
}
?>