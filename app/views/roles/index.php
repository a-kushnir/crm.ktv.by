<?php echo page_header($title, $subtitle); ?>

<?php if (count($roles) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Название</th>
  <th>Описание</th>
  <th>Пользователи</th>
  <th class="min-width">&nbsp;</th>
  </thead>
  <tbody>
<?php 
foreach($roles as $role)
{
  echo '<tr>'.
  '<td>'.link_to(url_for('roles', 'show', $role['id']), $role['name']).'</td>'.
  '<td>'.nl2br($role['description']).'</td>'.
  '<td>';
  
  $users = $role->users();
  echo implode($users['names'], ', ');
  if ($users['count'] > count($users['names'])) echo ', <small>и еще '.($users['count'] - count($users['names'])).'</small>';
  
  echo '</td>'.
  '<td>'.link_to(url_for('roles', 'rights', $role['id']), 'Права').'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php if (has_access('role/edit')) { ?>
<div class="form-actions">
  <?php echo link_to_new('roles') ?>
</div>
<?php } ?>