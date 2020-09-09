<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>

<?php if (count($billing_sources) == 0) {
  echo  table_no_data_tag();
} else { ?>

<h3>Сборы</h3>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Агент</th>
  <th class="align-right">Пл.</th>
  <th class="align-right">Сумма</th>
  <th class="align-right">%</th>
  </thead>
  <tbody>
<?php 
$total_sum = 0;
foreach($billing_sources as $billing_source) { $total_sum += $billing_source['order_fee']; }
foreach($billing_sources as $billing_source) {
  echo '<tr>'.
  '<td>'.$billing_source['billing_source'].'</td>'.
  '<td class="align-right">'.$billing_source['total_count'].'</td>'.
  '<td class="align-right">'.format_money($billing_source['order_fee']).'</td>'.
  '<td class="align-right">'.format_percent($billing_source['order_fee'] / $total_sum, 0).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>


</div>
<div class="span9">

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<a class="btn" href="<?php echo '/billing/download_report?from='.$from_date.'&to='.$to_date ?>"><i class="icon-download-alt"> </i> Скачать</a>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
if ($layout != 'print') {
  $javascripts[] = '/javascripts/timesheet.js';
  
  $html = '<div class="well form-inline">';
  
  $html.= '<span class="nobr">';
  $html.= '<label for="from_date" class="contol-label" style="margin-left:10px;">с</label> ';
  $html.= '<input type="text" id="from_date" value="'.$from_date.'" class="date-picker input-small align-center">';
  $html.= '<label for="to_date" class="contol-label" style="margin-left:10px;">по</label> ';
  $html.= '<input type="text" id="to_date" value="'.$to_date.'" class="date-picker input-small align-center">';
  $html.= '</span>';
  
  $html.= '</div>';
  echo $html;
}
?>

<?php if (count($billing_files) == 0) {
  echo  table_no_data_tag();
} else { ?>

<?php if (!$mobile_version && $layout != 'print') { ?>
<div id="chart_div" style="height:200px;"></div>
<?php } ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Дата</th>
  <th class="align-right">Платежей</th>
  <th class="align-right">Сумма</th>
  <th class="align-right">Сумма -<?php echo format_percent($tax_on_turnover,2); ?></th>
  </thead>
  <tbody>
<?php 
$total_count = 0;
$order_fee = 0;
foreach($billing_files as $billing_file) {
  $total_count += $billing_file['total_count'];
  $order_fee += $billing_file['order_fee'];

  echo '<tr>'.
  '<td>'.format_date($billing_file['order_date']).'</td>'.
  '<td class="align-right">'.$billing_file['total_count'].'</td>'.
  '<td class="align-right">'.format_money($billing_file['order_fee']).'</td>'.
  '<td class="align-right">'.format_money($billing_file['order_fee'] * (1-$tax_on_turnover)).'</td>'.
  '</tr>';
}
?>
</tbody>
<thead>
<tr>
<th class="align-right" colspan="1">Итого</th>
<th class="align-right"><?php echo $total_count; ?></th>
<th class="align-right" nowrap=""><?php echo format_money($order_fee); ?></th>
<th class="align-right" nowrap=""><?php echo format_money($order_fee * (1-$tax_on_turnover)); ?></th>
</tr>
</thead>
</table>

<?php } ?>

</div>
</div>

<?php if (!$mobile_version && $layout != 'print') { ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  google.load('visualization', '1.0', {'packages':['corechart']});
  google.setOnLoadCallback(drawChart);

  function drawChart() {

  var data = new google.visualization.DataTable();
  data.addColumn('date', 'Дата');
  data.addColumn('number', 'Сумма');
  data.addRows([
<?php 
foreach($billing_files as $billing_file) {
  $total_count += $billing_file['total_count'];
  $order_fee += $billing_file['order_fee'];

  echo '['.js_date($billing_file['order_date']).','.$billing_file['order_fee'].'],';
}
?>
  ]);

  var formatter = new google.visualization.DateFormat(
      {pattern:'dd.MM.yyyy'});
  formatter.format(data, 0);
  
  var formatter = new google.visualization.NumberFormat(
      {suffix: ' р.', fractionDigits:0, groupingSymbol:' '});
  formatter.format(data, 1);
  
    var options = {
      title: 'Поступления',
      hAxis: {format:'dd.MM.yyyy'}
    };

    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
</script>
<?php } ?>