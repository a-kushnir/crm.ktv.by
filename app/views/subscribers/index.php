<?php echo search_button('', isset($_SESSION['selected_region']) ?  ' по '.$_SESSION['selected_region']['name'] : ''); ?>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($subscribers) == 0) {
  echo  table_no_data_tag();
} else { ?>

<div class="hidden-phone">
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>ФИО</th>
  <th>Адрес</th>
  <th>Тарифный план</th>
  <th class="align-right">Лицевой счет</th>
  <th class="align-right">Баланс</th>
  <th class="align-right">Подписан</th>
  <th class="align-right">Расторгнут</th>
  </thead>
  <tbody>
<?php 
foreach($subscribers as $subscriber) {
  echo '<tr>'.
    '<td>'.(has_access('subscriber/show') ?
      link_to(url_for('subscribers', 'show', $subscriber['id']), search_highlight_part($subscriber->name()), array('class' => ($subscriber['active'] ? 'record-active' : 'record-terminated'))) :
      ($subscriber['active'] ? '' : '<s>').search_highlight_part($subscriber->name())).($subscriber['active'] ? '' : '</s>').'</td>'.  
    '<td>'.search_highlight_part(format_address($subscriber)).'</td>'.
    '<td>'.format_tariff($subscriber->billing_tariff()).'</td>'.
    '<td class="align-right">'.search_highlight_full($subscriber['lookup_code']).'</td>'.
    '<td class="align-right" nowrap>'.format_money($subscriber['actual_balance']).'</td>'.
    '<td class="align-right" nowrap>'.human_date($subscriber['starts_on']).'</td>'.
    '<td class="align-right" nowrap>'.human_date($subscriber['ends_on']).'</td>'.
    '</tr>';
}
?>
</tbody>
</table>
</div>

<div class="visible-phone">
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>ФИО</th>
  <th>Адрес</th>
  </thead>
  <tbody>
<?php 
foreach($subscribers as $subscriber) {
  echo '<tr>'.
    '<td>'.link_to(url_for('subscribers', 'show', $subscriber['id']), search_highlight_part($subscriber->name())).'</td>'.  
    '<td>'.search_highlight_part(format_address($subscriber)).'</td>'.
    '</tr>';
}
?>
</tbody>
</table>
</div>

<?php echo pagination($records, Subscriber::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>

<?php } ?>

<?php if (has_access('subscriber/new')) { ?>
<div class="form-actions visible-desktop">
  <?php echo link_to_new('subscribers'); ?>
</div>
<?php } ?>