<?php echo breadcrumb(array(
  'Данные' => '/data', 
  $title => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<fieldset>
<legend>Результаты операции</legend>
<ul>
<?php 
  foreach($cleanup_trace as $et)
    echo '<li>'.$et;
?>
</ul>
</fieldset>

<div class="form-actions">
  <?php echo link_to_back('/data'); ?>
</div>