<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']), 
  'Усилители' => null,
)); ?>


<?php echo page_header($title, $subtitle); ?>

<form method="post" action="/amplifiers/<?php echo $house['id']; ?>/upload_scans" enctype="multipart/form-data">

<?php if (count($amplifiers) == 0) {
  echo table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="align-middle">Название</th>
  <th class="align-middle">Расположение</th>
  <th class="align-middle">Комментарий</th>
  <th class="align-middle align-right min-width">Обновлено</th>
  <th class="align-middle">Загрузить скан</th>
  </thead>
  <tbody>
<?php 
foreach($amplifiers as $amplifier)
{
$amp_scan = Amplifier::last_scan($amplifier['id']);
echo '<tr>'.
'<td class="align-middle">'.link_to(url_for('amplifiers', 'show', $amplifier['id']), $amplifier['name']).'</td>'.
'<td class="align-middle">'.$amplifier->location().'</td>'.
'<td class="align-middle">'.nl2br($amplifier['description']).'</td>'.
'<td class="align-middle align-right">'.($amp_scan ? human_date($amp_scan['created_at']) : null).'</td>'.
'<td class="align-middle"><input name="scan_file['.$amplifier['id'].']" type="file" /></td>
</td>'.
'</tr>';
}
?>
</tbody>
</table>
<?php } ?>

<?php if (has_access('house/edit_amps', 'house/upload_amp_scans')) { ?>
<div class="form-actions">
  <?php if (has_access('house/edit_amps')) echo link_to('/amplifiers/'.$id.'/new', 'Добавить', array('class' => 'btn btn-large btn-success')) ?>
  <?php if (has_access('house/upload_amp_scans') && count($amplifiers) > 0) { ?><button class="btn btn-large btn-primary">Загрузка</button><?php } ?>
</div>
<?php } ?>
</form>