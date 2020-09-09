<?php echo search_button('', isset($_SESSION['selected_region']) ?  ' по '.$_SESSION['selected_region']['name'] : ''); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone" style="margin-right:10px;">
<a class="btn" style="margin-left:10px;" href="/files/dolznik.pdf"><i class="icon-download-alt"> </i> Записка должнику</a>
<a class="btn" style="margin-left:10px;" href="/files/nelegal.pdf"><i class="icon-download-alt"> </i> Записка нелегалу</a>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php if (count($houses) == 0) {
  echo  table_no_data_tag();
} else { ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
<?php if (has_access('house/detailed_index')) { ?>
  <th style="padding-left:17px;width:1px;">&nbsp;</th>
  <th style="width:30%;">Адрес</th>
  <th style="width:30%;">Владелец</th>
  <th class="align-center min-width">Схема</th>
  <th class="align-center" colspan="2">Абоненты</th>
  <th class="align-right" style="width:10%;">Обход</th>
  <th style="padding-left:57px;width:1px;">&nbsp;</th>
<?php } else { ?>
  <th>Адрес</th>
  <th>Владелец</th>
  <th>Комментарий</th>
<?php } ?>
  </thead>
  <tbody>
<?php 
if (has_access('house/detailed_index')) {
foreach($houses as $house)
{
  $count_subscribers = $house->count_subscribers();
  echo '<tr>'.
  '<td class="align-center">'.
    ($house['is_online'] ? ' <img src="/images/tv_blue.png" alt="Дом подключен к сети" title="Дом подключен к сети"> ' : ' <img src="/images/tv_grey.png" alt="Дом не подключен к сети" title="Дом не подключен к сети"> ').
  '</td>'.
  '<td>'.link_to(url_for('houses', 'show', $house['id']), search_highlight_part(format_address($house))).'</td>'.
  '<td>'.'<a class="tooltip-balloon" data-content="'.nl2br(search_highlight_part($house['owner_description'])).'" rel="popover" href="#" data-original-title="'.search_highlight_part($house['owner_name']).'">'.search_highlight_part($house['owner_name']).'</a>'.'</td>'.
  '<td class="align-center nobr"><span title="'.$house['entrances'].' подъездный '.$house['floors'].' этажный дом">'.$house['entrances'].' х '.$house['floors'].'</span></td>'.
  '<td class="align-center min-width nobr">'.$count_subscribers.' / '.$house['apartments'].'</td>'.
  '<td class="align-center">'.render_progress_bar($count_subscribers/$house['apartments'], array('style'=>'margin-bottom:0;')).'</td>'.
  '<td class="align-right min-width nobr">'.human_date($house['inspected_on']).'</td>'.
  '<td class="align-left">
  <a href="/houses/'.$house['id'].'/amplifiers"><img src="/images/action/device.png" alt="Усилители" title="Усилители"></a>
  <a href="/houses/'.$house['id'].'/passport.print"><img src="/images/action/passport.png" alt="Обходной документ" title="Обходной документ"></a>'.
  ($house['is_online'] ?
  "\n".'<a href="/inspections/'.$house['id'].'/new?index='.base64_encode($_SERVER['REQUEST_URI']).'"><img src="/images/action/add_report.png" alt="Добавить обходной отчет" title="Добавить обходной отчет"></a>' : null).
  '</td>'.
  '</tr>';
}
} else {
foreach($houses as $house)
{
echo '<tr>'.
'<td>'.search_highlight_part(format_address($house)).'</td>'.
'<td>'.search_highlight_part($house['owner_name']).'</td>'.
'<td>'.nl2br(search_highlight_part($house['owner_description'])).'</td>'.
'</tr>';
}
}
?>

</tbody>
</table>

<?php } ?>

<?php echo pagination($records, House::$page_size, get_field_value('page'), '&filter='.get_field_value('filter')); ?>

<?php if (has_access('house/new')) { ?>
<div class="form-actions">
  <?php echo link_to_new('houses') ?>
</div>
<?php } ?>