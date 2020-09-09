<?php echo breadcrumb(array(
  'Тарифы' => url_for('tariffs'), 
  'Новый тариф' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('billing_tariff');
  echo $form->begin_form(url_for('tariffs', 'create'), array('class' => 'form-horizontal'));
  include '_form.php'
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('tariffs')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php
  echo $form->end_form();
?>