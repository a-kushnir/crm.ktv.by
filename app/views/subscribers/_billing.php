<a class='btn pull-right' href='/billing/<?php echo $subscriber['billing_account_id']; ?>/account_details.print' onclick="window.open(this.href, this.href); return false;"><i class='icon icon-print'> </i> Версия для печати</a>
<?php
  echo $form->begin_div(array('class' => 'form-horizontal'));

  $cell_phone = get_object_value($subscriber, 'cell_phone');
  $home_phone = get_object_value($subscriber, 'home_phone');
  $lookup_code = get_object_value($subscriber, 'lookup_code');
  $lookup_code_hint = 
    ends_with($cell_phone, $lookup_code) ? 'совпадает с мобильным' : (
    ends_with($home_phone, $lookup_code) ? 'совпадает с домашним' : '');
  echo $form->read_only('lookup_code', 'Лицевой счет', null, array('value' => $lookup_code.($lookup_code_hint ? ' <small>'.$lookup_code_hint.'</small>' : '')));

  $tariff_ends_on = get_object_value($subscriber, 'tariff_ends_on');
  $tariff_justification = get_object_value($subscriber, 'tariff_justification');
  echo '<div class="control-group readonly_string">
<label class="control-label">Тарифный план</label>
<div class="controls"><div class="input">'.
format_tariff($subscriber->billing_tariff())
.($tariff_justification ? ' (<strong>'.$tariff_justification.'</strong>)' : '')
.($tariff_ends_on ? ' до <strong>'.human_date($tariff_ends_on).'</strong>' : '').
'</div></div>
</div>';

  echo $form->end_div();
?>

<?php if (count($billing_details) == 0) {
  echo  table_no_data_tag();
} else { ?>

<div class="accordion" id="billing_group">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#billing_group" href="#billing_summary">
        Сводка
      </a>
    </div>
    <div id="billing_summary" class="accordion-body collapse">
      <div class="accordion-inner">
<div class="row">
<div class="offset3 span6">
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

<table class="table table-condensed table-hover">
<thead>
  <th>Входящий остаток на <?php echo human_date($from_date); ?>:</td>
  <th class="align-right"><?php echo format_money($from_money); ?></td>
</thead>
</table>

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
</div>
</div>

    </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#billing_group" href="#billing_details">
        Детализация
      </a>
    </div>
    <div id="billing_details" class="accordion-body collapse">
      <div class="accordion-inner">
      
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
  '<td class="align-right nobr '.($balance < 0 ? 'minus-balance' : ($balance > 0 ? 'plus-balance' : 'zero-balance')).'">'.format_money($balance).'</td>'.
  '<td style="padding-left:20px;">'.
    (has_access('subscriber/detailed_billing') && $bd['billing_file_id'] ? '<a class="tooltip-balloon" data-content="'.
      $theirs_text.
      '" rel="popover" href="/billing/'.$bd['billing_file_id'].'/file_details?full" data-original-title="'.$bd['file_name'].'">'.$bd['comment'].'</a>' :
    $bd['comment']).
    ($bd->rollbackable() ? '<a href="/subscribers/'.$id.'/billing/'.$bd['id'].'/destroy" class="btn btn-danger btn-mini" style="float:right;margin-left:5px;" data-method="post" data-confirm="Вы уверены, что хотите УДАЛИТЬ запись?">Откатить</a>' : '').
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
  <td>&nbsp;</td>
</tfoot>
</table>

<table class="table table-condensed table-hover">
<thead>
  <th>Исходящий остаток на <?php echo human_date($to_date); ?>:</td>
  <th class="align-right"><?php echo format_money($to_money); ?></td>
</thead>
</table>

      </div>
    </div>
  </div>
</div>

<?php } ?>

<?php if(has_access('subscriber/new_billing') && isset($billing_detail)) { ?>

<div id='new_billing_button' class='form-actions'  <?php if (count($billing_detail->errors) > 0) echo "style='display:none;'" ?>>
<a href='#billing' class='btn btn-large btn-success' onclick='javascript:$("#new_billing_button").hide();$("#new_billing_form").show();'>Добавить</a>
</div>

<div id='new_billing_form' <?php if (count($billing_detail->errors) == 0) echo "style='display:none;'" ?>>
<?php 
  $form = new FormBuilder('billing_detail');
  echo $form->begin_form('/subscribers/'.$id.'/billing/new#billing', array('class' => 'form-horizontal'));
?>
<fieldset>
<legend>Новая транзакция</legend>
<?php
  echo $form->money('value', 'Сумма', array('required' => true), array('class' => 'input-small'));
  echo $form->date('actual_date', 'Дата', array('required' => true));
  echo $form->select('billing_detail_type_id', 'Тип операции', get_field_collection(BillingDetail::manual_types(), 'id', 'name', ''), array('required' => true));
  echo $form->text('comment', 'Описание', array('required' => true, 'hint'=>'Пополнение счета, Бесплатное подключение и т.д.'));
?>
<div class='form-actions'>
  <a href='#billing' class='btn btn-large' onclick='javascript:$("#new_billing_form").hide();$("#new_billing_button").show();'>Отмена</a>
  <?php echo submit_button('Создать'); ?>
</div>
</fieldset>
<?php echo $form->end_form(); ?>
</div>

<?php } ?>