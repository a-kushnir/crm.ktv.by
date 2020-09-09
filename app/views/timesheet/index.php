<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button('&worker='.$worker_id.'&from='.$from_date.'&to='.$to_date.($include_paid ? '&include=paid' : '')); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<form method="post" id="timesheet" action="/nowhere">

<?php 
if ($layout != 'print') {
  $javascripts[] = '/javascripts/timesheet.js';
  
  $html = '<div class="well form-inline">';
  
  $html.= '<select id="worker" class="input-large">';
  $total = 0;
  $html.= '<option value="">&lt;Выберите работника&gt;</option>';
  $html.= '<option value="all" '.('all' == $worker_id ? 'selected' : '').'>Все работники</option>';
  foreach($workers as $worker)
    $html.= "<option value='".$worker['id']."' ".($worker['id'] == $worker_id ? "selected='selected'" : "").">".$worker['name']."</option>";
  $html.= '</select>';
  
  $html.= '<span class="nobr">';
  $html.= '<label for="from_date" class="contol-label" style="margin-left:10px;">с</label> ';
  $html.= '<input type="text" id="from_date" value="'.$from_date.'" class="date-picker input-small align-center">';
  $html.= '<label for="to_date" class="contol-label" style="margin-left:10px;">по</label> ';
  $html.= '<input type="text" id="to_date" value="'.$to_date.'" class="date-picker input-small align-center">';
  $html.= '</span>';
  
  $html.= '<label class="checkbox" style="margin-left:10px;">';
  $html.= '<input type="checkbox" id="include" value="paid" '.($include_paid ? 'checked' : '').'> Включая оплаченные';
  $html.= '</label>';
  
  $html.= '</div>';
  echo $html;
}
?>

<?php if (count($time_entries) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <?php if ($layout != 'print') { ?>
  <th style="width:1px;"><input type="checkbox" id="select_all_time_entries" /></th>
  <?php } ?>
  <th>Дата</th>
  <th>Работник</th>
  <th class="align-right">Множитель</th>
  <th class="align-right">Часы</th>
  <th class="align-right">Сумма</th>
  </thead>
  <tbody>
<?php 
$total_hours = 0;
$total_sum = 0;
$grand_total_hours = 0;
$grand_total_sum = 0;

$has_both = false;
$is_paid = $time_entries[0]['is_paid'];

foreach($time_entries as $index => $time_entry) {  
  if ($index > 0 && !$has_both && $time_entry['is_paid'] != $is_paid) {
    $has_both = true;
    echo '<thead>'.
    '<th class="align-right" colspan="'.($layout == 'print' ? 3 : 4).'">Итого к оплате</th>'.
    '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
    '<th class="align-right" nowrap>'.format_float($total_sum, 2).'</th>'.
    '</thead>';
    $total_hours = 0;
    $total_sum = 0;
  }

  $total_hours += tofloat($time_entry['hours']);
  $grand_total_hours += tofloat($time_entry['hours']);
  $curr_sum = $time_entry['multiply'] ? tofloat($time_entry['multiply']) * tofloat($time_entry['hours']) : null;
  if ($curr_sum) {
    $total_sum += $curr_sum;
    $grand_total_sum += $curr_sum;
  }
  
  echo '<tr>'.
  ($layout != 'print' ? '<td><input type="checkbox" name="time_entries['.$time_entry['id'].']" class="time_entry" /></td>' : '').
  '<td class="align-middle'.($time_entry['is_paid'] ? ' handled-request' : '').'">'.($layout == 'print' ? format_date($time_entry['actual_date']) : link_to(url_for('timesheet', 'edit', $time_entry['id']), human_date($time_entry['actual_date']))).'</td>'.
  '<td class="align-middle">'.$time_entry['name'].'</td>'.
  '<td class="align-middle align-right">'.($time_entry['multiply'] ? format_float($time_entry['multiply'], 2) : null).'</td>'.
  '<td class="align-middle align-right">'.format_float($time_entry['hours'], 2).'</td>'.
  '<td class="align-middle align-right">'.($curr_sum ? format_float($curr_sum, 2) : null).'</td>'.
  '</tr>';
}
  echo '<thead>'.
  '<th class="align-right" colspan="'.($layout == 'print' ? 3 : 4).'">Итого '.($time_entries[count($time_entries)-1]['is_paid'] ? 'оплачено' : 'к оплате').'</th>'.
  '<th class="align-right">'.format_float($total_hours, 2).'</th>'.
  '<th class="align-right" nowrap>'.format_float($total_sum, 2).'</th>'.
  '</thead>';
  
  if ($has_both) {
    echo '<thead>'.
    '<th class="align-right" colspan="'.($layout == 'print' ? 3 : 4).'">Итого за период</th>'.
    '<th class="align-right">'.format_float($grand_total_hours, 2).'</th>'.
    '<th class="align-right" nowrap>'.format_float($grand_total_sum, 2).'</th>'.
    '</thead>';
  }
?>
</tbody>
</table>

<?php } ?>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <?php if (has_access('timesheet/new')) echo link_to_new('timesheet'); ?>
  <?php if (has_access('timesheet/mark_paid')) { ?>
  <input type="submit" value="Оплатить" class="btn btn-large btn-primary" style="display:none;" onclick="$('#timesheet').attr('action', '<?php echo TimesheetController::url_with_filter('/timesheet/set_paid') ?>');" />
  <?php } ?>
  <?php if(has_access('timesheet/destroy')) { ?>
  <input type="submit" value="Удалить" class="btn btn-large btn-danger" style="display:none;" onclick="$('#timesheet').attr('action', '<?php echo TimesheetController::url_with_filter('/timesheet/destroy') ?>');" />
  <?php } ?>
</div>
<?php } ?>

</form>