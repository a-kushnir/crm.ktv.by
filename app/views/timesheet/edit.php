<?php echo breadcrumb(array(
  'Табель' => url_for('timesheet'), 
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('time_entry');
  echo $form->begin_form(url_for('timesheet', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php';
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('timesheet')) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>