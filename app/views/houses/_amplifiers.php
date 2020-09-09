<?php
if (count($amplifiers) > 0) {
?>
<div class="avoid-page-break">
<p><?php echo '<strong>'.count($amplifiers).'</strong> '.rus_word(count($amplifiers), 'усилитель', 'усилителя', 'усилителей').' для <strong>'.format_address($house).'</strong>' ?></p>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="align-middle align-center min-width">№</th>
  <th class="align-middle">Название</th>
  <th class="align-middle">Расположение</th>
  <th class="align-middle">Комментарий</th>
  <th class="align-middle align-right min-width">Обновлено</th>
  <th class="align-middle align-center" style="width:20%">№ скана</th>
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
    '<td>&nbsp;</td>'.
    '</tr>';
}
?>
</tbody>
</table>

<?php include '_amplifier_scans.php'; ?>
</div>
<?php } ?>