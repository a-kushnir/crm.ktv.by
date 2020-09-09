<?php echo breadcrumb(array(
  'Абоненты' => url_for('subscribers'), 
  $subscriber->name() => url_for('subscribers', 'show', $id),
  'Редактирование' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('subscriber');
  echo $form->begin_form(url_for('subscribers', 'update', $id), array('class' => 'form-horizontal'));
  include '_form.php';
?>
<div class="form-actions">
  <?php echo link_to_back(url_for('subscribers', 'show', $id)) ?>
  <?php echo submit_button("Сохранить"); ?>
</div>
<?php 
  echo $form->end_form();
?>