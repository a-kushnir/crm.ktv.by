<?php 
function delta_class($value) {
  return 'delta-'.($value > 0 ? 'plus' : ($value < 0 ? 'minus' : 'zero'));
}
?>
<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>

<h3>Отключения</h3>

<?php if (count($term_reasons) != 0) { ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Причина</th>
  <th class="align-right min-width">Аб.</th>
  <th class="align-right min-width">%</th>
  </thead>
  <tbody>
<?php 
$total_count = null;
foreach($term_reasons as $term_reason) { $total_count += $term_reason['total_count']; }
foreach($term_reasons as $term_reason) {
  echo '<tr>'.
  '<td>'.$term_reason['termination_reason'].'</td>'.
  '<td class="align-right">'.$term_reason['total_count'].'</td>'.
  '<td class="align-right">'.format_percent($term_reason['total_count'] / $total_count, 0).'</td>'.
  '</tr>';
}
?>
  </tbody>
</table>

<?php } ?>

<?php if (count($competitors) != 0) { ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Перешли на</th>
  <th class="align-right min-width">Аб.</th>
  <th class="align-right min-width">%</th>
  </thead>
  <tbody>
<?php 
$total_count = null;
foreach($competitors as $competitor) { $total_count += $competitor['total_count']; }
foreach($competitors as $competitor) {
  echo '<tr>'.
  '<td>'.$competitor['competitor'].'</td>'.
  '<td class="align-right">'.$competitor['total_count'].'</td>'.
  '<td class="align-right">'.format_percent($competitor['total_count'] / $total_count, 0).'</td>'.
  '</tr>';
}
?>
  </tbody>
</table>

<?php } ?>

</div>
<div class="span9">
<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button('&mode='.$mode.'&from='.$from_date.'&to='.$to_date); ?>
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
  
  $html.= '<div class="btn-group" style="float:right;">
    <a href="/reports/subscribers?mode=delta&from='.$from_date.'&to='.$to_date.'" class="btn '.($mode == 'delta' ? 'active' : '').'">Движение</a>
    <a href="/reports/subscribers?mode=starts&from='.$from_date.'&to='.$to_date.'" class="btn '.($mode == 'starts' ? 'active' : '').'">Подключения</a>
    <a href="/reports/subscribers?mode=ends&from='.$from_date.'&to='.$to_date.'" class="btn '.($mode == 'ends' ? 'active' : '').'">Отключения</a>
  </div>';

  $html.= '<input type="hidden" id="mode" value="'.$mode.'" />';
  $html.= '</div>';
  echo $html;
}
?>

<?php 
if ($mode == 'delta') {
  include APP_ROOT.'/app/views/reports/_subscribers_delta.php';
} else if ($mode == 'starts') {
  include APP_ROOT.'/app/views/reports/_subscribers_starts.php';
} else if ($mode == 'ends') {
  include APP_ROOT.'/app/views/reports/_subscribers_ends.php';
}
?>

</div>
</div>