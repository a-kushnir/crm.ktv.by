<?php echo search_button(); ?>

<?php echo page_header($title, $subtitle); ?>

<div class="row">
<div class="span6">
  <h3>Поступления</h3>
  
<?php if (count($billing_files) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Файл</th>
  <th>Агент</th>
  <th class="align-right">Платежей</th>
  <th class="align-right">Сумма</th>
  <th class="align-right">Дата</th>
  </thead>
  <tbody>
<?php 
foreach($billing_files as $billing_file) {
  echo '<tr>'.
  '<td>'.link_to('/billing/'.$billing_file['id'].'/file_details', search_highlight_part($billing_file['file_name'])).
  (!$billing_file['updated_at'] ? ' <span class="badge badge-important">сбой</span> ' : ($billing_file['unhandled'] > 0 ? ' <span class="badge badge-important">ошибки</span> ' : null)).
  '</td>'.
  '<td>'.search_highlight_part($billing_file['billing_source']).'</td>'.
  '<td class="align-right">'.
  ($billing_file['success_count'] > 0 ? $billing_file['success_count'] : '<span class="badge badge-important">'.$billing_file['success_count'].'</span>').
  ($billing_file['failed_count'] > 0 ? ' <span class="badge badge-important">+'.$billing_file['failed_count'].'</span>' : '').
  '</td>'.
  '<td class="align-right" nowrap>'.format_money($billing_file['total_fee']).'</td>'.
  '<td class="align-right">'.human_datetime($billing_file['created_at']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php echo pagination($records, BillingFile::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>
</div>

<div class="span6">

<a href="/billing/run_now" class="btn btn-primary btn-mini" style="float:right;margin-left:5px;">Начислить</a>
<?php if ($billing_period_rollback) { ?>
<a href="/billing/rollback_period" class="btn btn-danger btn-mini" style="float:right;margin-left:5px;" data-method="post" data-confirm="Вы уверены, что хотите УДАЛИТЬ начисленную абонентскую плату за последний месяц?">Откатить</a>
<?php } ?>

<h3>Абонентская плата</h3>

<?php if (count($billing_periods) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Период</th>
  <th class="align-right">Абонентов</th>
  <th class="align-right">Начислено</th>
  </thead>
  <tbody>
<?php 
foreach($billing_periods as $billing_period) {
  echo '<tr>'.
  '<td>'.link_to('/billing/'.$billing_period['id'].'/period_details', format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date']."-1 month")))).(!$billing_period['finished_at'] ? ' <span class="badge badge-important">Не завершен</span> ' : null).'</td>'.
  '<td class="align-right">'.$billing_period['total_subscribers'].'</td>'.
  '<td class="align-right">'.format_money($billing_period['total_subscription_fee']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

</div>

</div>