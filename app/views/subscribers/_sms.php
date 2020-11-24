<?php if (count($messages) == 0) {
  echo no_data_tag();
} else {
 ?>
<table class="table table-bordered table-striped table-condensed">
  <thead>
  <th>Телефон</th>
  <th>Сообщение</th>
  <th>Дата</th>
  </thead>
<?php 
foreach($messages as $message) {
  echo '<tr>'.
  '<td nowrap>'.phone_with_icon($message['phone_number']).'</td>'.
  '<td>'.$message['message'].'</td>'.
  '<td class="align-right">'.human_datetime($message['sent_at']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>