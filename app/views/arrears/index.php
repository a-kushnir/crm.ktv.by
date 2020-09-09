<?php echo search_button('', isset($_SESSION['selected_region']) ?  ' по '.$_SESSION['selected_region']['name'] : ''); ?>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($subscribers) == 0) {
  echo table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>ФИО</th>
  <th>Адрес</th>
  <th>Тарифный план</th>
  <th class="align-right">Лицевой счет</th>
  <th class="align-right">Долг</th>
  </thead>
  <tbody>
<?php 
foreach($subscribers as $subscriber) {
  echo '<tr>'.
  '<td>'.link_to_show('arrears', $subscriber['id'], search_highlight_part($subscriber->name())).'</td>'.
  '<td>'.search_highlight_part(format_address($subscriber)).'</td>'.
  '<td>'.format_tariff($subscriber->billing_tariff()).'</td>'.
  '<td class="align-right">'.search_highlight_full($subscriber['lookup_code']).'</td>'.
  '<td class="align-right" nowrap>'.format_money(-$subscriber['actual_balance']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php echo pagination($records, Subscriber::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>