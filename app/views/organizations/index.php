<?php echo search_button(); ?>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($organizations) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Название</th>
  <th>Ключевые слова</th>
  </thead>
  <tbody>
<?php 
foreach($organizations as $organization) {
  echo '<tr>'.
  '<td>'.link_to(url_for('organizations', 'show', $organization['id']), search_highlight_part($organization['name'])).'</td>'.
  '<td>'.search_highlight_part($organization['keywords']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php echo pagination($records, Organization::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>

<?php } ?>

<?php if (has_access('organization/destroy') && $layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_new('organizations'); ?>
</div>
<?php } ?>