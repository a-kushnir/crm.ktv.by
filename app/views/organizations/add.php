<?php echo breadcrumb(array(
  'Организации' => url_for('organizations'), 
  'Новая организация' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('organization');
  echo $form->begin_form(url_for('organizations', 'create'), array('class' => 'form-horizontal'));
  include '_form.php'
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('organizations')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php
  echo $form->end_form();
?>