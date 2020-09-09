<?php echo breadcrumb(array(
  'Дома' => (get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('houses', 'index')),
  format_address($house) => url_for('houses', 'show', $house['id']), 
  'Обходной отчет' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<form action="<?php echo '/inspections/'.$id.'/new?index='.get_field_value('index') ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
  <?php include '_form.php' ?>
  
  <div class="form-actions">
    <?php echo link_to_back(get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('houses', 'index')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
</form>