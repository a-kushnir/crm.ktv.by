<?php /*echo breadcrumb(array(
  'Табель' => url_for('timesheet'), 
  'Отчет о проделанной работе' => null,
));*/ ?>

<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button('from='.$from_date.'&to='.$to_date); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
if ($layout != 'print') {
  $javascripts[] = '/javascripts/timesheet.js';

  $html = '<div class="well form-inline">';
  
  $html.= '<span class="nobr">';
  $html.= '<label for="from_date" class="control-label" style="margin-left:10px;">с</label> ';
  $html.= '<input type="text" id="from_date" value="'.$from_date.'" class="date-picker input-small align-center">';
  $html.= '<label for="to_date" class="control-label" style="margin-left:10px;">по</label> ';
  $html.= '<input type="text" id="to_date" value="'.$to_date.'" class="date-picker input-small align-center">';
  $html.= '</span>';
  
  $html.= '</div>';
  echo $html;
}
?>

<h3>Срез по дате</h3>

<?php if (count($daily_time_entries) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Работник</th>
  <th>Деятельность</th>
  <th>Комментарий</th>
  <th class="align-center">Оценка</th>
  <th class="align-center">Заявок</th>
  <th class="align-right">Часы</th>
  <th class="align-right">Сумма</th>
  </thead>
<?php 
$total_hours = 0;
$total_sum = 0;
$grand_total_hours = 0;
$grand_total_sum = 0;

$entires_count = 0;
$request_sum = 0;
$request_grand_sum = 0;
$grate_sum = 0;

$prev_date = null;

foreach($daily_time_entries as $index => $time_entry) {  
  if ($prev_date != $time_entry['actual_date']) {
  if ($prev_date != null) {
    echo '</tbody><thead>'.
    '<th class="align-right" colspan="3">Итого за '.human_date($prev_date).'</th>'.
    '<th class="align-center">'.format_float($grate_sum/$entires_count, 2).'</th>'.
    '<th class="align-center">'.$request_sum.'</th>'.
    '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
    '<th class="align-right">'.format_float($total_sum, 2).'</th>'.
    '</thead>';
    $total_hours = 0;
    $total_sum = 0;
    
      $entires_count = 0;
    $request_sum = 0;
    $grate_sum = 0;
  }

    echo '<thead>'.
    '<th colspan="8">'.human_date($time_entry['actual_date']).'</th>'.
    '</thead><tbody>';
  $prev_date = $time_entry['actual_date'];
  }

  $total_hours += tofloat($time_entry['hours']);
  $grand_total_hours += tofloat($time_entry['hours']);
  $curr_sum = $time_entry['multiply'] ? tofloat($time_entry['multiply']) * tofloat($time_entry['hours']) : null;
  if ($curr_sum) {
    $total_sum += $curr_sum;
    $grand_total_sum += $curr_sum;
  }
  
  echo '<tr>'.
  '<td class="align-middle">'.$time_entry['name'].'</td>'.
  '<td class="align-middle">'.$time_entry['time_activity'].'</td>'.
  '<td class="align-middle">'.nl2br($time_entry['comment']).'</td>'.
  '<td class="align-middle align-center">'.$time_entry['grade'].'</td>'.
  '<td class="align-middle align-center">'.$time_entry['requests'].'</td>'.
  '<td class="align-middle align-right">'.format_float($time_entry['hours'], 2).'</td>'.
  '<td class="align-middle align-right">'.($curr_sum ? format_float($curr_sum, 2) : null).'</td>'.
  '</tr>';
  
  $entires_count ++;
  $request_sum += $time_entry['requests'];
  $request_grand_sum += $time_entry['requests'];
  $grate_sum += $time_entry['grade'];
}

  if ($prev_date != null) {
    echo '</tbody><thead>'.
    '<th class="align-right" colspan="3">Итого за '.human_date($prev_date).'</th>'.
    '<th class="align-center">'.format_float($grate_sum/$entires_count, 2).'</th>'.
    '<th class="align-center">'.$request_sum.'</th>'.
    '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
    '<th class="align-right">'.format_float($total_sum, 2).'</th>'.
    '</thead>';
    $total_hours = 0;
    $total_sum = 0;
  }

  
echo '<thead>'.
  '<th class="align-right" colspan="4">Итого за неделю</th>'.
  '<th class="align-center">'.$request_grand_sum.'</th>'.
  '<th class="align-right">'.format_float($grand_total_hours, 2).'</th>'.
  '<th class="align-right">'.format_float($grand_total_sum, 2).'</th>'.
  '</thead>';
?>
</table>

<?php } ?>

<h3>Срез по работнику</h3>

<?php if (count($worker_time_entries) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Дата</th>
  <th>Деятельность</th>
  <th>Комментарий</th>
  <th class="align-center">Оценка</th>
  <th class="align-center">Заявок</th>
  <th class="align-right">Часы</th>
  <th class="align-right">Сумма</th>
  </thead>
<?php 
$total_hours = 0;
$total_sum = 0;
$grand_total_hours = 0;
$grand_total_sum = 0;

$entires_count = 0;
$request_sum = 0;
$request_grand_sum = 0;
$grate_sum = 0;

$prev_worker = null;
$prev_worker_name = null;

foreach($worker_time_entries as $index => $time_entry) {  
  if ($prev_worker != $time_entry['worker_id']) {
  if ($prev_worker != null) {
    echo '</tbody><thead>'.
    '<th class="align-right" colspan="3">Итого для '.$prev_worker_name.'</th>'.
    '<th class="align-center">'.format_float($grate_sum/$entires_count, 2).'</th>'.
    '<th class="align-center">'.$request_sum.'</th>'.
    '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
    '<th class="align-right">'.format_float($total_sum, 2).'</th>'.
    '</thead>';
    $total_hours = 0;
    $total_sum = 0;
    
      $entires_count = 0;
    $request_sum = 0;
    $grate_sum = 0;
  }

    echo '<thead>'.
    '<th colspan="8">'.$time_entry['name'].'</th>'.
    '</thead><tbody>';
  $prev_worker = $time_entry['worker_id'];
  $prev_worker_name = $time_entry['name'];
  }

  $total_hours += tofloat($time_entry['hours']);
  $grand_total_hours += tofloat($time_entry['hours']);
  $curr_sum = $time_entry['multiply'] ? tofloat($time_entry['multiply']) * tofloat($time_entry['hours']) : null;
  if ($curr_sum) {
    $total_sum += $curr_sum;
    $grand_total_sum += $curr_sum;
  }
  
  echo '<tr>'.
  '<td class="align-middle">'.human_date($time_entry['actual_date']).'</td>'.
  '<td class="align-middle">'.$time_entry['time_activity'].'</td>'.
  '<td class="align-middle">'.nl2br($time_entry['comment']).'</td>'.
  '<td class="align-middle align-center">'.$time_entry['grade'].'</td>'.
  '<td class="align-middle align-center">'.$time_entry['requests'].'</td>'.
  '<td class="align-middle align-right">'.format_float($time_entry['hours'], 2).'</td>'.
  '<td class="align-middle align-right">'.($curr_sum ? format_float($curr_sum, 2) : null).'</td>'.
  '</tr>';
  
  $entires_count ++;
  $request_sum += $time_entry['requests'];
  $request_grand_sum += $time_entry['requests'];
  $grate_sum += $time_entry['grade'];
}

  if ($prev_worker != null) {
    echo '</tbody><thead>'.
    '<th class="align-right" colspan="3">Итого для '.$prev_worker_name.'</th>'.
    '<th class="align-center">'.format_float($grate_sum/$entires_count, 2).'</th>'.
    '<th class="align-center">'.$request_sum.'</th>'.
    '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
    '<th class="align-right">'.format_float($total_sum, 2).'</th>'.
    '</thead>';
    $total_hours = 0;
    $total_sum = 0;
  }

  
echo '<thead>'.
  '<th class="align-right" colspan="4">Итого за неделю</th>'.
  '<th class="align-center">'.$request_grand_sum.'</th>'.
  '<th class="align-right">'.format_float($grand_total_hours, 2).'</th>'.
  '<th class="align-right">'.format_float($grand_total_sum, 2).'</th>'.
  '</thead>';
?>
</table>

<?php } ?>


</div>
</div>