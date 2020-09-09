<?php echo page_header($title, $subtitle); ?>

<?php
  $form = new FormBuilder('user');
  echo $form->begin_form(url_for('profile'), array('class' => 'form-horizontal'));
  echo $form->password('old_password', 'Прежний пароль', array('required' => true), array('autocomplete' => 'off'));
  echo $form->password('new_password', 'Новый пароль', array('required' => true), array('autocomplete' => 'off'));
  echo $form->password('password_confirm', 'Подтверждение', array('required' => true), array('autocomplete' => 'off'));
?>
<div class='form-actions'>
  <?php echo submit_button('Сохранить') ?>
</div>
<?php
  echo $form->end_form();
?>

