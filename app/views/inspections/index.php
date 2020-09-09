<?php echo search_button('', isset($_SESSION['selected_region']) ?  ' по '.$_SESSION['selected_region']['name'] : ''); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone" style="margin-right:10px;">
<a class="btn" style="margin-left:10px;" href="/files/dolznik.pdf"><i class="icon-download-alt"> </i> Записка должнику</a>
<a class="btn" style="margin-left:10px;" href="/files/nelegal.pdf"><i class="icon-download-alt"> </i> Записка нелегалу</a>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($inspections) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Адрес</th>
  <th>Ответственный</th>
  <th class="align-right">Дата</th>
  </thead>
  <tbody>
<?php 
foreach($inspections as $inspection)
{
  //$count_subscribers = $house->count_subscribers();
  echo '<tr>'.
  '<td>'.link_to(url_for('inspections', 'show', $inspection['id']), search_highlight_part(format_address($inspection))).'</td>'.
  '<td>'.$inspection['worker'].'</td>'.
  '<td class="align-right">'.human_date($inspection['actual_date']).'</td>'.
  '</tr>';
}
?>

</tbody>
</table>

<?php } ?>

<?php echo pagination($records, Inspection::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>