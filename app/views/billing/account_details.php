<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php 
  if(has_access('subscriber/new_billing') && $layout != 'print')
    echo '<a class="btn" href="/billing/'.$id.'/new_account_detail"><i class="icon-plus-sign"></i> Движение по счету</a>';
  echo print_version_button();
?>
</div>

<?php echo page_header($title, $subtitle); ?>

<div class="well">
  <p><?php if (get_class($owner) == 'Subscriber') echo $layout == 'print' ? format_name($owner) : link_to('/subscribers/'.$owner['id'], $owner->name()); ?></p>
  <p><?php echo (has_access('house/show') ? 
    link_to(url_for('houses', 'show', $owner['house_id'] ? $owner['house_id'] : $owner['id']), format_address($owner)) : 
    format_address($owner)); ?></p>
</div>

<?php if (count($billing_details) == 0) {
  echo  table_no_data_tag();
} else { ?>

<h3>Сводка</h3>

<?php 
  $groups = array();
  foreach($billing_details as $billing_detail) {
    $group = $billing_detail['billing_detail_type'];
    if (!isset($groups[$group])) $groups[$group] = 0;
    $groups[$group] += $billing_detail['value'];
  }
?>

<table class="table table-condensed table-hover">
<thead>
  <th>Входящий остаток на <?php echo human_date($from_date); ?>:</td>
  <th class="align-right"><?php echo format_money($from_money); ?></td>
</thead>
</table>

<?php
  $groups = array();
  foreach($billing_details as $bd) {
    $group_key = $bd['billing_detail_type_id'];
    
    if (!isset($groups[$group_key]))
      $groups[$group_key] = array(
        'name' => $bd['billing_detail_type'],
        'count' => 0,
        'amount' => 0
      );
    
    $groups[$group_key]['count']++;
    $groups[$group_key]['amount'] += $bd['value'];
  }
?>
<table class="table table-bordered table-striped table-condensed table-hover">
<thead>
  <th>Тип операции</th>
  <th class="align-right min-width nobr">Кол-во</th>
  <th class="align-right min-width">Сумма</th>
</thead>
<tbody>
<?php 
$count = 0;
$amount = 0;
foreach($groups as $name => $group) {
  $count += $group['count'];
  $amount += $group['amount'];
  echo '<tr>'.
  '<td>'.$group['name'].'</td>'.
  '<td class="align-right nobr">'.$group['count'].'</td>'.
  '<td class="align-right nobr">'.format_money($group['amount']).'</td>'.
  '</tr>';
}
?>
</tbody>
<tfoot>
  <td class="align-right">Итого</td>
  <td class="align-right"><?php echo $count; ?></td>
  <td class="align-right nobr"><?php echo format_money($amount); ?></td>
</tfoot>
</table>

<table class="table table-condensed table-hover">
<thead>
  <th>Исходящий остаток на <?php echo human_date($to_date); ?>:</td>
  <th class="align-right"><?php echo format_money($to_money); ?></td>
</thead>
</table>


<h3>Детализация</h3>

<table class="table table-condensed table-hover">
<thead>
  <th>Входящий остаток на <?php echo human_date($from_date); ?>:</td>
  <th class="align-right"><?php echo format_money($from_money); ?></td>
</thead>
</table>


<table class="table table-bordered table-striped table-condensed table-hover">
<thead>
  <th class="align-right min-width">Дата</th>
  <th class="align-right" style="width:10%;">Расход</th>
  <th class="align-right" style="width:10%;">Приход</th>
  <th class="align-right" style="width:10%;">Баланс</th>
<?php if (get_class($owner) == 'House') { ?>
  <th class="align-right min-width">Кв.</th>
<?php } ?>
  <th style="padding-left:20px;">Операция</th>
</thead>
<tbody>
<?php 
$debet = 0;
$credit = 0;

foreach($billing_details as $bd) {
  if($bd['value'] < 0) $debet += $bd['value'];
  if($bd['value'] > 0) $credit += $bd['value'];

  $theirs_text = "";
  if (has_access('subscriber/detailed_billing') && $bd['theirs']) {
    $theirs = explode("\n", $bd['theirs']);
    if (isset($theirs[0]) && $theirs[0]) $theirs_text .= "<b>Лицевой счет:</b><br>".$theirs[0]."<br>";
    if (isset($theirs[1]) && $theirs[1]) $theirs_text .= "<b>Абонент:</b><br>".$theirs[1]."<br>";
    if (isset($theirs[1]) && $theirs[1]) $theirs_text .= "<b>Адрес:</b><br>".$theirs[2];
  }
  
  $balance = $debet+$credit;
  echo '<tr>'.
  '<td class="align-right nobr">'.human_date($bd['actual_date']).'</td>'.
  '<td class="align-right nobr">'.($bd['value'] < 0 ? format_money(-$bd['value']) : '&nbsp;').'</td>'.
  '<td class="align-right nobr">'.($bd['value'] > 0 ? format_money($bd['value']) : '&nbsp;').'</td>'.
  '<td class="align-right nobr '.($balance < 0 ? 'minus-balance' : ($balance > 0 ? 'plus-balance' : 'zero-balance')).'">'.format_money($balance).'</td>';
  
  if (get_class($owner) == 'House')
    echo '<td class="align-right nobr">'.link_to(url_for('subscribers', 'show', $bd['subscriber_id']), $bd['apartment']).'</td>';
  
  echo '<td style="padding-left:20px;">'.
    (has_access('subscriber/detailed_billing') && $bd['billing_file_id'] ? '<a class="tooltip-balloon" data-content="'.
      $theirs_text.
      '" rel="popover" href="/billing/'.$bd['billing_file_id'].'/file_details?full" data-original-title="'.$bd['file_name'].'">'.$bd['comment'].'</a>' :
    $bd['comment']).
    ($bd->rollbackable() ? '<a href="/'.(get_class($owner) == 'Subscriber' ? 'subscribers' : 'houses').'/'.$id.'/billing/'.$bd['id'].'/destroy" class="btn btn-danger btn-mini" style="float:right;margin-left:5px;" data-method="post" data-confirm="Вы уверены, что хотите УДАЛИТЬ запись?">Откатить</a>' : '').
    '</td>'.
  '</tr>';
}
?>
</tbody>
<tfoot>
  <td class="align-right">Оборот</td>
  <td class="align-right nobr"><?php echo format_money(-$debet); ?></td>
  <td class="align-right nobr"><?php echo format_money($credit); ?></td>
  <td class="align-right nobr"><?php echo format_money($debet+$credit); ?></td>
  <td <?php echo get_class($owner) == 'House' ? 'colspan="2"' : null ?>>&nbsp;</td>
</tfoot>
</table>
<table class="table table-condensed table-hover">
<thead>
  <th>Исходящий остаток на <?php echo human_date($to_date); ?>:</td>
  <th class="align-right"><?php echo format_money($to_money); ?></td>
</thead>
</table>

<?php } ?>