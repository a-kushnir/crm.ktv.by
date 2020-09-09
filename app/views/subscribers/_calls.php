<p>Звонки оператора <strong><?php echo $subscriber['allow_calls'] ? '<i class="icon icon-ok"> </i> разрешены' : '<i class="icon icon-remove"> </i> запрещены'; ?></strong></p>
<?php if (count($calls) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Телефон</th>
  <th>Оператор</th>
  <th>Результат</th>
  <th class="align-right">Дата</th>
  </thead>
<?php 
foreach($calls as $c) {
  echo '<tr>'.
  '<td nowrap>'.phone_with_icon($c['phone_number']).'</td>'.
  '<td>'.$c['user_name'].'</td>'.
  '<td>'.$c['call_result'].
  ($c['promised_on'] ? ' до '.human_date($c['promised_on']).'' : '').
  ($c['request_id'] ? ' (cоздана заявка)' : '').
  ($c['new_phone_number'] ? ', исправлено на '.phone_with_icon($c['new_phone_number']).'' : '').
  '</td>'.
  '<td class="align-right">'.human_datetime($c['created_at']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>