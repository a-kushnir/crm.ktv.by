<div class="form-horizontal">
<?php 
  $subscriber_note_types = SubscriberNote::get_types('inspection', $house['id']);
  echo render_house_legend();
  echo render_house($house, 'scheme', $subscriber_note_types);
?>

<div>
  <table class="house-legend">
  <tr>
    <td class="legend-label"><b>Отмечать:</b></td>
<?php
  foreach ($subscriber_note_types as $subscriber_note_type)
    echo '<td class="align-right"><strong>'.$subscriber_note_type['code'].'</strong></td><td class="legend-label">- '.$subscriber_note_type['name'].'</td>';
?></tr>
  </table>
</div>

<?php
if (count($amplifiers) > 0) {
?>
<div class="avoid-page-break">
<h4>Усилители</h4>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="align-middle align-center min-width">№</th>
  <th class="align-middle">Название</th>
  <th class="align-middle">Расположение</th>
  <th class="align-middle">Комментарий</th>
  <th class="align-middle align-right">Обновлено</th>
  <th class="align-middle">Загрузить скан</th>
  </thead>
  <tbody>
<?php 
$index = 0;
$amp_scans = array();
$station_id = null;
foreach($amplifiers as $amplifier)
{
  if (!$station_id) $station_id = Amplifier::station_id($amplifier['id']);
  $amp_scans[] = $amp_scan = Amplifier::last_scan($amplifier['id']);
  $index++;
  
  echo '<tr>'.
    '<td class="align-middle align-center"><strong>'.$index.'</strong></td>'.
    '<td class="align-middle">'.link_to(url_for('amplifiers', 'show', $amplifier['id']), $amplifier['name']).'</td>'.
    '<td class="align-middle">'.$amplifier->location().'</td>'.
    '<td class="align-middle">'.nl2br($amplifier['description']).'</td>'.
    '<td class="align-middle align-right">'.($amp_scan ? human_date($amp_scan['created_at']) : null).'</td>'.
    '<td class="align-middle align-right"><input name="scan_file['.$amplifier['id'].']" type="file"></td>'.
    '</tr>';
}
?>
</tbody>
</table>
</div>
<?php } ?>

<br>
<?php 
  echo date_field('inspection', "actual_date", "Дата", array('required' => true));
  
  $workers = Request::workers();
  echo $read_only ? 
    readonly_field("Ответственный", $inspection['worker']):
    select_field('inspection', "handled_by", "Ответственный", get_field_collection($workers, 'id', 'name', ''), array('required' => true));
    
  echo text_area_field('inspection', "comment", "Дополнительно", null, array('class' => 'input-xxlarge','rows' => '4'));
?>