<?php # input: array $amp_scans, int $station_id

function amp_scan_position($amp_scans, $amplifier_scan_id) {
  if(!$amplifier_scan_id) return -1;
  $index = 0;
  foreach($amp_scans as $amp_scan)
    if ($amp_scan) {
      if ($amp_scan['id'] == $amplifier_scan_id)
        return $index;
      $index++;
    }
  return null;
}
function render_empty_level_cells($count) {
  for($i = 0; $i < $count; $i++)
    echo '<td class="align-center">-</td>';
}
function render_amp_scan_header($amp_scans, $amp_scan_ids) {?>
<td class="align-top my-column-row">
<table class="table table-bordered table-striped table-condensed table-hover">
<thead>
<tr>
<th class="align-middle align-center" rowspan="2">Код</th>
<th class="align-middle align-center" rowspan="2">Частота</th>
<th class="align-middle align-center" rowspan="2">Тип</th>
<th class="align-middle align-center" colspan="<?php echo count($amp_scan_ids); ?>">Уровень</th>
<th class="align-middle" rowspan="2">Название</th>
</tr>
<tr>
<?php
  $index = 1;
  foreach ($amp_scans as $amp_scan) {
    if ($amp_scan)
      echo '<th class="align-center">'.$index.'</th>';
    $index++;
  }
?>
</tr>
</thead>
<tbody>
<?php }
function render_amp_scan_footer() {?>
</tbody>
</table>
</td>
<?php }

$amp_scan_ids = array();
foreach ($amp_scans as $amp_scan)
  if ($amp_scan)
    $amp_scan_ids[] = $amp_scan['id'];
      
if (count($amp_scan_ids) > 0) {
  $amp_scan_details = Amplifier::scan_details($amp_scan_ids, $station_id);

$frequency_id = null;
$frequencies_count = 0;
foreach($amp_scan_details as $amp_scan_detail) {
  if ($frequency_id != $amp_scan_detail['frequency_id']) {
    $frequency_id = $amp_scan_detail['frequency_id'];
    $frequencies_count++;
  }
}
$frequencies_per_table = ceil($frequencies_count / 2);

?> 
<p>Замеры с усилителей</p>
<table class="my-columns-2"><tbody><tr>
<?php
render_amp_scan_header($amp_scans, $amp_scan_ids);

$frequency_id = null;
$amplifier_scan_id = null;
$frequencies_count = 0;
foreach($amp_scan_details as $amp_scan_detail) {
  if ($frequency_id != $amp_scan_detail['frequency_id']) {
    if ($frequency_id) {
      echo render_empty_level_cells(count($amp_scan_ids) - amp_scan_position($amp_scans, $amplifier_scan_id) - 1);
      echo '<td>'.$amp_scan_detail['channel_name'].'</td></tr>';
    }
    $amplifier_scan_id = null;
    
    if ($frequencies_count == $frequencies_per_table) {
      $frequencies_count = 0;
      render_amp_scan_footer();
      render_amp_scan_header($amp_scans, $amp_scan_ids);
    }
    $frequencies_count++;
    
    echo '<tr>'.
      '<td class="align-center">'.$amp_scan_detail['name'].'</td>'.
      '<td class="align-center">'.format_float($amp_scan_detail['frequency'], 2).'</td>'.
      '<td class="align-center">'.($amp_scan_detail['type'] == 'analog' ? 'A' : 'D').'</td>';
  }
  
  echo render_empty_level_cells(amp_scan_position($amp_scans, $amp_scan_detail['amplifier_scan_id']) - amp_scan_position($amp_scans, $amplifier_scan_id) - 1);
  echo '<td class="align-center">'.$amp_scan_detail['level'].'</td>';

  $frequency_id = $amp_scan_detail['frequency_id'];
  $amplifier_scan_id = $amp_scan_detail['amplifier_scan_id'];
}

if ($frequency_id) {
  echo render_empty_level_cells(count($amp_scan_ids) - amp_scan_position($amp_scans, $amplifier_scan_id) - 1);
  echo '<td>'.$amp_scan_detail['channel_name'].'</td></tr>';
}
}
render_amp_scan_footer();

?> </tr></tbody></table> <?php
?>