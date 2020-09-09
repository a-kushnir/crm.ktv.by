<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']), 
  'Усилители' => url_for('houses', 'amplifiers', $house['id']),
  'Новый усилитель' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('amplifier');
  echo $form->begin_form('/amplifiers/'.$id.'/new', array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('houses')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php 
  echo $form->end_form();
?>