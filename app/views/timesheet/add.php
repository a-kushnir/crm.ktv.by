<?php echo breadcrumb(array(
  'Табель' => url_for('timesheet'), 
  'Затраченное время' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('time_entry');
  echo $form->begin_form(url_for('timesheet'), array('class' => 'form-horizontal'));
  include '_form.php'
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('timesheet')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php 
  echo $form->end_form();
?>