<?php echo breadcrumb(array(
  'Видео' => url_for('video'),
  $camera['name'] => url_for('roles', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('camera');
  echo $form->begin_form(url_for('video', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php'
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('video', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>