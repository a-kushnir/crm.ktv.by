<?php if (count($subscribers) == 0) {
  echo  table_no_data_tag();
} else { ?>

<?php if (false && !$mobile_version && $layout != 'print') { ?>
<div id="chart_div" style="height:200px;"></div>
<?php } ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th></th>
  <th colspan="3" class="align-center">Заявки</th>
  <th colspan="3" class="align-center">Абоненты</th>
  </thead>
  <thead>
  <th class="align-center">Дата</th>
  <th class="align-right">Подключений</th>
  <th class="align-right">Отключений</th>
  <th class="align-right">Движение</th>
  <th class="align-right">Подключений</th>
  <th class="align-right">Отключений</th>
  <th class="align-right">Движение</th>
  </thead>
  <tbody>
<?php 
$req_starts = 0;
$req_ends = 0;
$sub_starts = 0;
$sub_ends = 0;
foreach($subscribers as $subscriber) {
  $req_starts += $subscriber['req_starts'];
  $req_ends += $subscriber['req_ends'];
  $sub_starts += $subscriber['sub_starts'];
  $sub_ends += $subscriber['sub_ends'];
  
  $delta_req = $subscriber['req_starts'] - $subscriber['req_ends'];
  $delta_sub = $subscriber['sub_starts'] - $subscriber['sub_ends'];
  echo '<tr>'.
  '<td class="align-center">'.human_date($subscriber['actual_date']).'</td>'.
  '<td class="align-right">'.($subscriber['req_starts'] ? '+'.$subscriber['req_starts'] : '').'</td>'.
  '<td class="align-right">'.($subscriber['req_ends'] ? -$subscriber['req_ends'] : '').'</td>'.
  '<td class="align-right '.delta_class($delta_req).'">'.($delta_req > 0 ? '+' : '').$delta_req.'</td>'.
  '<td class="align-right">'.($subscriber['sub_starts'] ? '+'.$subscriber['sub_starts'] : '').'</td>'.
  '<td class="align-right">'.($subscriber['sub_ends'] ? -$subscriber['sub_ends'] : '').'</td>'.
  '<td class="align-right '.delta_class($delta_sub).'">'.($delta_sub > 0 ? '+' : '').$delta_sub.'</td>'.
  '</tr>';
}
?>
</tbody>
<thead>
<tr>
<th class="align-right" colspan="1">Итого</th>
<th class="align-right">+<?php echo $req_starts; ?></th>
<th class="align-right">-<?php echo $req_ends; ?></th>
<th class="align-right <?php echo delta_class($req_starts - $req_ends) ?>" nowrap=""><?php $delta = $req_starts - $req_ends; echo ($delta > 0 ? '+' : '').$delta; ?></th>
<th class="align-right">+<?php echo $sub_starts; ?></th>
<th class="align-right">-<?php echo $sub_ends; ?></th>
<th class="align-right <?php echo delta_class($sub_starts - $sub_ends) ?>" nowrap=""><?php $delta = $sub_starts - $sub_ends; echo ($delta > 0 ? '+' : '').$delta; ?></th>
</tr>
</thead>
</table>

<?php } ?>
