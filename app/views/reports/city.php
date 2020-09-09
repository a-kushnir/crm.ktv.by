<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

<?php
$subscribers_total = Subscriber::total($id);
$apartments_online = Subscriber::online_apartments($id);
$apartments_total = Subscriber::total_apartments($id);

$houses_online = House::online_count($id);
$houses_total = House::total_count($id);

$s1 = Subscriber::new_subscribers(1, $id);
$s7 = Subscriber::new_subscribers(7, $id);
$s14 = Subscriber::new_subscribers(14, $id);
$s30 = Subscriber::new_subscribers(30, $id);
$s1s = Subscriber::new_subscribers_stats(1, $id);
$s7s = Subscriber::new_subscribers_stats(7, $id);
$s30s = Subscriber::new_subscribers_stats(30, $id);

$estimate_w = $s7 ? ($apartments_total - $subscribers_total) / $s7 * 7 : 0;
$estimate_m = $s14 ? ($apartments_total - $subscribers_total) / $s14 * 14 : 0;
$estimate_p = $estimate_w > 0 ? ($estimate_m > $estimate_w ? ($estimate_m/$estimate_w-1) :
  ($estimate_w > $estimate_m ? (1-$estimate_w/$estimate_m) : 0)) : 0;

$new_r1      = Request::request_stats('count',   'new',    $id, 1);
$new_r7      = Request::request_stats('count',   'new',    $id, 7);
$new_r30     = Request::request_stats('count',   'new',    $id, 30);
$new_r1s     = Request::request_stats('details', 'new',    $id, 1);
$new_r7s     = Request::request_stats('details', 'new',    $id, 7);
$new_r30s    = Request::request_stats('details', 'new',    $id, 30);
$closed_r1   = Request::request_stats('count',   'closed', $id, 1);
$closed_r7   = Request::request_stats('count',   'closed', $id, 7);
$closed_r30  = Request::request_stats('count',   'closed', $id, 30);
$closed_r1s  = Request::request_stats('details', 'closed', $id, 1);
$closed_r7s  = Request::request_stats('details', 'closed', $id, 7);
$closed_r30s = Request::request_stats('details', 'closed', $id, 30);
$online_rq   = Request::request_stats('quality', 'online', $id);
$closed_rq   = Request::request_stats('quality', 'closed', $id, 7);
$online_rqs  = Request::request_stats('details', 'online', $id);
$closed_rqs  = Request::request_stats('details', 'closed', $id, 7);
?>
<!--h3>Покрытие</h3-->
<!--p style="margin-bottom:3px;">Абонентов / Все дома : <?php echo $subscribers_total; ?> / <?php echo $apartments_total; ?></p>
<?php //echo render_progress($apartments_total ? $subscribers_total/$apartments_total : 0) ?>
<p style="margin-bottom:3px;margin-top:-12px;">Абонентов / Онлайн дома : <?php echo $subscribers_total; ?> / <?php echo $apartments_online; ?></p>
<?php //echo render_progress($apartments_online ? $subscribers_total/$apartments_online : 0) ?>
<p style="margin-bottom:3px;margin-top:-12px;">Онлайн дома / Все дома : <?php echo $houses_online; ?> / <?php echo $houses_total; ?></p>
<?php //echo render_progress($houses_online ? $houses_online/$houses_total : 0) ?>
<p style="margin-top:-12px;">Будет готово <b><?php //echo $datetime = date(DATE_FORMAT, strtotime('+ '.(int)$estimate_w.' days')); ?></b> (через <b><?php //echo format_float($estimate_w); ?></b> дн)</p-->

<h3>Договоров заключено</h3>
<p>День / Неделя / Месяц / Всего : <?php echo format_subscribers_stats('Договоров за сегодня', $s1, $s1s); ?> / <?php echo format_subscribers_stats('Договоров за неделю', $s7, $s7s); ?> / <?php echo format_subscribers_stats('Договоров за месяц', $s30, $s30s); ?>  / <?php echo $subscribers_total ?> <small class="nobr"><?php echo arrow_image($estimate_p).' '.($estimate_p > 0 ? '+'.format_percent($estimate_p) : ($estimate_p < 0 ? format_percent($estimate_p) : '')); ?></small></p>
<h3>Заявок принято</h3>
<p>День / Неделя / Месяц : <?php echo format_requests_stats('Заявок за сегодня', $new_r1, $new_r1s); ?> / <?php echo format_requests_stats('Заявок за неделю', $new_r7, $new_r7s); ?> / <?php echo format_requests_stats('Заявок за месяц', $new_r30, $new_r30s); ?></p>
<h3>Заявок выполнено</h3>
<p>День / Неделя / Месяц : <?php echo format_requests_stats('Заявок за сегодня', $closed_r1, $closed_r1s); ?> / <?php echo format_requests_stats('Заявок за неделю', $closed_r7, $closed_r7s); ?> / <?php echo format_requests_stats('Заявок за месяц', $closed_r30, $closed_r30s); ?></p>
<h3>Открытые заявки <small>дома онлайн</small></h3>
<p>Кол-во / Среднее / Макс : <?php echo format_requests_stats('Открытые заявки', $online_rq['cnt'], $online_rqs); ?> / <?php echo format_float($online_rq['avg'], 2); ?> дн / <?php echo (int)$online_rq['max']; ?> дн</p>
<h3>Закрытые заявки <small>за 7 дней</small></h3>
<p>Кол-во / Среднее / Макс : <?php echo format_requests_stats('Закрытые заявки', $closed_rq['cnt'], $closed_rqs); ?> / <?php echo format_float($closed_rq['avg'], 2); ?> дн / <?php echo (int)$closed_rq['max']; ?> дн</p>
</div>
</div>