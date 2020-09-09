<?php $available = $subscriber['cell_phone']; ?>
<p>Отправка сообщений <strong><?php echo $subscriber['allow_sms'] ? '<i class="icon icon-ok"> </i> разрешена' : '<i class="icon icon-remove"> </i> запрещена'; ?></strong><?php if (!$available) echo ', но номер мобильного телефона не введен'; ?></p>
<?php if (count($messages) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Телефон</th>
  <th>Сообщение</th>
  <th>Дата</th>
  </thead>
<?php 
foreach($messages as $m) {
  echo '<tr>'.
  '<td nowrap>'.phone_with_icon($m['phone_number']).'</td>'.
  '<td>'.$m['message'].'</td>'.
  '<td class="align-right">'.human_datetime($m['sent_at']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>

<?php if (isset($message) && has_access('subscriber/new_message') && $layout != 'print' && $available) { ?>
<div id='new_message_button' class='form-actions'  <?php if (count($message->errors) > 0) echo "style='display:none;'" ?>>
<a href='#messages' class='btn btn-large btn-success' onclick='javascript:$("#new_message_button").hide();$("#new_message_form").show();'>Добавить</a>
</div>

<div id='new_message_form' <?php if (count($message->errors) == 0) echo "style='display:none;'" ?>>
<?php 
  $form = new FormBuilder('message');
  echo $form->begin_form('/subscribers/'.$id.'/messages/new#messages', array('class' => 'form-horizontal'));
?>
<fieldset>
<legend>Отправка сообщения</legend>
<?php
  echo $form->read_only(null, 'Номер', null, array('value' => phone_with_icon($subscriber['cell_phone'])));
  echo $form->text_area('text', 'Текст', array('required' => true), array('rows' => '4'));
?>
<div class='form-actions'>
  <a href='#messages' class='btn btn-large' onclick='javascript:$("#new_message_form").hide();$("#new_message_button").show();'>Отмена</a>
  <?php echo submit_button('Отправить', array('data-confirm' => 'Вы уверены что хотите отправить сообщение?
Это действие не может быть отменено!')); ?>
</div>
</fieldset>
<?php echo $form->end_form(); ?>
</div>

<?php } ?>