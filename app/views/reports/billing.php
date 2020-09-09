<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

<?php
$ba_flow = BillingAccount::flow();
$ba_positive = BillingAccount::sum_positive();
$ba_negative = BillingAccount::sum_negative();
?>
<h3>Баланс</h3>
<p><small><?php echo format_money($ba_negative); ?></small> + <small><?php echo format_money($ba_positive); ?></small> = <small><?php echo format_money($ba_negative + $ba_positive); ?></small></p>
<h3>Поток</h3>
<p>День / Неделя / Месяц : <small><?php echo format_money($ba_flow/30); ?></small> / <small><?php echo format_money($ba_flow/4); ?></small> / <small><?php echo format_money($ba_flow); ?></small></p> 
<?php
$sum_and_count1 = BillingDetail::new_payments_sum_and_count(1, 1);
$sum_and_count7 = BillingDetail::new_payments_sum_and_count(7, 1);
$sum_and_count30 = BillingDetail::new_payments_sum_and_count(30, 1);
?>
<h3>Подключение</h3>
<p>День / Неделя / Месяц :
<?php echo $sum_and_count1['count'].' <small>'.format_money($sum_and_count1['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count7['count'].' <small>'.format_money($sum_and_count7['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count30['count'].' <small>'.format_money($sum_and_count30['sum']).'</small>' ?></p>

<?php
$sum_and_count1 = BillingDetail::new_payments_sum_and_count(1, 3, 'manual');
$sum_and_count7 = BillingDetail::new_payments_sum_and_count(7, 3, 'manual');
$sum_and_count30 = BillingDetail::new_payments_sum_and_count(30, 3, 'manual');
?>
<h3>Пополнение (Человек)</h3>
<p>День / Неделя / Месяц :
<?php echo $sum_and_count1['count'].' <small>'.format_money($sum_and_count1['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count7['count'].' <small>'.format_money($sum_and_count7['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count30['count'].' <small>'.format_money($sum_and_count30['sum']).'</small>' ?></p>

<?php
$sum_and_count1 = BillingDetail::new_payments_sum_and_count(1, 3, 'auto');
$sum_and_count7 = BillingDetail::new_payments_sum_and_count(7, 3, 'auto');
$sum_and_count30 = BillingDetail::new_payments_sum_and_count(30, 3, 'auto');
?>
<h3>Пополнение (Робот)</h3>
<p>День / Неделя / Месяц :
<?php echo $sum_and_count1['count'].' <small>'.format_money($sum_and_count1['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count7['count'].' <small>'.format_money($sum_and_count7['sum']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count30['count'].' <small>'.format_money($sum_and_count30['sum']).'</small>' ?></p>

<?php
$sum_and_count1 = BillingDetail::new_payments_sum_and_count(1, 4, 'all', 'plus_and_minus');
$sum_and_count7 = BillingDetail::new_payments_sum_and_count(7, 4, 'all', 'plus_and_minus');
$sum_and_count30 = BillingDetail::new_payments_sum_and_count(30, 4, 'all', 'plus_and_minus');
?>
<h3>Корректировка</h3>
<p>День / Неделя / Месяц :
<?php echo $sum_and_count1['count'].' <small>+'.format_money($sum_and_count1['sum_plus']).' -'.format_money(-$sum_and_count1['sum_minus']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count7['count'].' <small>+'.format_money($sum_and_count7['sum_plus']).' -'.format_money(-$sum_and_count7['sum_minus']).'</small>' ?>&nbsp;
/ <?php echo $sum_and_count30['count'].' <small>+'.format_money($sum_and_count30['sum_plus']).' -'.format_money(-$sum_and_count30['sum_minus']).'</small>' ?></p>
</div>

</div>

