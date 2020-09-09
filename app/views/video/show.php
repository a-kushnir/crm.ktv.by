<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Видео' => url_for('video'), 
  $camera['name'] => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('camera', true);
  echo $form->begin_div(array('class' => 'form-horizontal'));
  // include '_form.php';
?>

<select id="ipcam_url" style="display:none;">
  <option value="<?php echo $camera['url'] ?>"><?php echo $camera['name'] ?></option>
</select>
<div id="ipcam_wnd" class="align-center no-signal">
  <img class="img-polaroid2" src="/images/no-signal.gif" />
</div>
<div id="ipcam_time" class="align-center"></div>

<div class="form-actions">
  <?php
  echo link_to_back(url_for('video'));
  if (has_access('video/edit')) echo link_to_edit('video', $id);
  if (has_access('video/destroy')) echo link_to_destroy('video', $id);
  ?>
</div>

<?php echo $form->end_div(); ?>