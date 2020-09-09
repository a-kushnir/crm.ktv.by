<?php
class Survey extends ModelBase {
  var $table_name = 'surveys';
  public static $page_size = 10;
  
  var $form = null;
  var $details = array();

  static function load_form() {
    $query = 'SELECT sq.`id` question_id, sq.`text` question_text, sq.`comment`, sa.`id` answer_id, sa.`text` answer_text 
      FROM survey_questions sq
      JOIN survey_answers sa ON sq.`id` = sa.`survey_question_id`
      WHERE sq.`active` = true AND sa.`active` = true
      ORDER BY sq.`position`, sa.`position`';
    
    global $factory;
    $rows = $factory->connection->execute($query);
    
    $result = array();
    while($row = mysql_fetch_array($rows))
      $result[] = new Survey($row);
    unset($rows);
    
    return $result;
  }
  
  function load_attributes($id = null)
  {
    $this['id'] = $id;
    $this['subscriber_id'] = get_field_value('subscriber_id');
    $this['house_id'] = get_field_value('house_id');
    $this['apartment'] = get_field_value('apartment');
    $this['request_id'] = get_field_value('request_id');
    $this['phone_number'] = get_field_value('phone_number');
    
    if(!$this->form) $this->form = Survey::load_form();
    
    $answers = get_field_value('answers');
    $comments = get_field_value('comments');
    
    $this->details = array();
    foreach($this->form as $form_item) {
      $question_id = $form_item['question_id'];
      $this->details[] = new Survey(array(
        'survey_question_id' => $question_id,
        'survey_answer_id' => isset($answers[$question_id]) ? $answers[$question_id] : null,
        'comment' => isset($comments[$question_id]) ? $comments[$question_id] : null,
      ));
    }
    
    if ($this->is_new()) {
      $this->attributes['created_by'] = $_SESSION['user_id'];
      $this->attributes['created_at'] = date(MYSQL_TIME, time());
    }
  }
  
  function validate()
  {
    if ($this['house_id'] == null) $this->errors['house_id'] = ERROR_BLANK;
    if ($this['apartment'] == null) $this->errors['apartment'] = ERROR_BLANK;
    if ($this['phone_number'] == null) $this->errors['phone_number'] = ERROR_BLANK;
    
    if(!$this->form) $this->form = Survey::load_form();
    
    foreach($this->details as $detail) {
      $answer_required = true; // ALL answers are required
      $comment_required = false; // ALL comments are optional
      /*foreach($this->form as $form_item)
        if ($form_item['question_id'] == $detail['survey_question_id'])
          $answer_required = $form_item['required'];*/
    
      if ($answer_required && $detail['survey_answer_id'] == null) $detail->errors['survey_answer_id'] = ERROR_BLANK;
      if ($comment_required && $detail['comment'] == null) $detail->errors['comment'] = ERROR_BLANK;
    }
    
    foreach($this->details as $detail)
      if (count($detail->errors) > 0)
        $this->errors['details'] = true;
  }
}

?>