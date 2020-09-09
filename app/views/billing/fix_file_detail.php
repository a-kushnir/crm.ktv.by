<script type="text/javascript">var billing_file_log_id = <?php echo $id; ?></script>
<?php $javascripts[] = '/javascripts/billing_file_logs.js' ?>

<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Поступления' => url_for('billing'), 
  $billing_file_log['file_name'] => url_for('billing', 'file_details', $billing_file_log['billing_file_id']),
  'Ручное исправление' => null,
)); ?>

<?php echo search_button('', isset($_SESSION['selected_region']) ? ' по '.$_SESSION['selected_region']['name'] : ''); ?>
<?php echo page_header($title, $subtitle); ?>

<div class="row">
<div class="span3">
<div class="form-vertical">
<h3>Платеж <small>полученная информация</small></h3>
<?php
$form = new FormBuilder('billing_file_log', true);

$theirs = explode("\n", $billing_file_log['theirs']);
if (isset($theirs[0]) && $theirs[0]) echo $form->text('account', 'Лицевой счет', null, array('value' => $theirs[0]));
if (isset($theirs[1]) && $theirs[1]) echo $form->text('subscriber', 'Абонент', null, array('value' => $theirs[1]));
if (isset($theirs[2]) && $theirs[2]) echo $form->text('address', 'Адрес', null, array('value' => $theirs[2]));

echo $form->money('value', 'Сумма');
echo $form->date('actual_date', 'Дата');
echo $form->text('payment_comment', 'Пометка');
?>
</div>
</div>
<div class="span9">

<h3>Начислить абоненту <small>воспользуйтесь поиском и нажмите начислить</small></h3>

<?php if (count($subscribers) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Абонент</th>
  <th>Адрес</th>
  <th class="align-right">Лиц. счет</th>
  <th class="align-right">Баланс</th>
  <th class="align-right">Подписан</th>
  <th class="align-right">Расторгнут</th>
  <th style="width:1px;">Действия</th>
  </thead>
  <tbody>
<?php 
foreach($subscribers as $subscriber) {
  echo '<tr>'.
  '<td>'.link_to(url_for('subscribers', 'show', $subscriber['id']), search_highlight_part($subscriber->name()), array('class' => ($subscriber['active'] ? 'record-active' : 'record-terminated'))).'</td>'.
  '<td>'.search_highlight_part(format_address($subscriber)).'</td>'.
  '<td class="align-right">'.search_highlight_full($subscriber['lookup_code']).'</td>'.
  '<td class="align-right">'.format_money($subscriber['actual_balance']).'</td>'.
  '<td class="align-right">'.human_date($subscriber['starts_on']).'</td>'.
  '<td class="align-right">'.human_date($subscriber['ends_on']).'</td>'.
  '<td><a href="/billing/'.$id.'/fix_file_detail?subscriber='.$subscriber['id'].'" class="btn btn-primary btn-mini" data-method="post"
data-confirm="Вы подтверждаете движение по счету?'."\n\n".
((isset($theirs[0]) && $theirs[0]) ? $theirs[0]."\n" : '').
((isset($theirs[1]) && $theirs[1]) ? $theirs[1]."\n" : '').
((isset($theirs[2]) && $theirs[2]) ? $theirs[2]."\n" : '')."\n".
((isset($theirs[0]) && $theirs[0]) ? $subscriber['lookup_code']."\n" : '').
((isset($theirs[1]) && $theirs[1]) ? format_name($subscriber)."\n" : '').
((isset($theirs[2]) && $theirs[2]) ? format_address($subscriber, true)."\n" : '').
($subscriber['active'] ? '' : "\n".format_date($subscriber['ends_on'])." АБОНЕНТ ОТКЛЮЧЕН").
'">Начислить</a></td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php echo pagination($records, Subscriber::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>

<form method="post" class="form-horizontal">

<?php if ($request && !$request->is_new()) { ?>
<h3>Заявка на подключение</h3>    
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Принята</th>
  <th>Адрес</th>
<?php if ($request['subscriber_id']) { ?>
  <th>Закрыта</th>
  <th>Абонент</th>
  <th class="align-right">Лицевой счет</th>
<?php } ?>
  <th style="width:1px;"></th>
  </thead>
<?php     
    echo '<tr>'.
    '<td nowrap>'.link_to_show('requests', $request['id'], human_date($request['created_at'])).'</td>'.
    '<td nowrap>'.format_address($request).'</td>';
if ($request['subscriber_id']) {
    echo '<td nowrap>'.human_date($request['handled_on']).'</td>'.
    '<td nowrap>'.link_to_show('subscribers', $request['subscriber_id'], format_name($request)).'</td>'.
    '<td class="align-right" nowrap>'.link_to(url_for('billing', 'account_details', $request['billing_account_id']), $request['lookup_code']).'</td>'.
    '<td><a href="/billing/'.$id.'/fix_file_detail?subscriber='.$request['subscriber_id'].'" class="btn btn-primary btn-mini" data-method="post"
data-confirm="Вы подтверждаете движение по счету?'."\n\n".
((isset($theirs[0]) && $theirs[0]) ? $theirs[0]."\n" : '').
((isset($theirs[1]) && $theirs[1]) ? $theirs[1]."\n" : '').
((isset($theirs[2]) && $theirs[2]) ? $theirs[2]."\n" : '')."\n".
((isset($theirs[0]) && $theirs[0]) ? $request['lookup_code']."\n" : '').
((isset($theirs[1]) && $theirs[1]) ? $request->name()."\n" : '').
((isset($theirs[2]) && $theirs[2]) ? format_address($request)."\n" : '').
'">Начислить</a>&nbsp;<a href="/billing/'.$id.'/dismiss_request" class="btn btn-danger btn-mini" data-method="post">Освободить</a></td>';
} else {
  echo '<td><a href="/billing/'.$id.'/dismiss_request" class="btn btn-danger btn-mini" data-method="post">Освободить</a></td>';
}
  echo '</tr></table>';

  } else { ?>
<h3>Подать заявку <small>на подключение, если абонент не найден</small></h3>
<?php
  echo apartment_address_field('request', "Адрес", array('required' => true));
  echo '<div id="subscriber_container">';
  echo '</div>';
?>
  <div class="form-actions">
    <input class="btn btn-primary" type="submit" value="Подать">
  </div>
<?php } ?>
</form>

</div>
</div>