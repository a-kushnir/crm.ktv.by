<?php echo breadcrumb(array(
  'Обходы' => url_for('inspections'), 
  format_address($inspection) => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
  <?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('inspection', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  echo $form->date('actual_date', 'Дата');
  echo $form->text('worker', 'Ответственный');
  echo $form->text_area('comment', 'Дополнительно', array('hide_blank' => true));
  echo $form->end_div();
?>
<?php 
  if (count($subscriber_notes) > 0) {
    echo '<fieldset>
<legend>Схема дома</legend>';
    echo '<div class="row">
<div class="offset1 span10">
    <table class="table table-bordered table-striped table-condensed table-hover" style="margin-top:16px;margin-bottom:0;">
  <thead>
    <th class="min-width">Отметки</th>
    <th class="min-width">Всего</th>
    <th>Квартиры</th>
  </thead>
<tbody>';
    $subscriber_note_type_id = null;
    $subscriber_note_type = null;
    foreach ($subscriber_notes as $subscriber_note) {
    
      if($subscriber_note['subscriber_note_type_id'] != $subscriber_note_type_id) {
      
      if ($subscriber_note_type_id) echo '<tr>
<td class="nobr">'.$subscriber_note_type.'</td>
<td class="align-center">'.count($apartments).'</td>
<td>'.implode(', ', $apartments).'</td>
</tr>';
      
        $apartments = array();
        $subscriber_note_type_id = $subscriber_note['subscriber_note_type_id'];
        $subscriber_note_type = '<strong>'.$subscriber_note['code'].'</strong> - '.$subscriber_note['name'];
      }
      $apartments[] = $subscriber_note['subscriber_id'] ?
        link_to(url_for('subscribers', 'show', $subscriber_note['subscriber_id']), $subscriber_note['apartment'], $subscriber_note['active'] ? null : array('class' => 'record-terminated')) :
        $subscriber_note['apartment'];
    }
    
    if ($subscriber_note_type_id) echo '<tr>
<td class="nobr">'.$subscriber_note_type.'</td>
<td class="align-center">'.count($apartments).'</td>
<td>'.implode(', ', $apartments).'</td>
</tr>';

    echo '<tbody></table></div></div></fieldset>';
  }
?>

<?php 
  if (count($amplifiers) > 0) {
    echo '<fieldset>
<legend>Усилители</legend>';
    echo '<div class="row" style="margin-top:16px;">
<div class="offset1 span10">
<table class="table table-bordered table-striped table-condensed table-hover" style="margin-top:16px;margin-bottom:0;">
  <thead>
    <th class="align-middle align-center min-width">№</th>
    <th class="align-middle">Название</th>
    <th class="align-middle">Расположение</th>
    <th class="align-middle">Комментарий</th>
    <th class="align-middle align-center">Скан</th>
  </thead>
<tbody>';
    $index = 0;
    foreach($amplifiers as $amplifier) {
      $loaded_scan = isset($amplifier_scans[$amplifier['id']]) ? $amplifier_scans[$amplifier['id']] : null;
      $index++;
      echo '<tr>'.
        '<td class="align-middle align-center"><strong>'.$index.'</strong></td>'.
        '<td class="align-middle">'.link_to(url_for('amplifiers', 'show', $amplifier['id']), $amplifier['name']).'</td>'.
        '<td class="align-middle">'.$amplifier->location().'</td>'.
        '<td class="align-middle">'.nl2br($amplifier['description']).'</td>'.
        '<td class="align-middle">'.($loaded_scan ? '<i class="icon-ok"></i> '.$loaded_scan['file_name'] : '<i class="icon icon-remove"></i>').'</td>'.
        '</tr>';
    }
    echo '<tbody></table></div></div></fieldset>';
  }
?>

<div class='form-horizontal'>
<?php
  $audit_record = $inspection;
  include APP_ROOT.'/app/views/layouts/_audit.php';
?>
</div>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_back(get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('inspections', 'index')) ?>
</div>
<?php } ?>
