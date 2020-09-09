<?php echo breadcrumb(array(
  'Тарифы' => url_for('tariffs'), 
  $billing_tariff['name'] => url_for('tariffs', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('billing_tariff');
  echo $form->begin_form(url_for('tariffs', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php'
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('tariffs', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php
  echo $form->end_form();
?>