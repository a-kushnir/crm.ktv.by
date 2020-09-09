<?php
class FormBuilder
{
  var $object_name;
  var $read_only;

  public function __construct($object_name, $read_only = false) {
    $this->object_name = $object_name;
    $this->read_only = $read_only;
  }
  
  public function begin_form($url, $html_options = null) {
    if (!$html_options) $html_options = array();
    $html_options['method'] = get_object_value($html_options, 'method', 'post');
    return '<form action="'.htmlspecialchars($url).'"'.tag_it($html_options).'>';
  }
  
  public function end_form() {
    return '</form>';
  }
  
  public function begin_div($html_options = null) {
    return '<div'.tag_it($html_options).'>';
  }
  
  public function end_div() {
    return '</div>';
  }
  
  public function begin_fieldset($label, $html_options = null) {
    return '<fieldset'.tag_it($html_options).'><legend>'.$label.'</legend>';
  }
  
  public function end_fieldset() {
    return '</fieldset>';
  }
  
  public function text($method_name, $label, $options = null, $html_options = null) {
    return text_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }
  
  public function read_only($method_name, $label, $options = null, $html_options = null) {
    return read_only_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }

  public function password($method_name, $label, $options = null, $html_options = null) {
    return password_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }

  public function hidden($method_name, $label, $options = null, $html_options = null) {
    return hidden_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }

  public function phone($method_name, $label, $options = null, $html_options = null) {
    return phone_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }

  public function date($method_name, $label, $options = null, $html_options = null) {
    return date_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }
  
  public function money($method_name, $label, $options = null, $html_options = null) {
    return money_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }
  
  public function text_area($method_name, $label, $options = null, $html_options = null) {
    return text_area_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options);
  }

  public function select($method_name, $label, $collection, $options = null, $html_options = null) {
    return select_field($this->object_name, $method_name, $label, $collection, $this->set_read_only_flag($options), $html_options);
  }
  
  public function radio($method_name, $label, $collection, $options = null, $html_options = null) {
    return radio_field($this->object_name, $method_name, $label, $collection, $this->set_read_only_flag($options), $html_options);
  }

  public function check_boxes($method_name, $label, $collection, $options = null, $html_options = null) {
    return check_boxes_field($this->object_name, $method_name, $label, $collection, $this->set_read_only_flag($options), $html_options);
  }

  public function check_box($method_name, $label, $options = null, $html_options = null, $checked_value = '1', $unchecked_value = '0') {
    return check_box_field($this->object_name, $method_name, $label, $this->set_read_only_flag($options), $html_options, $checked_value, $unchecked_value);
  }

  private function set_read_only_flag($options) {
    if ($this->read_only) {
      if (!$options) $options = array();
      if (!isset($options['read_only'])) $options['read_only'] = $this->read_only;
    }
    return $options;
  }
}
?>