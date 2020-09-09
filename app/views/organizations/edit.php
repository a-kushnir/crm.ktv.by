<?php echo breadcrumb(array(
  'Организации' => url_for('organizations'), 
  $organization['name'] => url_for('organizations', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('organization');
  echo $form->begin_form(url_for('organizations', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php'
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('organizations', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php
  echo $form->end_form();
?>