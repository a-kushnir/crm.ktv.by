<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
  <?php echo print_version_button('&station='.$station_id); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
if ($layout != 'print') {
  $javascripts[] = '/javascripts/channels.js';
  
  $html = '<div class="well form-inline">';
  
  $html.= '<select id="station" class="input-xlarge">';
  $total = 0;
  foreach($stations as $station) { $total += $station['channel_count']; }
  $html.= '<option value="">&lt;Выберите станцию&gt;</option>';
  $html.= '<option value="all" '.('all' == $station_id ? 'selected' : '').'>Все станции ('.$total.')</option>';
  foreach($stations as $station) {
    $html.= '<option '.($station['id'] == $station_id ? 'selected' : '').' value="'.$station['id'].'">'.$station['name'].' ('.$station['channel_count'].')</option>';
  }    
  $html.= '</select>';
  
  $html.= '</div>';
  echo $html;
}
?>

<?php if (count($channels) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
 
<div class="text">
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <?php if ($layout != 'print') echo '<th style="padding-left:17px;">&nbsp;</th>'; ?>
  <th>Название</th>
  <th>Тип</th>
  <th>Канал</th>
  <th>Частота</th>
  <th>Джамперы</th>
  <th>Спутник</th>
  <th>Транспордер</th>
  <th>Тюнер</th>
  <th>Канал Тюнера</th>
  <th>Ключ доступа</th>
  <th>Примечание</th>
  </thead>
<?php 
$station_id = null;

foreach($channels as $channel) {
  if ($station_id != $channel['station_id']) {
    if ($station_id) echo '</tbody>';
    echo '<thead>'.
    '<th colspan="10">'.$channel['station'].'</th>'.
    '</thead><tbody>';
    $station_id = $channel['station_id'];
  }
  
  $status_image = '';
  $status_hint = '';
  if ($layout != 'print') {
    if ($channel->curr_broken()) {
      $status_image = 'tv_red.png';
      $status_hint = 'Вещание канала прервано';
    } else if ($channel->curr_connection_lost()) {
      $status_image = 'tv_grey.png';
      $status_hint = 'Соединение со станцией потеряно';
    } else {
      $status_image = 'tv_blue.png';
      $status_hint = 'Канал в эфире';
    }
  }

  $channel_type = '';
  if ($channel['type'] == 'analog') {
    $channel_type = 'А';
  } else if ($channel['type'] == 'digital') {
    $channel_type = 'Ц';
  }
  
  echo '<tr'.($channel['enabled'] ? '' : ' class="handled-request"').'>'.
  ($status_image ? '<td><img src="/images/'.$status_image.'" alt="'.$status_hint.'" title="'.$status_hint.'"></img></td>' : '').
  '<td nowrap>'.($layout == 'print' ? $channel['name'] : link_to(url_for('channels', 'show', $channel['id']), $channel['name'])).'</td>'.
  '<td nowrap class="align-center">'.$channel_type.'</td>'.
  '<td nowrap class="align-right">'.$channel['channel_code'].'</td>'.
  '<td nowrap class="align-right">'.($channel['frequency_id'] ? format_float($channel['frequency'], 2) : '').'</td>'.
  '<td nowrap class="align-center">'.($channel['frequency_id'] ? render_jumpers($channel['jumpers']) : '').'</td>'.
  '<td nowrap>'.$channel['satellite'].'</td>'.
  '<td nowrap>'.$channel['transponder'].'</td>'.
  '<td nowrap>'.$channel['tuner'].'</td>'.
  '<td nowrap class="align-right">'.$channel['tuner_channel'].'</td>'.
  '<td nowrap>'.$channel['access_key'].'</td>'.
  '<td nowrap>'.$channel['description'].'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
</div>

<?php } ?>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_new('channels') ?>
</div>
<?php } ?>
