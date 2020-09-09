<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Работники' => url_for('workers'),
  format_name($worker) => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('worker', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('workers')) ?>
    <?php if (has_access('worker/edit') && $worker['active']) echo link_to_edit('workers', $id) ?>
    <?php if (has_access('worker/destroy') && $worker['active']) echo link_to_destroy('workers', $id, 'Уволить') ?>
  </div>
<?php 
  echo $form->end_form();
?>