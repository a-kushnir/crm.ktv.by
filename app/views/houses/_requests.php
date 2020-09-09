<?php if (count($requests) > 0) {
$include_closed = false;
?>
<p><?php echo '<strong>'.count($requests).'</strong> '.rus_word(count($requests), 'заявка', 'заявки', 'заявок') ?></p>
<div class="text">
<table class="table table-bordered table-striped table-condensed table-hover">
<thead>
  <th class="min-width align-right">Кв.</th>
  <th class="min-width align-right">П.</th>
  <th class="min-width align-right">Э.</th>
  <th class="min-width">Тип</th>
  <th class="min-width">Приор.</th>
  <th class="align-center min-width">Дог.</th>
  <th class="align-right min-width">Принята</th>
  <?php if($include_closed){ ?>
  <th class="align-right">Закрыта</th>
  <th>Исполнитель</th>
  <?php } ?>
  <th>Телефоны</th>
  <th>Примечание</th>
</thead>
<tbody>
<?php 
$curr_house_id = null;
$house_schema = null;
$entrance = null;
$floor = null;

$now = time();
$wait_normal = $now - Request::$wait_normal * 86400;
$wait_long = $now - Request::$wait_long * 86400;

foreach($requests as $request) {
  if(!$house_schema) $house_schema = House::generate_schema($request);
  
  $apartment = $request['apartment'];
  $si = House::search_arartment($house_schema, $apartment);
  
  $doc_icon = null;
  if ($layout == 'print')
    $doc_icon = $request['subscriber_id'] ? ' есть' : ' <b>нет</b>';
  else
    $doc_icon = $request['subscriber_id'] ? ' <i class="icon-ok"></i>' : ' <i class="icon-remove"></i>';
 
  $css_class = '';
  $created_at = parse_db_datetime($request['created_at']);
  if ($created_at > $wait_normal) {
    $css_class = 'wait_short';
  } else if ($created_at > $wait_long) {
    $css_class = 'wait_normal';
  } else {
    $css_class = 'wait_long';
  }
 
  echo '<tr'.($request['handled_on'] ? ' class="handled-request"' : '').'>'.
  '<td nowrap class="align-right">'.link_to('/requests/'.$request['id'].'?index='.base64_encode($_SERVER['REQUEST_URI']), $request['apartment']).'</td>'.
  '<td nowrap class="align-right">'.$si['entrance'].'</td>'.
  '<td nowrap class="align-right">'.$si['floor'].'</td>'.
  '<td nowrap>'.$request['request_type'].'</td>'.
  '<td nowrap class="priority-'.$request['request_priority_id'].'">'.$request['request_priority'].'</td>'.
  '<td nowrap class="align-center">'.$doc_icon.'</td>'.
  '<td nowrap class="align-right"><span class="'.$css_class.'">'.($layout != 'print' ? human_date($request['created_at']) : format_date($request['created_at'])).'</span></td>';
  
  if($include_closed){
    echo '<td nowrap class="align-right">'.human_date($request['handled_on']).'</td>'.
  '<td nowrap>'.$request['worker'].'</td>';
  }
  
  echo '<td>'.phone_with_icon($request['cell_phone']).' '.phone_with_icon($request['home_phone']).'</td>'.
  '<td>'.nl2br($request['comment']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
</div>

<?php } ?>