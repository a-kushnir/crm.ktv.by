<?php echo breadcrumb(array(
  'Видео' => url_for('video'), 
  'Новая камера' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('camera');
  echo $form->begin_form(url_for('video'), array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('camera')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php 
  echo $form->end_form();
?>