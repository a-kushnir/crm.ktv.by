<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($billing_tariffs) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Название</th>
  <th>Город</th>
  <th>Подключение</th>
  <th>Абон. плата</th>
  <th>Описание</th>
  </thead>
  <tbody>
<?php 
foreach($billing_tariffs as $billing_tariff) {
  echo '<tr>'.
  '<td>'.link_to(url_for('tariffs', 'show', $billing_tariff['id']), $billing_tariff['name']).'</td>'.
  '<td>'.$billing_tariff['city'].'</td>'.
  '<td class="align-right">'.format_money($billing_tariff['activation_fee']).'</td>'.
  '<td class="align-right">'.format_money($billing_tariff['subscription_fee']).'</td>'.
  '<td>'.$billing_tariff['description'].'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php if (has_access('tariff/edit') && $layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_new('tariffs'); ?>
</div>
<?php } ?>