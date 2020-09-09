<?php $javascripts[] = '/javascripts/channels.js' ?>

<?php echo breadcrumb(array(
  'Каналы' => url_for('channels'), 
  $channel['station'] => url_for('channels').'?station='.$channel['station_id'], 
  $channel['name'] => url_for('channels', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('channel');
  echo $form->begin_form(url_for('channels', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php';
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('channels', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>