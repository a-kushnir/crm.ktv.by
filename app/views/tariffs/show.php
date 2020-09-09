<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Тарифы' => url_for('tariffs'), 
  $billing_tariff['name'] => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php
  $form = new FormBuilder('billing_tariff', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  include '_form.php';
  
  $history = array();
  if ($billing_tariff && $billing_tariff['id'])
    $history = $billing_tariff->load_history();
?>
<?php if (count($history) != 0) { ?>
<fieldset>
<legend>История</legend>
<div class="row">
<div class="offset3 span6">
<br>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Срок действия</th>
  <th class="align-right">Абон. плата</th>
  <th class="align-right">Изменение</th>
  </thead>
  <tbody>
<?php 
foreach($history as $index => $history_record) {
  $prev_record = isset($history[$index + 1]) ? $history[$index + 1] : null;
  echo '<tr>'.
  '<td>'.format_date($history_record['starts_on']).' - '.
  ($history_record['ends_on'] ? format_date($history_record['ends_on']) : '<i>сегодня</i>').'</td>'.
  '<td class="align-right">'.format_money($history_record['subscription_fee']).'</td>'.
  '<td class="align-right">'.($prev_record ? format_percent($history_record['subscription_fee'] / $prev_record['subscription_fee'] - 1) : '').'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
</div>
</div>
</fieldset>
<?php } ?>
  <?php if ($layout != 'print') { ?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('tariffs')) ?>
    <?php if (has_access('tariff/edit') && $billing_tariff['active'])
      echo link_to_edit('tariffs', $id) ?>
    <?php if (has_access('tariff/edit') && $billing_tariff['active'] && !$billing_tariff->has_subscribers())
      echo link_to_destroy('tariffs', $id) ?>
  </div>
  <?php } ?>
<?php
  echo $form->end_div();
?>
