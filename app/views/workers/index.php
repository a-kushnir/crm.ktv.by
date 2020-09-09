<?php echo search_button(); ?>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($workers) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>ФИО</th>
  <th></th>
  <th>Телефоны</th>
  <th>Комментарий</th>
  </thead>
  <tbody>
<?php 
foreach($workers as $worker) {
  echo '<tr>'.
  '<td>'.link_to_show('workers', $worker['id'], search_highlight_part($worker['first_name'].' '.$worker['last_name'])).'</td>'.
  '<td class="align-center" class="nobr">'.
    ($worker['show_timesheet'] ? ' <i class="icon-time" title="Видимый в табеле"> </i> ' : '').
    ($worker['show_requests'] ? ' <i class="icon-briefcase" title="Видимый в заявках"> </i> ' : '').
  (has_access('worker/user') && $worker['user_id'] && !$worker['disabled_at'] ? ' <i class="icon-user" title="Вход разрешен"> </i> ' : '').
  '</td>'.
  '<td><span class="nobr">'.search_highlight_part(phone_with_icon($worker['cell_phone1'])).'</span> <span class="nobr">'.search_highlight_part(phone_with_icon($worker['cell_phone2'])).'</span></td>'.
  '<td>'.nl2br(search_highlight_part($worker['comment'])).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>

<?php } ?>

<?php echo pagination($records, Worker::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>

<?php if (has_access('worker/new')) { ?>
<div class="form-actions">
  <?php echo link_to_new('workers') ?>
</div>
<?php } ?>