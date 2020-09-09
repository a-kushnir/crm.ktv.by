<?php
// Common parameters:
// $object_name - record name
// $method_name - record's field name
// $label - human readable label
// $options - supports the following keys: read_only, required, hint, addon_left, addon_right, hide_blank, without_wrapper
// $html_options - additional tag attributes

function get_field_id($object_name, $method_name = null) {
  $id = get_field_name($object_name, $method_name);
  $id = str_replace('[', '_', $id);
  $id = str_replace(']', '', $id);
  return $id;
}

function get_field_name($object_name, $method_name = null) {
  return $method_name ? $object_name.'['.$method_name.']' : $object_name;
}

function get_field_value($object_name, $method_name = null) {
  if (!$method_name && isset($_POST) && isset($_POST[$object_name]))
    return $_POST[$object_name];
  else if (!$method_name && isset($_GET) && isset($_GET[$object_name]))
    return $_GET[$object_name];
  else if (isset($_POST) && isset($_POST[$object_name]) && isset($_POST[$object_name][$method_name]))
    return $_POST[$object_name][$method_name];
  else if (isset($_GET) && isset($_GET[$object_name]) && isset($_GET[$object_name][$method_name]))
    return $_GET[$object_name][$method_name];
  else
    return null;
}

function tag_it($html_options) {
  if (isset($html_options)) {
    $result = ' ';
    foreach ($html_options as $key => $value) {
      $result .= htmlspecialchars($key).'="'.htmlspecialchars($value).'" ';
    }
    return $result;
  }
  return '';
}

function get_object_by_name($object_name) {
  if (gettype($object_name) == 'string') {
    global $$object_name;
    return $$object_name;
  } else {
    return $object_name;
  }
}

function get_object_value($object_name, $method_name, $default = null) {
  $object = get_object_by_name($object_name);
  return isset($object) && isset($object[$method_name]) ? $object[$method_name] : $default;
}

function get_object_error($object_name, $method_name) {
  $object = get_object_by_name($object_name);
  return isset($object) && isset($object->errors) && isset($object->errors[$method_name]) ? $object->errors[$method_name] : null;
}

function field_error_tag($object_name, $method_name) {
  $error_text = get_object_error($object_name, $method_name);
  return $error_text != null ? "<span class='help-inline error'>".$error_text."</span>" : '';
}

function field_hint_tag($hint_text) {
  return $hint_text ? "<p class='help-block'>".$hint_text."</p>" : '';
}

function field_required_tag() {
  return "<abbr title='".(defined('REQUIRED_FIELD') ? REQUIRED_FIELD : 'Required field')."'>*</abbr> ";
}

function field_addon_tag($addon_text) {
  return $addon_text ? "<span class='add-on'>".$addon_text."</span>" : '';
}

function table_no_data_tag() {
  return "<div class='alert'>".(defined('TABLE_NO_DATA') ? TABLE_NO_DATA : 'No data to display')."</div>";
}

function text_field_tag($name, $value, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  
  $html_options['type'] = get_object_value($html_options, 'type', 'text');
  $html_options['id'] = get_object_value($html_options, 'id', get_field_id($name));
  $html_options['name'] = get_object_value($html_options, 'name', get_field_name($name));
  $html_options['value'] = get_object_value($html_options, 'value', get_field_name($value));
  
  return "<input".tag_it($html_options)."/>";
}

function text_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  if (get_object_value($options, 'read_only')){
    return read_only_field($object_name, $method_name, $label, $options, $html_options);
  } else {
    return control_group_div_tag($object_name, $method_name, $label, $options,
      text_field_tag(get_field_name($object_name, $method_name), $value, $html_options));
  }
}

function read_only_field_tag($name, $value, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  $html_options['class'] = get_object_value($html_options, 'class', '').' input';
  unset($html_options['value']);
  return "<div".tag_it($html_options).">".$value."</div>";
}

function read_only_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  if ($options) unset($options['hint']);
  if (!get_object_value($options, 'hide_blank') || $value) {
    $input_tag = read_only_field_tag(get_field_name($object_name, $method_name), $value, $html_options);
    return control_group_div_tag($object_name, $method_name, $label, $options, $input_tag);
  } else {
    return '';
  }
}

function hidden_field_tag($name, $value, $html_options = null) {  
  $html_options = prepare_field_attributes($html_options);
  $html_options['type'] = 'hidden';
  return text_field_tag($name, $value, $html_options);
}

function hidden_field($object_name, $method_name, $label, $options = null, $html_options = null) {  
  $name = get_field_name($object_name, $method_name);
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  return hidden_field_tag($name, $value, $html_options);
}

function password_field_tag($name, $value, $html_options = null) {  
  $html_options = prepare_field_attributes($html_options);
  $html_options['type'] = 'password';
  $html_options['value'] = '';
  return text_field_tag($name, $value, $html_options);
}

function password_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  $html_options['type'] = 'password';
  $html_options['value'] = '';
  return text_field($object_name, $method_name, $label, $options, $html_options);
}

function phone_field_tag($name, $value, $html_options = null) {
  $value = get_object_value($html_options, 'value', $value);
  
  $html_options = prepare_field_attributes($html_options);
  $html_options['value'] = format_phone($value);
  $html_options['class'] = get_object_value($html_options, 'class', '').' phone-number input-medium';
  
  return text_field_tag($name, $value, $html_options);
}

function phone_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $read_only = get_object_value($options, 'read_only');
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  
  $html_options = prepare_field_attributes($html_options);
  $html_options['value'] = $read_only ? phone_with_icon($value) : format_phone($value);
  $html_options['class'] = get_object_value($html_options, 'class', '').' phone-number input-medium';
  
  return text_field($object_name, $method_name, $label, $options, $html_options);
}

function date_field_tag($name, $value, $html_options = null) {
  $value = get_object_value($html_options, 'value', $value);
  
  $html_options = prepare_field_attributes($html_options);
  $html_options['value'] = format_date($value);
  $html_options['class'] = get_object_value($html_options, 'class', '').' date-picker input-small align-center';
  
  return text_field_tag($name, $value, $html_options);
}

function date_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $read_only = get_object_value($options, 'read_only');
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  
  $html_options = prepare_field_attributes($html_options);
  $html_options['value'] = $read_only ? human_date($value) : format_date($value);
  if (!$read_only) $html_options['class'] = get_object_value($html_options, 'class', '').' date-picker input-small align-center';
  
  return text_field($object_name, $method_name, $label, $options, $html_options);
}

function money_field_tag($name, $value, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  return text_field_tag($name, $value, $html_options);
}

function money_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  if (get_object_value($options, 'read_only')) {
    $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
    $html_options = prepare_field_attributes($html_options);
    $html_options['value'] = format_money($value);
  } else {
    if (!$options) $options = array();
    if (defined('CURRENCY_PREFIX')) $options['left_addon'] = get_object_value($options, 'left_addon', CURRENCY_PREFIX);
    if (defined('CURRENCY_SUFFIX')) $options['right_addon'] = get_object_value($options, 'right_addon', CURRENCY_SUFFIX);
  }

  return text_field($object_name, $method_name, $label, $options, $html_options);
}

function text_area_tag($name, $value, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  
  $html_options['id'] = get_object_value($html_options, 'id', get_field_id($name));
  $html_options['name'] = get_object_value($html_options, 'name', get_field_name($name));
  
  $value = get_object_value($html_options, 'value', get_field_name($value));
  unset($html_options['value']);
  
  return "<textarea".tag_it($html_options).">".htmlspecialchars($value)."</textarea>";
}

function text_area_field($object_name, $method_name, $label, $options = null, $html_options = null) {
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  if (get_object_value($options, 'read_only')){
    $html_options = prepare_field_attributes($html_options);
    $html_options['value'] = nl2br($value);
    return read_only_field($object_name, $method_name, $label, $options, $html_options);
  } else {
    return control_group_div_tag($object_name, $method_name, $label, $options,
      text_area_tag(get_field_name($object_name, $method_name), $value, $html_options));
  }
}

function get_field_collection($items, $key, $value, $include_blank = null) {
  $result = array();
  if (!is_null($include_blank)) $result[] = array('', $include_blank);
  
  foreach($items as $item)
    $result[] = array($item[$key], $item[$value]);
  
  return $result;
}

function select_tag($name, $value, $collection, $html_options = null) {
  $value = get_object_value($html_options, 'value', $value);

  $options = '';
  foreach($collection as $item) {
  
    $attributes = array();
    foreach ($item as $k => $v)
      if ($k !== 0 && $k !== 1)
        $attributes[$k] = $v;
    
    $attributes['value'] = $item[0];
    if ($item[0] == $value) $attributes['selected'] = 'selected';
   
    $options .= "<option ".tag_it($attributes).">".htmlspecialchars($item[1])."</option>";
  }
  
  $html_options = prepare_field_attributes($html_options);
  
  $html_options['id'] = get_object_value($html_options, 'id', get_field_id($name));
  $html_options['name'] = get_object_value($html_options, 'name', get_field_name($name));
  
  return "<select ".tag_it($html_options).">".$options."</select>";
}

function select_field($object_name, $method_name, $label, $collection, $options = null, $html_options = null) {
  $read_only = get_object_value($options, 'read_only');
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));
  
  $input = null;
  if ($read_only) {
    $text = '';
    foreach($collection as $item)
      if ($item[0] == $value) $text = $item[1];

    if (!get_object_value($options, 'hide_blank') || $text) {
      $input = read_only_field_tag(get_field_name($object_name, $method_name), $text, $html_options);
    } else {
      return '';
    }
  } else {
    $input = select_tag(get_field_name($object_name, $method_name), $value, $collection, $html_options);
  }
  return control_group_div_tag($object_name, $method_name, $label, $options, $input);
}

function radio_tag($name, $value, $collection, $html_options = null)
{
  $result = '';
  foreach($collection as $item) {
  
    $attributes = $html_options;
    foreach ($item as $k => $v)
      if ($k !== 0 && $k !== 1)
        $attributes[$k] = $v;
    
    $attributes['id'] = get_field_id($name.'_'.$item[0]);
    $attributes['name'] = get_field_name($name);
    $attributes['value'] = $item[0];
    if ($item[0] == $value) $attributes['checked'] = 'checked';
    
    $result .= '<label class="radio"><input class="radio_buttons" type="radio" '.tag_it($attributes).'/>'.htmlspecialchars($item[1]).'</label>';
  }
  return $result;
}

function radio_field($object_name, $method_name, $label, $collection, $options = null, $html_options = null)
{
  $html_options = prepare_field_attributes($html_options, $options);
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));

  return control_group_div_tag($object_name, $method_name, $label, $options,
    radio_tag(get_field_name($object_name, $method_name), $value, $collection, $html_options));
}

function check_boxes_tag($name, $value, $collection, $html_options = null)
{
  $result = '';
  foreach($collection as $item) {
  
    $attributes = $html_options;
    foreach ($item as $k => $v)
      if ($k !== 0 && $k !== 1)
        $attributes[$k] = $v;
    
    $attributes['id'] = get_field_id($name.'_'.$item[0]);
    $attributes['name'] = get_field_name($name).'[]';
    $attributes['value'] = $item[0];
    if (is_array($value) && in_array($item[0], $value) || $item[0] == $value) $attributes['checked'] = 'checked';
    
    $result .= '<label class="checkbox"><input type="checkbox" '.tag_it($attributes).'/>'.htmlspecialchars($item[1]).'</label>';
  }
  return $result;
}

function check_boxes_field($object_name, $method_name, $label, $collection, $options = null, $html_options = null)
{
  $html_options = prepare_field_attributes($html_options, $options);
  $value = get_object_value($html_options, 'value', get_object_value($object_name, $method_name));

  return control_group_div_tag($object_name, $method_name, $label, $options,
    check_boxes_tag(get_field_name($object_name, $method_name), $value, $collection, $html_options));
}

function check_box_tag($name, $value = '1', $checked = false, $html_options = null) {
  $html_options = prepare_field_attributes($html_options);
  
  $html_options['id'] = get_object_value($html_options, 'id', get_field_id($name));
  $html_options['name'] = get_object_value($html_options, 'name', get_field_name($name));
  $html_options['type'] = get_object_value($html_options, 'type', 'checkbox');
  $html_options['value'] = get_object_value($html_options, 'value', get_field_name($value));
  
  $checked = get_object_value($html_options, 'checked', $checked);
  if ($checked) $html_options['checked'] = 'checked';
  else unset($html_options['checked']);
  
  return "<input ".tag_it($html_options)."/>";
}

function check_box_field($object_name, $method_name, $label, $options = null, $html_options = null, $checked_value = '1', $unchecked_value = '0') {
  $html_options = prepare_field_attributes($html_options, $options);
  
  $name = get_object_value($html_options, 'name', get_field_name($object_name, $method_name));
  $checked = get_object_value($html_options, 'checked', get_object_value($object_name, $method_name, false));
  
  $input_tag = '';
  if (!is_null($unchecked_value)) $input_tag .= hidden_field_tag($name, $unchecked_value, array('id' => '')); // Fixes label for check boxes 
  $input_tag .= check_box_tag($name, $checked_value, $checked, $html_options);
  
  return control_group_div_tag($object_name, $method_name, $label, $options, $input_tag, 'include');
}

function control_group_div_tag($object_name, $method_name, $label, $options, $input_tag, $label_placement = 'before') {
  $error_tag = field_error_tag($object_name, $method_name);
  $hint_tag = field_hint_tag(get_object_value($options, 'hint'));
  $required_tag = get_object_value($options, 'required') && !get_object_value($options, 'read_only') ? field_required_tag() : null;
  $left_addon_tag = !get_object_value($options, 'read_only') ? field_addon_tag(get_object_value($options, 'left_addon')) : '';
  $right_addon_tag = !get_object_value($options, 'read_only') ? field_addon_tag(get_object_value($options, 'right_addon')) : '';
  $without_wrapper = field_hint_tag(get_object_value($options, 'without_wrapper'));
  
  return ($without_wrapper ? '' : "<div class='control-group".($error_tag ? ' error' : null)."'>").
  ($label_placement == 'before' ? "<label for='".get_field_id($object_name, $method_name)."' class='control-label'>".$required_tag.htmlspecialchars($label)."</label>": '').
  ($without_wrapper ? '' : "<div class='controls'>").
  ($label_placement == 'include' ? "<label for='".get_field_id($object_name, $method_name)."' class='checkbox'>": '')."
  ".($left_addon_tag || $right_addon_tag ? "<div class='".($left_addon_tag ? 'input-prepend' : '')." ".($right_addon_tag ? 'input-append' : '')."'>" : "")."
  ".$left_addon_tag.$input_tag.$right_addon_tag."
  ".($left_addon_tag || $right_addon_tag ? "</div>" : "")."
  ".($label_placement == 'include' ? $required_tag.htmlspecialchars($label)."</label>" : '')."
  ".$error_tag.$hint_tag.
  ($without_wrapper ? '' : '</div></div>');
}



// !!TODO


function page_header($title, $subtitle) {
  $html = '';
  
  // html version
  $html.= "<div class='page-header visible-html'><h1>";
  $html.= $title;
  if ($subtitle != null) $html.= " <small>".htmlspecialchars($subtitle)."</small>";
  $html.= "</h1></div>";
  
  // print version
  $html.= "<h3 class='visible-print'>".htmlspecialchars($title);
  if ($subtitle != null) $html.= " <small>".htmlspecialchars($subtitle)."</small>";
  $html.= "</h3>";
  
  return $html;
}

function flash_alert($text) {
  $_SESSION['flash-alert'] = $text;
}

function flash_notice($text) {
  $_SESSION['flash-notice'] = $text;
}

function alert_block($type, $title, $text) {
  $html = '<div class="alert alert-blo2ck alert-'.$type.'">';
  $html.= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
  if ($title) $html.= '<h4 class="alert-heading">'.$title.'</h4>';
  $html.= $text;
  $html.= '</div>';
  return $html;
}

function render_progress_bar($value, $html_options = null) {
  return '<div class="progress progress-striped active" '.tag_it($html_options).'>'.
         '<div class="bar" style="width: '.round($value*100).'%;">'.format_percent($value).'</div>'.
         '</div>';
}

function search_highlight_part($value) {
  $filter = get_field_value('filter');
  if ($filter) {
    $words = array_filter(explode(' ', $filter), 'strlen');
    foreach($words as $word)
      $value = preg_replace("|(".preg_quote($word).")|iu" , "<span class='search_highlight'>$1</span>",$value);
  }
  return $value;
}

function search_highlight_full($value) {
  $filter = get_field_value('filter');
  if ($filter) {
    $words = array_filter(explode(' ', $filter), 'strlen');
    foreach($words as $word)
      if ($value == $word)
        $value = "<span class='search_highlight'>".$value."</span>";
  }
  return $value;
}

function search_highlight_whole($value) {
  $filter = get_field_value('filter');
  if ($filter) {
    $value = preg_replace("|(".preg_quote($filter).")|iu" , "<span class='search_highlight'>$1</span>",$value);
  }
  return $value;
}

function search_button($params = '', $hint = '') {
  $js_handler = 'window.location = \'?filter=\'+encodeURIComponent(jQuery(\'#filter\').val()) + \''.$params.'\'';
  return '<div class="well form-search align-center visible-desktop hidden-print" style="float:right;">'.
    '<div class="input-append">'.
    '<input id="filter" type="text" class="input-large search-query" value="'.get_field_value('filter').'" onkeypress="if(event.which == 13){'.$js_handler.'}" />&nbsp;
    <button type="submit" class="btn" onclick="'.$js_handler.'"><i class="icon-search"> </i> Поиск <small>'.$hint.'</small></button>'.
    '</div>'.
    '</div>';
}

function print_version_button($params = '', $name = 'Версия для печати') {
  return '<a class="btn" href="javascript://" onclick="window.open(window.location.pathname + \'.print'.($params ? '?':'').$params.'\', \'ts_print_version_\'+Math.random());"><i class="icon-print"></i> '.$name.'</a>';
}

function show_section($id) {
  return '<a id="show_'.$id.'" href="#" class="btn btn-mini float-right" onclick="return showSection(\''.$id.'\');"><i class="icon-chevron-down"> </i></a>
  <a id="hide_'.$id.'" href="#" class="btn btn-mini float-right" onclick="return hideSection(\''.$id.'\');" style="display:none;"><i class="icon-chevron-up"> </i></a>';
}

function arrow_image($type)
{
  if ($type == 0)
    return '';
  else if ($type > 0)
    return '<i class="icon-arrow-up"></i>';
  else if ($type < 0)
    return '<i class="icon-arrow-down"></i>';
}

function radio_field2($object, $name, $label, $values, $key, $value, $options = null, $html_options = null)
{
  $required = get_object_value($options, 'required');
  $raw_html = get_object_value($options, 'raw_html');
  
  $error = get_object_error($object, $name);
  $error_tag = ($error != null ? "<span class='help-inline error'>".$error."</span>" : '');
  
  $selected = get_object_value($object, $name);
  return '<div class="control-group radio_buttons '.($error != null ? ' error' : null).'">
    <label class="control-label" for="'.$name.'">'.($required ? required_tag() : '').$label.'</label>
    <div class="controls">'.radio_tag($name, $values, $key, $value, $selected, $raw_html).$error_tag.'</div></div>';
}

function radio_tag2($name, $values, $key, $value, $selected = null, $raw_html = false) {
  $result = "";
  
  foreach($values as $option)
  {
    $checked = $option[$key] == $selected ? "checked='checked'" : "";
    $result .= '<label class="radio"><input class="radio_buttons" id="'.get_field_id($name.'_'.$option[$key]).'" name="'.get_field_name($name).'" type="radio" value="'.$option[$key].'" '.$checked.'/>'.($raw_html ? $option[$value] : htmlspecialchars($option[$value])).'</label>';
  }
  return $result;
}

function submit_button($name, $options = null) {
  return "<input type='submit' value='".$name."' class='btn btn-large btn-primary' ".tag_it($options)." />";
}

function prepare_field_attributes($html_options, $options = null)
{
  if (!$html_options) $html_options = array();

  $disabled = get_object_value($html_options, 'disabled', get_object_value($options, 'read_only', false));
  if ($disabled) $html_options['disabled'] = 'disabled';
  else unset($html_options['disabled']);
  
  return $html_options;
}
?>