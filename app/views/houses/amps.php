<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']),
  'Усилители' => null
)); ?>


<?php echo '<div style="float:right;">'.print_version_button('&amp='.$amplifier_id).'</div>'; ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
if ($layout != 'print') {
  $html = '<script type="text/javascript" src="/javascripts/amplifiers.js"></script>';
  $html.= '<div class="well form-inline">';
  
  $html.= '<select id="amp" class="input-xlarge">';
  $total = 0;
  $html.= '<option value="">&lt;Выберите усилитель&gt;</option>';
  foreach($amplifiers as $amp) {
    $html.= '<option '.($amp['id'] == $amplifier_id ? 'selected' : '').' value="'.$amp['id'].'">'.$amp['name'].'</option>';
  }    
  $html.= '</select>';
  
  $html.= '</div>';
  echo $html;
}
?>

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
  '<td class="align-right">'.$amp_channel['input'].'</td>'.
  '<td class="align-right">'.$amp_channel['output'].'</td>'.
  '<td class="align-right">'.$amp_channel['tap'].'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
</div>

<?php } ?>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <a href="/houses/<?php echo $id; ?>/new_amp" class="btn btn-large btn-success">Добавить</a>
<?php if ($amplifier_id) { ?>
  <a href="/houses/<?php echo $amplifier_id; ?>/edit_amp" class="btn btn-large btn-primary">Редактировать</a>
  <!--a href="/houses/<?php echo $amplifier_id; ?>/destroy_amp" rel="nofollow" data-method="delete" class="btn btn-large btn-danger" data-confirm="Вы уверены?">Удалить</a-->
<?php } ?>
</div>
<?php } ?>
