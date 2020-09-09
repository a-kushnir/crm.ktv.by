<?php 
if ($id) {
echo breadcrumb(array(
  'Абоненты' => url_for('subscribers'), 
  format_name($subscriber) => url_for('subscribers', 'show', $id),
  'Отправка сообщения' => null
));
} ?>

<?php if ($id == null || $sms_available) { ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('sms');
  echo $form->begin_form(url_for('subscribers', 'notify', $id), array('class' => 'form-horizontal'));
  echo $form->read_only(null, 'Кому', null, array('value' => $id ? format_name($subscriber) : $count.' абонентов'));
  echo $form->text_area('message', 'Текст', null, array('rows' => '4'));
?>
  <div class='form-actions'>
    <?php echo link_to_back(url_for('subscribers')) ?>
    <?php echo submit_button('Отправить', array('data-confirm' => 'Вы уверены что хотите отправить сообщение?
Это действие не может быть отменено!')) ?>
  </div>
<?php
  echo $form->end_form();
?>

<?php } else { ?>
  <div class='alert'>Невозможно отправить сообщение</div>
<?php } ?>