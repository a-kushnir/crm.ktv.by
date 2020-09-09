<?php
function render_jumpers($value) {
  if ($value === null) {
    return '';
  } else {
    return render_jumper($value & 1).
           render_jumper($value & 2).
           render_jumper($value & 4).
           render_jumper($value & 8).
           render_jumper($value & 16).
           render_jumper($value & 32).
           render_jumper($value & 64).
           render_jumper($value & 128);
  }
}

function render_jumper($state)
{
  return $state ? '1' : '0';
}

function base_address_field($object_name, $label, $options, $street_html) {
  if (get_object_value($options, 'read_only')){
    return read_only_field($object_name, 'address', $label, null, array('value' => format_address($object_name)));
  } else {
    $error_tag = field_error_tag($object_name, 'address');
    $required = get_object_value($options, 'required');
    $include_blank = is_null(get_object_value($object_name, 'id', null)) ? '' : null;
  
    $selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null;
    if (!$selected_region) $cities = Address::get_cities();
    $city_id = get_object_value($object_name, 'city_id');
    if ($city_id == null && $selected_region) $city_id = $selected_region['city_id'];

    $street_id = get_object_value($object_name, 'street_id');
    
    $html = "<div id='".get_field_id($object_name, 'address')."' class='control-group address".($error_tag != null ? ' error' : null)."'>";
    $html.= "<label for='".get_field_id($object_name, 'city_id')."' class='control-label'>".($required ? field_required_tag() : '').$label."</label>";
    $html.= "<div class='controls'>";

  if ($selected_region)
    $html.= '<span>'.Address::city_name($city_id).'</span>&nbsp;&nbsp;'.
            hidden_field_tag(get_field_name($object_name, 'city_id'), $city_id);
  else
    $html.= select_tag(get_field_name($object_name, 'city_id'), $city_id, get_field_collection($cities, 'id', 'name', $include_blank), array('class' => 'address_city span2'))."&nbsp;";

    $html.= "<span class='address_street_container'>";
    if ($city_id != null) {
      $streets = Address::get_streets($city_id, true);
      $html.= select_tag(get_field_name($object_name, 'street_id'), $street_id, get_field_collection($streets, 'id', 'name', $include_blank), array('class' => 'address_street'));
    }
    $html.= "</span>";

    $html.= "<span class='address_street_selected nobr' ".($street_id == null ? 'style="display:none;"' : '').">";
    $html.= $street_html;
    $html.= "</span><span class='loading address_loading'>Загрузка...</span>".$error_tag;
    $html.= "</div></div>";
    
    return $html;
  }
}

function building_address_field($object_name, $label, $options = null) {
  // street_container html
  $city_id = get_object_value($object_name, 'city_id');
  if ($city_id == null && isset($_SESSION['selected_region'])) $city_id = $_SESSION['selected_region']['city_id'];
  
  $city_district_id = get_object_value($object_name, 'city_district_id');
  $city_microdistrict_id = get_object_value($object_name, 'city_microdistrict_id');
  
  $districts = $city_id ? Address::get_districts($city_id) : array();
  $microdistricts = $city_district_id ? Address::get_microdistricts($city_district_id) : array();
  
  $html = "<span class='help-inline'>дом&nbsp;&nbsp;</span>";
  $html.= text_field_tag(get_field_name($object_name, 'house'), get_object_value($object_name, 'house'), array('class' => 'input-mini'));
  $html.= "<span class='help-inline'>корпус&nbsp;&nbsp;</span>";
  $html.= text_field_tag(get_field_name($object_name, 'building'), get_object_value($object_name, 'building'), array('class' => 'input-mini'));
  
  $result = base_address_field($object_name, $label, $options, $html);
  $result.= (count($districts) > 0 ? select_field($object_name, "city_district_id", "Район", get_field_collection($districts, 'id', 'name', '')) : '');
  $result.= (count($microdistricts) > 0 ? select_field($object_name, "city_microdistrict_id", "Микрорайон", get_field_collection($microdistricts, 'id', 'name', '')) : '');
    
  return $result;
}

function apartment_address_field($object_name, $label, $options = null) {
  // street_container html
  $street_id = get_object_value($object_name, 'street_id');
  $house_id = get_object_value($object_name, 'house_id');
  $apartment = get_object_value($object_name, 'apartment');

  $html = "<span class='help-inline'>дом&nbsp;&nbsp;</span><span class='address_house_container'>";
  if ($street_id != null) {
    $include_blank = is_null(get_object_value($object_name, 'id', null)) ? '' : null;
    $houses = Address::get_houses($street_id);
    
    $html.= select_tag(get_field_name($object_name, 'house_id'), $house_id, get_field_collection($houses, 'id', 'name', $include_blank), array('class' => 'address_house span1'));
  }
  $html.= "</span>";
  $html.= "</span>";
  
  $html.= "<span class='address_house_selected' ".($house_id == null ? 'style="display:none;"' : '').">";
  $html.= "<span class='help-inline'>квартира&nbsp;&nbsp;</span>";
  $html.= text_field_tag(get_field_name($object_name, 'apartment'), $apartment, array('class' => 'span1'));
  $html.= "</span>";

  return base_address_field($object_name, $label, $options, $html);
}

function billing_tariff_select_tag($name, $billing_tariff_id, $billing_tariffs, $html_options = null)
{
  $collection = array();
  foreach($billing_tariffs as $billing_tariff)
    $collection[] = array(
      0 => $billing_tariff['id'],
      1 => $billing_tariff['name'],
      'data-justification' => $billing_tariff['with_justification'],
      'data-ends-on' => $billing_tariff['with_ends_on']
    );
  
  return select_tag($name, $billing_tariff_id, $collection, $html_options);
}

function billing_tariff_field($object_name, $method_name, $label, $billing_tariffs, $options = null, $html_options = null)
{
  $error = get_object_error($object_name, $method_name);
  $error_tag = ($error != null ? "<span class='help-inline error'>".$error."</span>" : '');

  $required = get_object_value($options, 'required');
  
  if (!$html_options) $html_options = array();
  $html_options['class'] = get_object_value($html_options, 'class', '').' billing_tariff';
  
  $hidden = count($billing_tariffs) == 0;
  
  $html = '<div class="control-group select billing_tariff_container" '.($hidden ? 'style="display:none;"' : '').'>';
  $html.= '<label class="control-label" for="'.get_field_id($object_name, $method_name).'">'.field_required_tag().$label.'</label>';
  $html.= '<div class="controls">';
  $html.= billing_tariff_select_tag(get_field_name($object_name, $method_name), get_object_value($object_name, $method_name), $billing_tariffs, $html_options).$error_tag;
  $html.= "</div></div>";
    
  return $html;
}

function passport_field($object_name, $label, $options = null) {
  if (get_object_value($options, 'read_only')){
    return read_only_field($object_name, 'passport', $label, null, array('value' => format_passport($object_name)));
  } else {

    $error = get_object_error($object_name, 'passport');
    $error_tag = ($error != null ? "<span class='help-inline error'>".$error."</span>" : '');
    
    $required = get_object_value($options, 'required');
    
    $html = "<div class='control-group passport".($error != null ? ' error' : null)."'><label for='passport_identifier' class='control-label'>".($required ? field_required_tag() : '').$label."</label><div class='controls'>";
    $html.= text_field_tag(get_field_name($object_name, 'passport_identifier'), get_object_value($object_name, 'passport_identifier'), array('class' => 'input-small passport-id'));
    $html.= "<span class='help-inline'>выдан&nbsp;&nbsp;</span>";
    $html.= date_field_tag(get_field_name($object_name, 'passport_issued_on'), get_object_value($object_name, 'passport_issued_on'));
    $html.= "<span class='help-inline'>кем&nbsp;&nbsp;</span>";
    $html.= text_field_tag(get_field_name($object_name, 'passport_issued_by'), get_object_value($object_name, 'passport_issued_by'), array('class' => 'span4'));
    $html.= $error_tag;
    $html.= "</div></div>";
    
    return $html;
  }
}

function hash_to_kv_array($hash) {
  $array = array();
  foreach($hash as $key => $value)
    $array[] = array('key' => $key, 'value' => $value);
  return $array;
}

function prepare_phone_for_search($value) {
  if ($value) {
    $value = preg_replace('/[^0-9]/', '', $value);

    if (preg_match('/^375\d{9}$/', $value))
      $value = '+'.$value;

    else if (preg_match('/^3750\d{9}$/', $value))
      $value = '+375'.substr($value,4);

    else if (preg_match('/^\d{9}$/', $value))
      $value = '+375'.$value;

    else if (preg_match('/^80\d{9}$/', $value))
      $value = '+375'.substr($value,2);
      
    else if (preg_match('/^\d{7}$/', $value))
      $value = '+375__'.$value;
      
    else if (preg_match('/^\d{6}$/', $value))
      $value = '+37516_'.$value;

    else if (preg_match('/^\d{5}$/', $value))
      $value = '+37516__'.$value;
  }
  return $value;
}

function prepare_phone($value) {
  if ($value) {
    $value = preg_replace('/[^0-9]/', '', $value);

    if (preg_match('/^375\d{9}$/', $value))
      $value = '+'.$value;

    else if (preg_match('/^3750\d{9}$/', $value))
      $value = '+375'.substr($value,4);

    else if (preg_match('/^\d{9}$/', $value))
      $value = '+375'.$value;

    # Converts it to international format.
    else if (preg_match('/^80\d{9}$/', $value))
      $value = '+375'.substr($value,2);
      
  }
  return $value;
}

function phone_with_icon($value) {
  $op = '';
  if (!$value) {
    return $value;
  } else if (preg_match('/\+375(29(2|5|7|8)|33\d)\d{6}/', $value) == 1) {
    $op = 'mts';
  } else if (preg_match('/\+375(29(1|3|6|9)|44\d)\d{6}/', $value) == 1) {
    $op = 'velcom';
  } else if (preg_match('/\+37525\d{7}/', $value) == 1) {
    $op = 'life';
  } else {
    $op = 'btk';
  }

  return '<span class="phone-with-icon '.$op.'">'.format_phone($value).'</span>';
}

function format_phone($value) {
  if (!$value) return $value;
  $value = prepare_phone($value);
  return '8 (0'.substr($value,4,2).') '.substr($value,6,3).'-'.substr($value,9,2).'-'.substr($value,11,2);
}

function prepare_name($value) {
  return $value ? mb_ucfirst(mb_strtolower(mb_trim($value), "UTF-8")) : $value;
}

function format_name($subscriber) {
  return $subscriber['last_name'].' '.$subscriber['first_name'].' '.$subscriber['middle_name'];
}

function format_address($object_name, $full = false) {
  $address = '';
  if (($full || !isset($_SESSION['selected_region'])) && get_object_value($object_name, 'city')) $address .= get_object_value($object_name, 'city').', ';
  
  $address .= get_object_value($object_name, 'street').', ';
  $address .= get_object_value($object_name, 'house');
  
  $building = get_object_value($object_name, 'building');
  if ($building) $address .= '/'.$building;
  
  $apartment = get_object_value($object_name, 'apartment');
  if ($apartment) $address .= '-'.$apartment;
  
  return $address;
}

function format_passport($object_name) {
  $passport = '';
  $passport .= get_object_value($object_name, 'passport_identifier').', ';
  $passport .= get_object_value($object_name, 'passport_issued_by').', ';
  $passport .= format_date(get_object_value($object_name, 'passport_issued_on'));
  return $passport;
}

function format_tariff($billing_tariff) {
  return $billing_tariff ? 
    '<a class="tooltip-balloon" data-content="'.
      $billing_tariff['description'].'<br>'.
      ($billing_tariff['city'] ? 'Только для <b>'.$billing_tariff['city'].'</b>'.($billing_tariff['default'] ? ' <i>(по умолчанию)</i>' : '').'<br>' : '').
      'Подключение: <b>'.format_money($billing_tariff['activation_fee']).'</b><br>'.
      'Ежемесячный платеж: <b>'.format_money($billing_tariff['subscription_fee']).'</b><br>'.
      '" rel="popover" href="#" data-original-title="'.$billing_tariff['name'].'">'.search_highlight_whole($billing_tariff['name']).'</a>'
    : null;
}

function format_subscribers_stats($title, $total, $details) { 
    $html = '<a class="tooltip-balloon" data-content="';
  
  foreach($details as $detail)
    $html.= $detail['count'].' - '.format_address($detail).'<br>';
    
    $html.= '" rel="popover" href="#" data-original-title="'.$title.'">'.$total.'</a>';
  return $html;
}

function format_requests_stats($title, $total, $details) { 
  $html = '<a class="tooltip-balloon" data-content="';
  
  $level = -1;
  do {
    $level += 1;
    $records = 0;
    foreach($details as $detail)
      if ($detail['count'] > $level)
        $records++;
  } while ($records > 20);
  
  $rt_id = null;
  foreach($details as $detail) {
    if ($rt_id != $detail['request_type_id']) {
      $sum = 0;
      foreach($details as $d) {
        if ($detail['request_type_id'] == $d['request_type_id'])
          $sum += $d['count'];
      }
    
      $html.= '<h5>'.$sum.' - '.$detail['request_type'].'</h5>';
      $rt_id = $detail['request_type_id'];
    }
    if ($detail['count'] > $level) $html.= $detail['count'].' - '.format_address($detail).'<br>';
  }
    
    $html.= '" rel="popover" href="#" data-original-title="'.$title.'">'.$total.'</a>';
  return $html;
}

function render_house_legend() {
  $apartment = (int)rand(10,99);
  $html = '<table class="house-legend"><tr>
    <td class="align-right good-subscriber"><a href="#">'.$apartment.'</a></td><td class="legend-label">- нет долга</td>
    <td class="align-right risk-subscriber"><a href="#">'.$apartment.'</a></td><td class="legend-label">- небольшой долг</td>
    <td class="align-right evil-subscriber"><a href="#">'.$apartment.'</a></td><td class="legend-label">- крупный долг</td>
    <td class="align-right not-subscriber">'.$apartment.'</td><td class="legend-label">- не подключен</td>
    <td class="align-right"><span class="filtered-subscriber">*</span>'.$apartment.'</td><td class="legend-label">- установлен фильтр</td>
  </tr></table>';
  return "<div>".$html."</div>";
}

// $type = subscriber_offline, subscriber_online, subscriber_debt
function render_subscriber_note_types($subscriber_note_types, $apartment, $subscriber_type, $css_class = null)
{
  $html = '<div class="dropdown">
<a class="dropdown-toggle '.$css_class.'" data-toggle="dropdown" href="#">'.$apartment.'</a>
<ul class="dropdown-menu dropdown-menu-form" role="menu">
<li>
<button type="button" class="close">×</button>
<h4>'.$apartment.' кв</h4></li>';
  foreach ($subscriber_note_types as $subscriber_note_type)
    if ($subscriber_note_type[$subscriber_type])
      $html.= '<li><label class="checkbox"><input type="checkbox" name="notes['.$apartment.'][]" value="'.$subscriber_note_type['id'].'"></input> <b>'.$subscriber_note_type['code'].'</b> - '.$subscriber_note_type['name'].'</label></li>';
  $html.= '<li><button class="btn">Закрыть</button></li>
</ul>
</div>';
  return $html;
}

function render_house($house, $mode = 'scheme', $subscriber_note_types = null) {
  global $factory;
  
  $good_subscribers = array();
  $subs = Subscriber::good_subscribers($house['id']);
  foreach($subs as $sub) 
    $good_subscribers[$sub['apartment']] = $sub['id'];

  $risk_subscribers = array();
  $subs = Subscriber::risk_subscribers($house['id']);
  foreach($subs as $sub) 
    $risk_subscribers[$sub['apartment']] = $sub['id'];
    
  $evil_subscribers = array();
  $subs = Subscriber::evil_subscribers($house['id']);
  foreach($subs as $sub) 
    $evil_subscribers[$sub['apartment']] = $sub['id'];
  
  $filtered_subscribers = array();
  $subs = Subscriber::filtered_subscribers($house['id']);
  foreach($subs as $sub) 
    $filtered_subscribers[$sub['apartment']] = $sub['id'];
  
  $schema = House::generate_schema($house);

  $max_floors = 0;
  for($e = 0; $e < count($schema); $e++) {
    if ($max_floors < count($schema[$e]))
      $max_floors = count($schema[$e]);
  }
  
  $html =  "<table class='house-schema'><tr>";
  $html .= "<td class='align-top' style='padding: 0 10px;'><table class='table table-bordered table-striped table-condensed'>";
  for ($f = $max_floors; $f >= 1; $f--) {
    $html .= "<tr><td style='font-weight:bold;' nowrap>".$f." этаж</td></tr>";
  }
  $html .= "</table></td>";
  
  for($e = 0; $e < count($schema); $e++) {
    $html .= "<td class='align-bottom' style='padding: 0 10px;'><table class='table table-bordered table-striped table-condensed'>";
    
    $max_apartments = 0; // Max apartments per entrance
    for($f = count($schema[$e]) - 1; $f >= 0; $f--) {
      if ($max_apartments < count($schema[$e][$f]))
        $max_apartments = count($schema[$e][$f]);
    }
    
    for($f = count($schema[$e]) - 1; $f >= 0; $f--) {
      if (count($schema[$e][$f]) > 0) {
        $html .= "<tr>";
        for($a = 0; $a < $max_apartments; $a++) {
          if ($a < count($schema[$e][$f])) {
            $apartment = $schema[$e][$f][$a];
            
            if (isset($good_subscribers[$apartment])) {
              $subscriber_id = $good_subscribers[$apartment];
              $apartment_class = 'good-subscriber';
              $subscriber_type = 'subscriber_online';
            } else if (isset($risk_subscribers[$apartment])) {
              $subscriber_id = $risk_subscribers[$apartment];
              $apartment_class = 'risk-subscriber';
              $subscriber_type = 'subscriber_online';
            } else if (isset($evil_subscribers[$apartment])) {
              $subscriber_id = $evil_subscribers[$apartment];
              $apartment_class = 'evil-subscriber';
              $subscriber_type = 'subscriber_debt';
            } else {
              $subscriber_id = null;
              $apartment_class = 'not-subscriber';
              $subscriber_type = 'subscriber_offline';
            }

            $filtered = '';
            if ($mode == 'scheme' && isset($filtered_subscribers[$apartment])) $filtered = '<span class="filtered-subscriber">*</span>';
            $text = '';
            if ($mode == 'scheme' && !$subscriber_note_types) $text = ($subscriber_id ? link_to_show('subscribers', $subscriber_id, $filtered.$apartment) : '<span>'.$apartment.'</span>');
          
            $html .= "<td class='".$apartment_class."'>".($subscriber_note_types ? render_subscriber_note_types($subscriber_note_types, $apartment, $subscriber_type, $apartment_class) : $text)."</td>";
          } else {
            $html .= "<td>&nbsp;</td>";
          }
        }
        $html .= "</tr>";
      }
    }
    
    $html .= "</table>";
    $html .= "<table class='table table-bordered table-striped table-condensed'><tr><td class='align-center'><span class='align-center' style='font-weight:bold;'>".($e+1)." подъезд</span></td></tr></table>";
    $html .= "</td>";
    
  }
  $html .= "</tr></table>";
  
  return "<div>".$html."</div>";
}

function rus_days($count)
{
  return $count.' '.rus_word($count, 'день', 'дня', 'дней');
}

function rus_word($count, $one, $two, $many)
{
  $st = $count % 10;
  $nd = floor($count % 100 / 10);
  
  if ($nd == 1 || $st == 0 || $st >= 5)
    return $many;
  if ($st == 1) {
    return $one;
  } else {
    return $two;
  }
}
?>