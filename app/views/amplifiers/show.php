<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']), 
  'Усилители' => url_for('houses', 'amplifiers', $house['id']),
  $amplifier['name'] => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<div class="form-horizontal">
<?php
  $form = new FormBuilder('amplifier', true);
  include '_form.php';
?>

<?php if ($layout != 'print') {
  $audit_record = $amplifier;
  include APP_ROOT.'/app/views/layouts/_audit.php';
} ?>

<?php if ($layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_back('/houses/'.$house['id'].'/amplifiers') ?>
  <?php if (has_access('house/edit_amps')) echo link_to_edit('amplifiers', $id) ?>
  <?php if (has_access('house/destroy_amps')) { echo link_to_destroy('amplifiers', $id); } ?>
</div>
<?php } ?>
</div>
