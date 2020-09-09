<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']), 
  'Усилители' => url_for('houses', 'amplifiers', $house['id']),
  $amplifier['name'] => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('amplifier');
  echo $form->begin_form(url_for('amplifiers', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php'
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('amplifiers', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>