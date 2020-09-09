<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo page_header($title, $subtitle); ?>

<?php
  $form = new FormBuilder('settings');
  echo $form->begin_form(url_for('settings'), array('class' => 'form-horizontal'));
?>
<fieldset>
<legend>Биллинг</legend>
<?php
$hint = 'Доступные теги: {first_name} - имя, {last_name} - фамилия, {middle_name} - отчество,<br>
{value} - абонентская плата, {actual_balance} - баланс, {period} - месяц, {date} - дата начисления';
echo $form->text_area('template_sms', 'Шаблон сообщения', array('hint' => $hint), array('class' => 'input-xxlarge','rows' => '2')); ?>
<?php
$hint = 'Десятичная дробь, например: 0.05 для 5%';
echo $form->text('tax_on_turnover', 'Налог на оборот', array('hint' => $hint)); ?>
</fieldset>

<fieldset>
<legend>Система мониторинга</legend>
<?php $hint = 'Два числа, разделитель - дефис, например 9-21 обозначает 9:00-20:59';
echo $form->text('monitoring_hours', 'Режим работы', array('hint' => $hint), array('class' => 'input-small')); ?>
<?php echo $form->phone('monitoring_cell', 'Мобильный телефон'); ?>
<?php $hint = 'Доступные теги: {code} - код канала, {name} - название, {frequency} - частота, {station} - город,<br>
{datetime} - дата и время, {date} - дата, {time} - время';
echo $form->text_area('monitoring_sms', 'Шаблон сообщения', array('hint' => $hint), array('class' => 'input-xxlarge','rows' => '2')); ?>
</fieldset>

<fieldset>
<legend>Заявки</legend>
<?php echo $form->text_area('fix_billing_file_comment', 'Подключение', null, array('class' => 'input-xxlarge','rows' => '2')); ?>
<?php echo $form->text_area('change_tariff_call_comment', 'Смена тарифа', null, array('class' => 'input-xxlarge','rows' => '2')); ?>
<?php echo $form->text_area('termination_call_comment', 'Отключение должника', array('hint' => 'Для генерации заявки во время телефонного разговора с оператором'), array('class' => 'input-xxlarge','rows' => '2')); ?>
<?php echo $form->text_area('arrear_termination_comment', 'Отключение должника', array('hint' => 'Для генерации заявки в остальных случаях'), array('class' => 'input-xxlarge','rows' => '2')); ?>
</fieldset>

<div class='form-actions'>
  <?php echo submit_button('Сохранить') ?>
</div>

<?php
  echo $form->end_form();
?>
