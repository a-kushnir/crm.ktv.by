<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Каналы' => url_for('channels'), 
  $channel['station'] => url_for('channels').'?station='.$channel['station_id'], 
  $channel['name'] => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('channel', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('channels').'?station='.$channel['station_id']) ?>
    <?php if (has_access('channel/edit')) echo link_to_edit('channels', $id) ?>
    <?php if (has_access('channel/edit')) echo link_to_destroy('channels', $id) ?>
  </div>
<?php 
  echo $form->end_div();
?>