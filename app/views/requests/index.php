<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button('&house='.$house_id.'&type='.$request_type_id.'&include='.$include_closed.'&selection='.$selection); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
if ($layout != 'print') {
  $total = 0; foreach($houses as $house) { $total += $house['request_count']; }
  $online = 0; foreach($houses as $house) { if ($house['is_online']) $online += $house['request_count']; }
  $offline = 0; foreach($houses as $house) { if (!$house['is_online']) $offline += $house['request_count']; }

  $javascripts[] = '/javascripts/requests.js';
  
  $html = '<div class="well form-inline">';

  $html.= '<select id="house" class="input-xlarge">';
  $html.= '<option value="">&lt;Выберите адрес&gt;</option>';
  $html.= '<option value="selection" '.('selection' == $house_id ? 'selected' : '').' style="'.('selection' == $house_id ? '' : 'display:none;').'">Выбранные дома ('.count($requests).')</option>';
  $html.= '<option value="all" '.('all' == $house_id ? 'selected' : '').'>'.(isset($_SESSION['selected_region']) ? $_SESSION['selected_region']['name'] : 'Все адреса').' ('.$total.')</option>';
  if ($offline != 0 || 'online' == $house_id || 'offline' == $house_id) {
  $html.= '<option value="online" '.('online' == $house_id ? 'selected' : '').'>Дома онлайн ('.$online.')</option>';
  $html.= '<option value="offline" '.('offline' == $house_id ? 'selected' : '').'>Дома офлайн ('.$offline.')</option>';
  }
  foreach($houses as $house)
    $html.= '<option '.($house['id'] == $house_id ? 'selected' : '').' value="'.$house['id'].'">'.format_address($house).' ('.$house['request_count'].')</option>';
  $html.= '</select>';

  $request_types = RequestType::load();
  $html.= '<select id="type" class="input-medium" style="margin-left:10px;">';
  $html.= '<option value="" '.('' == $request_type_id ? 'selected' : '').'>Все заявки</option>';
  $html.= '<option disabled="disabled" value="-">———————</option>';
  $html.= '<option value="technician" '.('technician' == $request_type_id ? 'selected' : '').'>Монтажник</option>';
  $html.= '<option value="dispatcher" '.('dispatcher' == $request_type_id ? 'selected' : '').'>Диспетчер</option>';
  $html.= '<option disabled="disabled" value="-">———————</option>';
  foreach($request_types as $request_type)
    $html.= '<option '.($request_type['id'] == $request_type_id ? 'selected' : '').' value="'.$request_type['id'].'">'.$request_type['name'].'</option>';
  $html.= '</select>';
  
  $html.= '<label class="checkbox" style="margin-left:10px;">';
  $html.= '<input type="checkbox" id="include" value="closed" '.($include_closed ? 'checked' : '').'> Включая выполненные';
  $html.= '</label>';
  
  $html.= '</div>';
  echo $html;
}

if ($selection) echo "<input type='hidden' id='selection' value='".$selection."' />";
?>

<?php if (count($requests) == 0) {
  echo  table_no_data_tag();
} else {
 ?>
 
<div class="text">
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="min-width align-right">Кв.</th>
  <th class="min-width align-right">П.</th>
  <th class="min-width align-right">Э.</th>
  <th>Тип</th>
  <th>Приор.</th>
  <th class="align-center">Дог.</th>
  <th class="align-right">Принята</th>
  <?php if($include_closed){ ?>
  <th class="align-right">Закрыта</th>
  <th>Исполнитель</th>
  <?php } ?>
  <th>Телефоны</th>
  <th>Примечание</th>
  </thead>
<?php 
$curr_house_id = null;
$house_schema = null;

$now = time();
$wait_normal = $now - Request::$wait_normal * 86400;
$wait_long = $now - Request::$wait_long * 86400;

foreach($requests as $request) {
  if ($curr_house_id != $request['house_id']) {
    $rcount = 0;
    foreach($requests as $r)
      if ($request['house_id'] == $r['house_id'])
        $rcount++;
        
    $rcount = $layout == 'print' ? $rcount.' <span style="font-weight:normal;">'.rus_word($rcount,'заявка', 'заявки', 'заявок').' для </span>' :
      '<span class="badge">'.$rcount.' '.rus_word($rcount,'заявка', 'заявки', 'заявок').'</span>';
  
    if ($curr_house_id) echo '</tbody>';
    echo '<thead>'.
    '<th colspan="'.($include_closed ? 11 : 9).'">'.
    ($layout != 'print' && 'selection' != $house_id && !is_numeric($house_id) ? "<input type='checkbox' class='select_house' value='".$request['house_id']."' />&nbsp;" : '').
    $rcount.' '.
    format_address(array(
      'city' => $request['city'],
      'street' => $request['street'],
      'house' => $request['house'],
      'building' => $request['building'],
      ), $layout == 'print').'</th>'.
    '</thead><tbody>';

    $house_schema = House::generate_schema($request);
    $curr_house_id = $request['house_id'];
  }
  
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
  '<td nowrap class="align-right">'.(has_access('request/show') ? link_to('/requests/'.$request['id'].'?index='.base64_encode($_SERVER['REQUEST_URI']), $request['apartment']) : $request['apartment']).'</td>'.
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

<?php if ($layout != 'print' && has_access('request/new')) { ?>
<div class="form-actions visible-desktop">
  <?php if(has_access('request/new')) echo link_to('requests/new?index='.base64_encode($_SERVER['REQUEST_URI']), 'Добавить', array('class' => 'btn btn-large btn-success')) ?>
</div>
<?php } ?>
