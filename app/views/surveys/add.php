<?php echo breadcrumb(array(
  'Опросы' => url_for('surveys'), 
  $subscriber->name() => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<form method="post" class="form-horizontal">
  <?php include '_form.php' ?>

  <div class="form-actions">
    <?php echo link_to_back(url_for('subscribers')) ?>
    <?php echo submit_button("Сохранить"); ?>
  </div>
</form>
