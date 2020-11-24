<div class="form-horizontal">
  <?php echo text_field($amplifier, "name", "Название", array('required' => true)); ?>
  <?php echo text_area_field($amplifier, "description", "Описание", null, array('class' => 'input-xlarge','rows' => '4')); ?>
</div>
<?php if (count($amp_channels) == 0) {
  echo no_data_tag();
} else {
 ?>
 
<div class="text">
<table class="table table-bordered table-striped table-condensed">
  <thead>
  <th>Канал</th>
  <th>Частота</th>
  <th>Название</th>
  <th>Вход</th>
  <th>Выход</th>
  <th>Отвод</th>
  </thead>
<?php 
foreach($amp_channels as $amp_channel) {
  echo '<tr'.($amp_channel['enabled'] ? ' class="handled-request"' : '').'>'.
  '<td class="align-right">'.$amp_channel['channel_name'].'</td>'.
  '<td class="align-right">'.format_float($amp_channel['frequency'], 2).'</td>'.
  '<td>'.$amp_channel['name'].'</td>'.
  '<td class="align-center"><input name="input['.$amp_channel['channel_id'].']" class="input-small" value="'.htmlspecialchars($amp_channel['input']).'"></input></td>'.
  '<td class="align-center"><input name="output['.$amp_channel['channel_id'].']" class="input-small" value="'.htmlspecialchars($amp_channel['output']).'"></input></td>'.
  '<td class="align-center"><input name="tap['.$amp_channel['channel_id'].']" class="input-small" value="'.htmlspecialchars($amp_channel['tap']).'"></input></td>'.
  '</tr>';
}
?>
</tbody>
</table>
</div>

<?php } ?>