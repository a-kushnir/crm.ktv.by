<?php /*echo breadcrumb(array(
  'Должники' => url_for('arrears'), 
  'Отчет о звонках' => null
));*/ ?>

<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>

<h3>Сводка</h3>
<?php if (count($call_summary) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Результат</th>
  <th class="align-right">Зв.</th>
  <th class="align-right">%</th>
  </thead>
  <tbody>
<?php 
$date = null;
$total_count = 0;
foreach($call_summary as $cs) {
  if ($date != $cs['actual_date']) {
    $date = $cs['actual_date'];
    
    $count = 0;
    foreach($call_summary as $cd)
      if ($date == $cd['actual_date'])
        $count += $cd['cnt'];
        
    echo '<thead><th colspan="5">'.human_date($date).' ('.$count.')</th></thead>';
  }

  echo '<tr>'.
  '<td>'.$cs['name'].'</td>'.
  '<td class="align-right">'.$cs['cnt'].'</td>'.
  '<td class="align-right">'.format_percent($cs['cnt'] / $count, 0).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

</div>
<div class="span9">
<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
  <?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<h3>Детализация</h3>

<?php if (count($call_details) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Абонент</th>
  <th>Телефон</th>
  <th>Оператор</th>
  <th>Результат</th>
  </thead>
<?php 
$date = null;
foreach($call_details as $call) {

  if ($date != $call['actual_date']) {
    $date = $call['actual_date'];
    
    $count = 0;
    foreach($call_details as $cd)
      if ($date == $cd['actual_date'])
        $count++;
        
    echo '<thead><th colspan="5">'.human_date($date).' ('.$count.')</th></thead>';
  }

  $today = mktime(0,0,0,date("m"),date("d"),date("Y"));
  echo '<tr'.(!$call['active'] ? ' class="handled-request"' : '').'>'.
  '<td>'.link_to(url_for('subscribers', 'show', $call['subscriber_id']), format_name($call)).'</td>'.
  '<td nowrap>'.phone_with_icon($call['phone_number']).'</td>'.
  '<td>'.$call['user_name'].'</td>'.
  '<td>'.($call['promised_on'] && parse_db_date($call['promised_on']) < $today ? ($call['actual_balance'] >= -$call['subscription_fee'] ? '<i class="icon-ok"> </i> ' : '<i class="icon-remove"> </i> ') : '').
  $call['call_result'].
  ($call['promised_on'] ? ' до '.human_date($call['promised_on']).'' : '').
  ($call['request_id'] ? ' (cоздана заявка)' : '').
  ($call['new_phone_number'] ? ', исправлено на '.phone_with_icon($call['new_phone_number']).'' : '').
  '</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>

</div>
</div>