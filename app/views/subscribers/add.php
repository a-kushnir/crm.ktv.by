<?php echo breadcrumb(array(
  'Абоненты' => url_for('subscribers'), 
  'Новый абонент' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('subscriber');
  echo $form->begin_form(url_for('subscribers'), array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('subscribers')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php 
  echo $form->end_form();
?>