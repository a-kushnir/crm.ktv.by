<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Поступления' => url_for('billing'), 
  $billing_file['file_name'] => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php if(has_access('billing/rollback_file')) {
  $datetime = date(MYSQL_DATE, strtotime('-30 days')).' 00:00:00';
  $query = "SELECT id FROM billing_files WHERE id = '".mysql_real_escape_string($id)."' AND created_at > '".mysql_real_escape_string($datetime)."'";
  if ($factory->connection->execute_scalar($query) != null) {
?><a href="/billing/<?php echo $billing_file['id']; ?>/rollback_file" class="btn btn-danger" data-method="post" data-confirm="Вы уверены, что хотите УДАЛИТЬ этот файл?">Откатить</a>
<?php }} ?>

<?php if ($billing_file['unhandled']) {
 echo $full_file ? 
  '<a class="btn" href="/billing/'.$id.'/file_details"><i class="icon-list"> </i> Проблемные записи</a>' :
  '<a class="btn" href="/billing/'.$id.'/file_details?full"><i class="icon-list"> </i> Все записи</a>';
  if (!$full_file) {
    $bfls = $billing_file_logs;
    $billing_file_logs = array();
    foreach($bfls as $bfl)
      if (!$bfl['ours'] && (!$bfl['has_request'] || $bfl['subscriber_id']))
        $billing_file_logs[] = $bfl;
  }
}; ?>

<?php echo '<a class="btn" href="javascript://" onclick="window.open(\'/billing/'.$id.'/original_file.txt\', \'ts_print_version_\'+Math.random());"><i class="icon-file"> </i> Исходный файл</a>' ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<div class="well">
<div class="row">
<div class="offset2 span8">
<p>
  <?php 
    echo
      '<b>'.$billing_file['billing_source'].'</b> ордер <b>'.
      $billing_file['order_code'].'</b> за <b>'.
      human_date($billing_file['order_date']).'</b> на сумму <b>'.
      format_money($billing_file['order_fee']).'</b> (или <b>'.format_money($billing_file['total_fee']).'</b> без комиссии)<br>'.
      'обработан <b>'.human_datetime($billing_file['updated_at']).'</b> за <b>'.
      (parse_db_datetime($billing_file['updated_at'])-parse_db_datetime($billing_file['created_at'])).'</b> сек, '.
      'при этом <b>'.
      $billing_file['success_count'].'</b> записей успешно и <b>'.
      $billing_file['failed_count'].'</b> с ошибками';
  ?>
</p>
</div>
</div>
</div>

<?php if (count($billing_file_logs) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="align-right">Строка</th>
  <th>Сообщение</th>
  <th class="align-right">Дата</th>
  <th class="align-right">Сумма</th>
  <th>Наши данные</th>
  <th>Их данные</th>
  </thead>
  <tbody>
<?php 
foreach($billing_file_logs as $log_entry) {
  echo '<tr>'.
  '<td class="align-right align-middle">'.$log_entry['line'].'</td>'.
  '<td class="align-middle">'.($log_entry['level'] == 'E' ? '<span class="badge badge-important">ошибка</span> ' : ($log_entry['level'] == 'W' ? '<span class="badge badge-warning">замечание</span> ' : '')).$log_entry['message'].'</td>'.
  '<td class="align-right align-middle">'.($log_entry['actual_date'] ? human_date($log_entry['actual_date']) : '&nbsp;').'</td>'.
  '<td class="align-right align-middle">'.($log_entry['value'] ? format_money($log_entry['value']): '&nbsp;').'</td>'.
  '<td class="align-middle">'.
    ($log_entry['ours'] ? 
      link_to('/billing/'.$log_entry['billing_account_id'].'/account_details', nl2br($log_entry['ours'])) : 
      ($log_entry['value'] ? 
        link_to('/billing/'.$log_entry['id'].'/fix_file_detail', 'Исправить вручную', array('class' => 'btn')).
        ($log_entry['subscriber_id'] ? ' <i class="icon-ok" title="Закрытая заявка"> </i>' : ($log_entry['has_request'] ? ' <i class="icon-remove" title="Открытая заявка"> </i>' : '')) : 
        '')).
  '</td>'.
  '<td class="align-middle">'.($log_entry['theirs'] ? nl2br($log_entry['theirs']) : '&nbsp;').'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>
