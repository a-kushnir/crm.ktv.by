<?php
  echo hidden_field_tag(null, 'request_id', get_object_value($request, 'id'));
  echo hidden_field_tag($request, 'subscriber_id', $request ? null : get_object_value($subscriber, 'id'));
  echo hidden_field_tag($subscriber, 'house_id');
  echo hidden_field_tag($subscriber, 'apartment');
  
  echo readonly_field('Адрес', format_address($subscriber));
  
  if ($request) {
    echo readonly_field('Заявка', get_object_value($request, 'request_type'));
    
    $created = parse_db_datetime($request['created_at']);
    $closed = parse_db_date($request['handled_on']);

    if ($closed - $created <= Request::$wait_normal * 86400) {
      $css_class = 'wait_short';
    } else if ($closed - $created <= Request::$wait_long * 86400) {
      $css_class = 'wait_normal';
    } else {
      $css_class = 'wait_long';
    }
    echo readonly_field('Срок выполнения', '<span class="'.$css_class.'">'.delta_days($closed, $created).'</span>');
  }
  
  $cell_phone = get_object_value($subscriber, 'cell_phone');
  $home_phone = get_object_value($subscriber, 'home_phone');

  if ($cell_phone && $home_phone) {  
    echo radio_field(null, 'phone_number', 'Номер телефона', hash_to_kv_array(
      array($cell_phone => phone_with_icon($cell_phone),
            $home_phone => phone_with_icon($home_phone),
            )), 'key', 'value', array('raw_html' => true, 'required' => true));
  } else if ($cell_phone || $home_phone) {
    $phone_number = $cell_phone ? $cell_phone : $home_phone;
    echo hidden_field_tag(null, 'phone_number', $phone_number);
    echo readonly_field('Номер телефона', phone_with_icon($phone_number));
  }
  echo '<hr>';
?>

<p>Здравствуйте, кабельное телевидение ТелеСпутник, оператор <?php echo first_word($_SESSION['user_name']) ?>.</p>
<?php
  echo radio_field(null, 'can_answer', 'Можете ли вы ответить на пару вопросов?', hash_to_kv_array(
    array('yes' => 'Да',
          'no' => 'Нет',
          )), 'key', 'value', array('required' => true));
?>
<div id='cannot_anwser' style='display:none;'>
<p>Извините, всего доброго!</p>
</div>
<div id='can_anwser' style='display:none;'>
<?php
  echo '<hr>';
  
  $question_ids = array();
  foreach($survey_form as $survey_item)
    if (!in_array($survey_item['question_id'], $question_ids))
      $question_ids[] = $survey_item['question_id'];
    
  foreach($question_ids as $question_id) {
    $answers = array();
    foreach($survey_form as $survey_item)
      if ($survey_item['question_id'] == $question_id)
        $answers[] = $survey_item;
  
    echo radio_field(null, 'answer['.$question_id.']', $answers[0]['question_text'], $answers, 'answer_id', 'answer_text', array('required' => true));
    if ($answers[0]['comment'])
      echo text_area_field(null, 'comment['.$question_id.']', $answers[0]['comment']);
    echo '<hr>';
  }
?>
<p>Спасибо что ответили на наши вопросы. До свидания!</p>
</div>

<script>
  jQuery('#can_answer_yes,#can_answer_no').click(function() {
    if(jQuery('#can_answer_yes').is(':checked')) {
      jQuery('#cannot_anwser').hide();
      jQuery('#can_anwser').show('fast');
    } else {
      jQuery('#can_anwser').hide();
      jQuery('#cannot_anwser').show('fast');
    }
  });
</script>
