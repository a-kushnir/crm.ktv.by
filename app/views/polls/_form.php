<p>Здравствуйте, кабельное телевидение ТелеСпутник, оператор <?php echo first_word($_SESSION['user_name']) ?>.</p>
<?php
  echo radio_field(null, 'q0', 'Можете ли вы ответить на пару вопросов?', hash_to_kv_array(
    array('yes' => 'Да',
          'no' => 'Нет',
          )), 'key', 'value');
?>
<div id='cannot_anwser' style='display:none;'>
<p>Извините, всего доброго!</p>
</div>
<div id='can_anwser' style='display:none;'>
<?php
  echo '<hr>';
  
  echo radio_field(null, 'q1', 'Устраивает ли вас скорость обслуживания?', hash_to_kv_array(
    array(3 => 'Быстро',
          2 => 'Нормально',
          1 => 'Медленно',
          )), 'key', 'value');
  
  echo text_area_field(null, 'q7', 'Комментарий');
  
  echo '<hr>';
  
  echo radio_field(null, 'q2', 'Достаточно ли профессиональны сотрудники?', hash_to_kv_array(
    array(3 => 'Хорошо',
          2 => 'Средне',
          1 => 'Плохо',
          )), 'key', 'value');
  
  echo text_area_field(null, 'q7', 'Комментарий');
  
  echo '<hr>';
  
  echo radio_field(null, 'q3', 'Достаточно ли вежливы сотрудники?', hash_to_kv_array(
    array(3 => 'Вежливые',
          2 => 'Нормальные',
          1 => 'Грубые',
          )), 'key', 'value');

  echo text_area_field(null, 'q7', 'Комментарий');
  
  echo '<hr>';
  
  echo radio_field(null, 'q4', 'Устраивает ли вас качество каналов?', hash_to_kv_array(
    array(3 => 'Хорошее',
          2 => 'Среднее',
          1 => 'Плохое',
          )), 'key', 'value');

  echo text_area_field(null, 'q7', 'Комментарий');
  
  echo '<hr>';
  
  echo radio_field(null, 'q5', 'Готовы ли вы рекомендовать ТелеСпутник своим друзьям и знакомым?', hash_to_kv_array(
    array(2 => 'Да',
          1 => 'Нет',
          )), 'key', 'value');
          
  echo text_area_field(null, 'q7', 'Почему?')
?>
<p>Спасибо что ответили на наши вопросы. До свидания!</p>
</div>

<script>
  jQuery('#q0_yes,#q0_no').click(function() {
    if(jQuery('#q0_yes').is(':checked')) {
      jQuery('#cannot_anwser').hide();
      jQuery('#can_anwser').show('fast');
    } else {
      jQuery('#can_anwser').hide();
      jQuery('#cannot_anwser').show('fast');
    }
  });
</script>
