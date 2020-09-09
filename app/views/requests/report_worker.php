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

<?php if (count($requests) == 0) {
  echo  table_no_data_tag();
} else { ?>

<?php
function calc_total_requests($requests, $date, $user) { 
  $count = 0;
  foreach($requests as $request)
    if ($date == $request['actual_date'] && $user == $request['worker'])
      $count++;
  return $count;
}
?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Адрес</th>
  <th></th>
  <th>Тип</th>
  <th>Комментарий</th>
  <th>Отчет</th>
  <th>Дней</th>
  </thead>
<?php 
$prev_date = null;
$prev_user = null;
$total_days = 0;

foreach($requests as $request) {  
  if ($prev_date != $request['actual_date'] || $prev_user != $request['worker']) {
    $count = calc_total_requests($requests, $request['actual_date'], $request['worker']);
    echo '<thead>'.
      '<th colspan="8">'.$request['worker'].', '.human_date($request['actual_date']).' ('.$count.')</th>'.
      '</thead><tbody>';
    $prev_date = $request['actual_date'];
    $prev_user = $request['worker'];
  }
  
  $doc_icon = null;
  if ($layout == 'print')
    $doc_icon = $request['subscriber_id'] ? ' [есть]' : ' <b>[нет]</b>';
  else
    $doc_icon = $request['subscriber_id'] ? ' <i class="icon-ok"></i>' : ' <i class="icon-remove"></i>';
  
  echo '<tr'.($request['handled_on'] ? ' class="handled-request"' : '').'>'.
  '<td class="align-middle">'.($layout != 'print' ? link_to(url_for('requests', $request['id']), format_address($request, true)) : format_address($request, true)).'</td>'.
  '<td class="align-middle align-center">'.$doc_icon.'</td>'.
  '<td class="align-middle">'.$request['request_type'].'</td>'.
  '<td class="align-middle">'.nl2br($request['comment']).'</td>'.
  '<td class="align-middle">'.nl2br($request['handled_comment']).'</td>'.
  '<td class="align-middle align-right">'.$request['wait_time'].'</td>'.
  '</tr>';
  
  $total_days += $request['wait_time'];
}

  if ($prev_date != null) {
    echo '</tbody>';
  }
  
  echo '<thead>'.
    '<th colspan="5" class="align-right">Среднее для '.count($requests).' заявок:</th>'.
    '<th class="align-right">'.format_float($total_days/count($requests), 2).'</th>'.
    '</thead><tbody>';
?>
</table>

<?php } ?>

</div>
</div>