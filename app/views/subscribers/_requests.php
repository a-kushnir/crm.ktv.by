<?php
  $has_open = false;
  foreach($requests as $r)
    if (!$r['handled_on'])
      $has_open = true;
?>
<p>Добавление заявок <?php echo !$has_open ? '<strong><i class="icon icon-ok"> </i> разрешено</strong>' : '<strong><i class="icon icon-remove"> </i> запрещено</strong>, так как у абонента есть открытые заявки'; ?></p>

<?php if (count($requests) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
<table class='table table-bordered table-striped table-condensed'>
  <thead>
  <th>Принята</th>
  <th>Тип</th>
  <th>Подробности</th>
  <th>Закрыта</th>
  <th>Исполнитель</th>
  <th>Отчет</th>
  </thead>
<?php 
foreach($requests as $r) {
  echo '<tr>'.
  '<td nowrap>'.link_to(url_for('requests', $r['id']), human_date($r['created_at'])).'</td>'.
  '<td nowrap>'.$r['request_type'].'</td>'.
  '<td>'.$r['comment'].'</td>'.
  '<td nowrap>'.human_date($r['handled_on']).'</td>'.
  '<td nowrap>'.$r['worker'].'</td>'.
  '<td>'.$r['handled_comment'].'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>

<?php if(!$has_open && (!isset($suppress_new_request) || !$suppress_new_request)) { ?>

<div id='new_request_button' class='form-actions'  <?php if (count($request->errors) > 0) echo "style='display:none;'" ?>>
<a href='#requests' class='btn btn-large btn-success' onclick='javascript:$("#new_request_button").hide();$("#new_request_form").show();'>Добавить</a>
</div>

<div id='new_request_form' <?php if (count($request->errors) == 0) echo "style='display:none;'" ?>>
<?php 
  $form = new FormBuilder('request');
  echo $form->begin_form('/subscribers/'.$id.'/requests/new#requests', array('class' => 'form-horizontal'));
?>
<fieldset>
<legend>Новая заявка</legend>
<?php
  echo $form->select('request_type_id', 'Тип', get_field_collection(RequestType::load(), 'id', 'name', ''), array('required' => true));
  echo $form->select('request_priority_id', 'Приоритет', get_field_collection(Request::priorities(), 'id', 'name'), array('required' => true));
  echo $form->text_area('comment', 'Примечание', null, array('class' => 'input-xxlarge'));
?>
<div class='form-actions'>
  <a href='#requests' class='btn btn-large' onclick='javascript:$("#new_request_form").hide();$("#new_request_button").show();'>Отмена</a>
  <?php echo submit_button('Создать'); ?>
</div>
</fieldset>
<?php echo $form->end_form(); ?>
</div>

<?php } ?>