<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Роли' => url_for('roles'), 
  $role['name'] => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('role', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  include '_form.php';
?>

<?php if ($layout != 'print') {
  $audit_record = $role;
  include APP_ROOT.'/app/views/layouts/_audit.php';
} ?>

<div class="form-actions">
  <?php
  echo link_to_back(url_for('roles'));
  echo link_to(url_for('roles', 'rights', $id), 'Права', array('class' => 'btn btn-large btn btn-info'));
  echo link_to_edit('roles', $id);
  if ($role->has_users() == null) { echo link_to_destroy('roles', $id); }
  ?>
</div>

<?php echo $form->end_div(); ?>