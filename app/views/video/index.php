<?php echo page_header($title, $subtitle); ?>

<?php if (count($cameras) == 0) {
  echo table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Название</th>
  <th>Адрес</th>
  </thead>
  <tbody>
<?php 
foreach($cameras as $camera)
{
  echo '<tr>'.
  '<td>'.link_to(url_for('video', 'show', $camera['id']), $camera['name'], array('class' => ($camera['active'] ? 'record-active' : 'record-terminated'))).'</td>'.
  '<td>'.$camera['url'].'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php if (has_access('video/edit')) { ?>
<div class="form-actions">
  <?php echo link_to_new('video') ?>
</div>
<?php } ?>