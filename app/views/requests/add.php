<?php $javascripts[] = '/javascripts/requests.js'; ?>

<?php echo breadcrumb(array(
  'Заявки' => (get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('requests', 'index')), 
  'Новая заявка' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('request');
  echo $form->begin_form('/requests?index='.get_field_value('index'), array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class='form-actions'>
    <?php echo link_to_back(get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('requests', 'index')) ?>
    <?php echo submit_button('Создать'); ?>
  </div>
<?php 
  echo $form->end_form();
?>
