<?php echo breadcrumb(array(
  'Работники' => url_for('workers'), 
  format_name($worker) => url_for('workers', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('worker');
  echo $form->begin_form(url_for('workers', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php';
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('workers', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>