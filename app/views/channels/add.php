<?php $javascripts[] = '/javascripts/timesheet.js' ?>

<script type="text/javascript" src="/javascripts/channels.js"></script>

<?php echo breadcrumb(array(
  'Каналы' => url_for('channels'), 
  'Новый канал' => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('channel');
  echo $form->begin_form(url_for('channels'), array('class' => 'form-horizontal'));
  include '_form.php';
?>
  <div class="form-actions">
    <?php echo link_to_back(url_for('channels')) ?>
    <?php echo submit_button("Создать"); ?>
  </div>
<?php 
  echo $form->end_form();
?>