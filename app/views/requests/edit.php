<?php echo breadcrumb(array(
  'Заявки' => (get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('requests', 'index')), 
  format_address($request) => '/requests/'.$id.'?index='.get_field_value('index'),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('request'); 
  echo $form->begin_form('/requests/'.$id.'?index='.get_field_value('index'), array('class' => 'form-horizontal'));
  include '_form.php'
?>
<div class="form-actions">
  <?php echo link_to_back('/requests/'.$id.'?index='.get_field_value('index')) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>