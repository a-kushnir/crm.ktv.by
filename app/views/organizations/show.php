<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Организации' => url_for('organizations'), 
  $organization['name'] => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('organization', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  include '_form.php'
?>
  <?php if ($layout != 'print') { ?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('organizations')) ?>
    <?php if (has_access('organization/edit') && $organization['active']) echo link_to_edit('organizations', $id) ?>
    <?php if (has_access('organization/destroy') && $organization['active']) echo link_to_destroy('organizations', $id) ?>
  </div>
  <?php } ?>
<?php 
  echo $form->end_div();
?>
