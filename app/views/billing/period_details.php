<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Платежные периоды' => url_for('billing'), 
  $subtitle => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php
  if (has_access('billing/download_memo') && $billing_period['finished_at'])
    echo '<a class="btn" href="/billing/'.$id.'/memos.rtf"><i class="icon-download-alt"></i> Скачать памятки</a>';
  if (has_access('billing/send_messages') && $billing_period['finished_at'])
    echo (!$billing_period['sms_sent_at'] ? '<a class="btn" href="/billing/'.$id.'/send_sms" rel="nofollow" data-method="post" data-confirm="После этого откатить период будет невозможно. Продолжить?"><i class="icon-envelope"></i> Сформировать смс</a>' : '');
  
 echo $full_file ? 
  '<a class="btn" href="/billing/'.$id.'/period_details"><i class="icon-list"> </i> Краткий отчет</a>' :
  '<a class="btn" href="/billing/'.$id.'/period_details?full"><i class="icon-list"> </i> Полный отчет</a>';
?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($billing_details) == 0) {
  echo  table_no_data_tag();
} else { ?>

<h3>Сводка</h3>

<?php 
  $groups = array();
  foreach($billing_details as $billing_detail) {
    $group = $billing_detail['billing_tariff_id'];
    if (!isset($groups[$group])) $groups[$group] = array('count' => 0, 'amount' => 0);
    $groups[$group]['count'] += 1;
    $groups[$group]['amount'] += $billing_detail['value'];
  }
?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Тарифный план</th>
  <th>Город</th>
  <th class="align-right">Ежемесячно</th>
  <th class="align-right">Абонентов</th>
  <th class="align-right">Начислено</th>
  </thead>
  <tbody>
<?php 
$total_amount = 0;
$total_count = 0;
foreach($groups as $group => $value) {
  $total_amount += $value['amount'];
  $total_count += $value['count'];
  
  $s = new Subscriber();
  $s['billing_tariff_id'] = $group;
  $billing_tariff = $s->billing_tariff();
  
  echo '<tr>'.
  '<td>'.format_tariff($billing_tariff).'</td>'.
  '<td>'.$billing_tariff['city'].'</td>'.
  '<td class="align-right">'.format_money(-$value['amount']/$value['count']).'</td>'.
  '<td class="align-right">'.$value['count'].'</td>'.
  '<td class="align-right">'.format_money(-$value['amount']).'</td>'.
  '</tr>';
}
?>

<tfoot>
  <td class="align-right" colspan="3">Итого</td>
  <td class="align-right"><?php echo $total_count; ?></td>
  <td class="align-right"><?php echo format_money(-$total_amount); ?></td>
</tfoot>

</tbody>
</table>

<?php if ($full_file) { ?>
<h3>Детализация</h3>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Лицевой счет</th>
  <th>Абонент</th>
  <th>Тарифный план</th>
  <th class="align-right">Начислено</th>
  <th class="align-right">Баланс</th>
  </thead>
  <tbody>
<?php 
foreach($billing_details as $billing_detail) {
  echo '<tr>'.
  '<td>'.link_to('/billing/'.$billing_detail['billing_account_id'].'/account_details', $billing_detail['lookup_code']).'</td>'.
  '<td>'.($billing_detail['subscriber_id'] ? link_to('/subscribers/'.$billing_detail['subscriber_id'], format_name($billing_detail)) : '').'</td>'.
  '<td>'.$billing_detail['billing_tariff'].'</td>'.
  '<td class="align-right">'.format_money(-$billing_detail['value']).'</td>'.
  '<td class="align-right">'.format_money($billing_detail['actual_balance']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>
<?php } ?>

<a href="/billing" class="btn">Назад</a>