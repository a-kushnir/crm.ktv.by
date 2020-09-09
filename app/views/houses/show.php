<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php if (isset($house)) echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php
  if (has_access('house/billing') && $layout != 'print') echo '<a class="btn" href="/houses/'.$id.'/memos.rtf"><i class="icon-download-alt"></i> Памятки</a>';
  if ($house['billing_account_id']) {
    echo '<a href="/houses/'.$id.'/subscribers" class="btn"><i class="icon icon-user"> </i> Абоненты</a>';
    echo '<a href="/billing/'.$house['billing_account_id'].'/account_details" class="btn"><i class="icon icon-list"> </i> Биллинг</a>';
  }
  echo print_version_button('&debtors', 'Должники');
  echo print_version_button();
?>
</div>

<?php echo page_header($title, $subtitle); ?>

<div class="form-horizontal">
<?php
if (isset($house)) {
  echo render_house_legend();
  echo render_house($house);
} else {
  echo render_house_legend();
  foreach ($houses as $house) {
    echo '<h3>'.format_address($house).'</h3>';
    echo render_house($house);
  }
}
?>

<?php if ($layout != 'print') {
  $audit_record = $house;
  include APP_ROOT.'/app/views/layouts/_audit.php';
} ?>

<?php if ($layout == 'print' && isset($debtors) && $debtors) {
$subscribers = Subscriber::evil_subscribers($house['id']);
if (count($subscribers) != 0) {
?>
<h4>Должники</h4>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="align-right">Кв.</th>
  <th>ФИО</th>
  <th class="align-center">Мобильный</th>
  <th class="align-center">Домашний</th>
  <th class="align-center">Договор</th>
  <th>Тариф</th>
  <th class="align-right">Долг</th>
  </thead>
  <tbody>
<?php 
foreach($subscribers as $sub) {
  $subscriber = Subscriber::load($sub['id']);
  echo '<tr>'.
  '<td class="align-right">'.$subscriber['apartment'].'</td>'.
  '<td>'.format_name($subscriber).'</td>'.
  '<td class="align-center" nowrap>'.format_phone($subscriber['cell_phone']).'</td>'.
  '<td class="align-center" nowrap>'.format_phone($subscriber['home_phone']).'</td>'.
  '<td class="align-center">'.$subscriber['lookup_code'].'</td>'.
  '<td>'.$subscriber['billing_tariff'].'</td>'.
  '<td class="align-right" nowrap>'.format_money(-$subscriber['actual_balance']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php }
  } ?>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_back(url_for('houses')) ?>
  <?php if (has_access('house/edit')) echo link_to_edit('houses', $id) ?>
  <?php if (has_access('house/destroy') && $house->has_subscribers() == null) { echo link_to_destroy('houses', $id); } ?>
</div>
<?php } ?>
</div>
