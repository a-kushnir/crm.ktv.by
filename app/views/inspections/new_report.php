<?php echo breadcrumb(array(
  'Отчеты' => url_for('inspections'), 
  'Новый отчет' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<form method="post" class="form-horizontal">
  <?php include '_form.php' ?>
  
  <div class="form-actions">
    <?php echo link_to_back(url_for('houses')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
</form>