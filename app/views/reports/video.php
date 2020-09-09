<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

  <?php $cameras = User::cameras(); ?>
  <div class="align-center">
  <select id="ipcam_url">
    <option value="">&lt; Выберите камеру &gt;</option>
    <?php foreach($cameras as $camera) { ?>
    <option value="<?php echo $camera['url'] ?>"><?php echo $camera['name'] ?></option>
    <?php } ?>
  </select>
  </div>
  <div id="ipcam_wnd" class="align-center no-signal">
    <img class="img-polaroid2" src="/images/no-signal.gif" />
  </div>
  <div id="ipcam_time" class="align-center"></div>

</div>
</div>
