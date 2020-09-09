<?php echo breadcrumb(array(
  'Работники' => url_for('workers'), 
  'Новый работник' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('worker');
  echo $form->begin_form(url_for('workers'), array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('workers')) ?>
    <?php echo submit_button("Нанять"); ?>
  </div>
<?php 
  echo $form->end_form();
?>