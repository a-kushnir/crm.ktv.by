<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('billing_detail');
  echo $form->begin_form(url_for('billing', 'new_account_detail', $id), array('class' => 'form-horizontal'), false);

  echo $form->read_only('owner', 'Владелец счета', null, array('value' => $subscriber->name()));
  echo $form->read_only('address', 'Адрес', null, array('value' => format_address($subscriber)));
  echo $form->read_only('billing_tariff', 'Тарифный план', null, array('value' => format_tariff($subscriber->billing_tariff())));
  echo $form->read_only('actual_balance', 'Баланс', null, array('value' => format_money($billing_account['actual_balance'])));

  echo $form->money('value', 'Сумма', array('required' => true), array('class' => 'input-small'));
  echo $form->date('actual_date', 'Дата', array('required' => true));
  echo $form->select('billing_detail_type_id', 'Тип операции', get_field_collection(BillingDetail::manual_types(), 'id', 'name', ''), array('required' => true));
  echo $form->text('comment', 'Пометка', array('required' => true, 'hint'=>'Пополнение счета, Бесплатное подключение и т.д.'));
?>
<div class='form-actions'>
  <?php echo link_to('/billing/'.$id.'/account_details', 'Назад', array('class' => 'btn btn-large')); ?>
  <?php echo submit_button('Добавить'); ?>
</div>
<?php 
  echo $form->end_form();
?>

