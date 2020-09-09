<div class="row">
<div class="span6 offset3">
<?php echo page_header($title, $subtitle); ?>

<form method="post" class="form-horizontal">

<div class="control-group">
  <label class="control-label" for="login"><?php echo field_required_tag() ?> Имя пользователя</label>
  <div class="controls">
    <input type="text" name="login" value="<?php echo isset($_POST['login']) ? $_POST['login'] : null; ?>" size="20" />
  </div>
</div>
<div class="control-group">
  <label class="control-label" for="password"><?php echo field_required_tag() ?> Пароль</label>
  <div class="controls">
    <input type="password" name="password" size="20" />
  </div>
</div>

<div class="form-actions">
  <?php echo submit_button('Войти') ?>
</div>

</form>
</div>
</div>