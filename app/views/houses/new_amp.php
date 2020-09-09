<?php echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $house['id']),
  'Усилители' => url_for('houses', 'amps', $house['id']),
  $title => null
)); ?>

<?php echo page_header($title, $subtitle); ?>


<form method="post" class="form-vertical">
<?php include '_amp.php' ?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('houses', 'amps', $house['id'])) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
</form>