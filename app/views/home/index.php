<?php if (has_access('home/warnings')) { ?>

<?php
$alerts = array();

$requests = Request::high_priority_requests();
if (count($requests) > 0) {
  $message = array();
  $house_ids = array();
  foreach($requests as $request) {
    $house_ids[] = $request['house_id'];
    $message[] = format_address($request, true);
  }
  $message[] = '<a href="/requests?house=selection&type=&include=&selection='.implode(',', $house_ids).'">просмотр</a>';
  $alerts[] = alert_block('error', 'Аварии', implode('<br>', $message));
}


$birth_dates = Worker::birth_dates();
if (count($birth_dates) > 0) {
  $message = array();
  foreach($birth_dates as $birth_date) {
    $m = ($birth_date['days'] == 0 ? 'Сегодня' : ($birth_date['days'] == 1 ? 'Завтра' : 'Через '.rus_days($birth_date['days'])));
    $m.= ' <a href="/workers/'.$birth_date['id'].'">'.format_name($birth_date).'</a>';
    $m.= ' отмечает свое '.$birth_date['years'].'-летие ';
    $message[] = $m;
  }
  $alerts[] = alert_block('info', 'Дни рождения', implode('<br>', $message));
}

$lost_connections = Channel::lost_connections();
if (count($lost_connections)) {
  foreach($lost_connections as $lost_connection) {
    $alerts[] = alert_block('error', 'Потеряно соединение с '.$lost_connection['name'], 'Последний пинг был '.time_since($lost_connection['seconds_ago']));
  }
}

$broken_channels = Channel::broken_channels();
if (count($broken_channels)) {
  $station = $broken_channels[0]['station'];

  $channels_text = '';
  $has_enabled = false;
  foreach($broken_channels as $broken_channel) {
    if ($station != $broken_channel['station']) {
      $alerts[] = alert_block($has_enabled ? 'error' : 'info', 'Сигнал отсутствует в '.$station, $channels_text.'<a href="/channels/'.$broken_channel['station_id'].'/forget_known">удалить сообщение</a>');
      $station = $broken_channel['station'];
      $channels_text = '';
      $has_enabled = false;
    }
    
    $has_enabled = $has_enabled || $broken_channel['enabled'];
    $channels_text .= $broken_channel['channel_code'].' ('.format_float($broken_channel['frequency'], 2).' МГц) '.$broken_channel['name'].' '.($broken_channel['enabled'] ? '' : ' / Отключен').' <small>'.time_since($broken_channel['seconds_ago']).'</small><br/>';
  }
  if ($channels_text) $alerts[] = alert_block($has_enabled ? 'error' : 'info', 'Сигнал отсутствует в '.$station, $channels_text.'<a href="/channels/'.$broken_channel['station_id'].'/forget_known">удалить сообщение</a>');
}

$invalid_channels = Channel::invalid_channels();
if (count($invalid_channels)) {
  $station = $invalid_channels[0]['station'];

  $channels_text = 'Проблемы на следующих частотах: ';
  $channels = array();
  foreach($invalid_channels as $invalid_channel) {
    if ($station != $invalid_channel['station']) {
      $alerts[] = alert_block('error', 'Неизвестные частоты в '.$station, $channels_text.implode(', ', $channels).', <a href="/channels/'.$invalid_channel['station_id'].'/forget_unknown">удалить сообщение</a>');
      $station = $invalid_channel['station'];
      $channels = array();
    }
    
    $channels[] = format_float($invalid_channel['frequency'], 2).' МГц';
  }
  if ($channels_text) $alerts[] = alert_block('error', 'Неизвестные частоты в '.$station, $channels_text.implode(', ', $channels).', <a href="/channels/'.$invalid_channels[count($invalid_channels)-1]['station_id'].'/forget_unknown">удалить сообщение</a>');
}

$curr_hour18 = (integer)date('G') >= 18;
if ($curr_hour18) {
  $hours_1day = TimeEntry::hours_added(1);
  if ($hours_1day == 0) $alerts[] = alert_block('error', 'Табель не заполнен', 'Тaбeль за сегодня не был заполнен, <a href="/timesheet/new">исправить</a>');
}

$sms = TimeEntry::hours_added(1);

$billing_period = BillingPeriod::get_last_period_info();

$currday = (integer)date('d');
$actual_date = strtotime(date("Y-m-d", strtotime($billing_period['actual_date']))."+1 month");
if ($actual_date <= time()) {
  $alerts[] = alert_block($currday >= 2 ? 'error' : 'info', 'Абонентская плата', 'Абонентская плата за прошлый месяц не начислена, <a href="/billing">исправить</a>');
}
if (!$billing_period['sms_sent_at']) {
  $alerts[] = alert_block($currday >= 7 ? 'error' : 'info', 'Абонентская плата', 'Сообщения за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date']."-1 month"))).' не сформированы, <a href="/billing/'.$billing_period['id'].'/period_details">исправить</a>');
}
if ($billing_period['client_cards_downloads'] == 0) {
  $alerts[] = alert_block($currday >= 7 ? 'error' : 'info', 'Абонентская плата', 'Памятки абонентов за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date']."-1 month"))).' не распечатаны, <a href="/billing/'.$billing_period['id'].'/period_details">исправить</a>');
}

$sms_stats = Sms::stats('subs');
if ($sms_stats['cnt'] > 0 && $sms_stats['max'] > 1) 
  $alerts[] = alert_block('error', 'Абонентские сообщения', $sms_stats['cnt'].' сообщений в очереди '.rus_days((int)$sms_stats['max']).', <a href="/messages/destroy?for=subs" data-method="delete" data-confirm="Удалить все неотправленные АБОНЕНТСКИЕ сообщения?
ВНИМАНИЕ! Это действие невозможно отменить.">удалить все</a>');
else if ($sms_stats['cnt'] > 0)
  $alerts[] = alert_block('info', 'Абонентские сообщения', $sms_stats['cnt'].' сообщений в очереди '.rus_days((int)$sms_stats['max']));

$sms_stats = Sms::stats('admin');
if ($sms_stats['cnt'] > 0 && $sms_stats['max'] > 1) 
  $alerts[] = alert_block('error', 'Служебные сообщения', $sms_stats['cnt'].' сообщений в очереди '.rus_days((int)$sms_stats['max']).', <a href="/messages/destroy?for=admin" data-method="delete" data-confirm="Удалить все неотправленные СЛУЖЕБНЫЕ сообщения?
ВНИМАНИЕ! Это действие невозможно отменить.">удалить все</a>');
else if ($sms_stats['cnt'] > 0)
  $alerts[] = alert_block('info', 'Служебные сообщения', $sms_stats['cnt'].' сообщений в очереди '.rus_days((int)$sms_stats['max']));

$bf_stats = BillingFile::stats();
if ($bf_stats['max'] > 3)
  $alerts[] = alert_block('error', 'Поступления', 'Платежи не поступали '.rus_days((int)$bf_stats['max']).', <a href="/billing">перейти</a>');
else if ($bf_stats['max'] > 2)
  $alerts[] = alert_block('info', 'Поступления', 'Платежи не поступали '.rus_days((int)$bf_stats['max']).', <a href="/billing">перейти</a>');

if ($bf_stats['err'])
  $alerts[] = alert_block('error', 'Поступления', 'Произошел сбой во время обработки платежей, <a href="/billing">перейти</a>');

$terminatios = Subscriber::count_terminations();
if ($terminatios['req_ends'] > 0 || $terminatios['sub_ends'] > 0) {
  $alerts[] = alert_block('info', 'Отключения', 'Расторгнуто договоров: '.$terminatios['sub_ends'].', и подано заявок на отключение: '.$terminatios['req_ends'].', <a href="/reports/subscribers">отчет</a>');
}
  
$last_call = Call::days_from_last_call();
if ($last_call == 0) {
  $today_calls = Call::today_calls();
  if ($today_calls > 0)
    $alerts[] = alert_block('info', 'Должники', 'Было сделано звонков: '.$today_calls.', <a href="/arrears/report">отчет</a>');
} else if ($last_call >= 15) {
  $alerts[] = alert_block($last_call >= 30 ? 'error' : 'info', 'Должники', 'Обзвонка не производилась '.rus_days($last_call));
}
  
$cities = Address::get_cities();
foreach($cities as $city) {
  $online_rq = Request::request_stats('quality', 'online', $city['id']);
  if ($online_rq['avg'] > 7 || $online_rq['max'] > 30) {
    $alerts[] = alert_block('error', 'Заявки в '.$city['name'], $online_rq['cnt'].' заявок в среднем '.rus_days(format_float($online_rq['avg'], 2)).', максимум '.rus_days((int)$online_rq['max']));
  } else if ($online_rq['avg'] > 3 || $online_rq['max'] > 15) {
    $alerts[] = alert_block('info', 'Заявки в '.$city['name'], $online_rq['cnt'].' заявок в среднем '.rus_days(format_float($online_rq['avg'], 2)).', максимум '.rus_days((int)$online_rq['max']));
  }
  
  $offine_rq = Request::request_stats('quality', 'offline', $city['id']);
  if ($offine_rq['cnt'] > 0) $alerts[] = alert_block('info', 'Заявки в '.$city['name'].' (дома офлайн)', $offine_rq['cnt'].' заявок в среднем '.rus_days(format_float($offine_rq['avg'], 2)).', максимум '.rus_days((int)$offine_rq['max']));
  
  if ($curr_hour18) {
    $s1 = Subscriber::new_subscribers(1, $city['id']);
    $new_r1 = Request::request_stats('count', 'new', $city['id'], 1);
    $closed_r1 = Request::request_stats('count', 'closed', $city['id'], 1);
    if ($s1 == 0) $alerts[] = alert_block('', 'Договора в '.$city['name'], 'Ни одного договора не было введено за сегодня');
    if ($new_r1 == 0) $alerts[] = alert_block('', 'Принятые заявки в '.$city['name'], 'Ни одной заявки не было принято за сегодня');
    if ($closed_r1 == 0) $alerts[] = alert_block('', 'Закрытые заявки в '.$city['name'], 'Ни одного заявки не было закрыто за сегодня');
  }
}

db_session_reset_old();
$sessions_info = db_session_show_info();

?>
<div class="row">

<div class="span8">
<?php foreach($alerts as $alert) { if (strrpos($alert, 'alert-error"') > 0) echo $alert; } ?>
<?php foreach($alerts as $alert) { if (strrpos($alert, 'alert-info"') > 0) echo $alert; } ?>
<?php foreach($alerts as $alert) { if (strrpos($alert, 'alert-"') > 0) echo $alert; } ?>
</div>

<div class="span4">
<p></p>
<address>
  <strong>Наш офис</strong><br>
  г. Брест, ул. Мичурина, 52<br>
  Будние с 10:00 до 19:00<br>
  Суббота с 11:00 до 18:00<br>
</address>
<address>
  <strong>Наши контакты</strong><br>
  <?php echo phone_with_icon('+375333636565'); ?><br>
  <?php echo phone_with_icon('+375259324004'); ?><br>
  <?php echo phone_with_icon('+375162243596'); ?><br>
</address>

<strong>Активные Сессии</strong>
<p><?php 
$sessions = array();
foreach($sessions_info as $session_info) {
  $ua = getBrowser($session_info['user_agent']);
  $sessions[] = "<img src='/images/browsers/".strtolower($ua['code']).".png' title='".$ua['name'].' '.$ua['version']."'> <img src='/images/browsers/".$ua['platform'].".png'> ".$session_info['name'].' <small>'.human_datetime($session_info['created_at']).' - '.human_datetime($session_info['updated_at']).' @ '.$session_info['ip_address'].'</small>';
}
  echo join($sessions, '<br>');
?></p>

</div>
</div>

<?php };
if (has_access('home/stats')) { ?>

<div class="row">
<div class="span6">
<div class="well">
<?php echo show_section('info'); ?>
<?php
$s1 = Subscriber::new_subscribers(1, 2);
$s7 = Subscriber::new_subscribers(7, 2);
$s7 = (int)(($s7 - $s1) * 0.66 + $s1);

$new_requests_1day      = Request::request_stats('count',   'new',    2, 1);
$new_requests_7day      = Request::request_stats('count',   'new',    2, 7);
$closed_requests_1day   = Request::request_stats('count',   'closed', 2, 1);
$closed_requests_7day   = Request::request_stats('count',   'closed', 2, 7);
$online_stats           = Request::request_stats('quality', 'online', 2);
$closed_stats           = Request::request_stats('quality', 'closed', 2, 7);

$hours_1day = TimeEntry::hours_added(1);
$hours_7day = TimeEntry::hours_added(7);
?>
<div id="info" style="display:none;">
  <h3>Договоров заключено</h3>
  <p>День / Неделя : <?php echo $s1; ?> / <?php echo $s7; ?></p>
  <h3>Заявок принято</h3>
  <p>День / Неделя : <?php echo $new_requests_1day; ?> / <?php echo $new_requests_7day; ?></p>
  <h3>Заявок выполнено</h3>
  <p>День / Неделя : <?php echo $closed_requests_1day; ?> / <?php echo $closed_requests_7day; ?></p>
  <h3>Открытые заявки <small>дома онлайн</small></h3>
  <p>Кол-во / Среднее / Макс : <?php echo $online_stats['cnt']; ?> / <?php echo format_float($online_stats['avg'], 2); ?> дн / <?php echo (int)$online_stats['max']; ?> дн</p>
  <h3>Закрытые заявки <small>за 7 дней</small></h3>
  <p>Кол-во / Среднее / Макс : <?php echo $closed_stats['cnt']; ?> / <?php echo format_float($closed_stats['avg'], 2); ?> дн / <?php echo (int)$closed_stats['max']; ?> дн</p>
</div>
  <h3>Часов отработано</h3>
  <p>День / Неделя : <?php echo format_float($hours_1day, 2); ?> / <?php echo format_float($hours_7day, 2); ?> <?php echo($hours_1day == 0 ? '<span class="badge badge-important">Табель не заполнен</span>' : '') ?></p>
</div>
</div>

<div class="span6">
<div class="well">
  <?php echo show_section('video'); ?>
  <h2>Видео</h2>
<div id="video" style="display:none;">
  <?php $cameras = User::cameras(); ?>
  <div class="align-center">
  <select id="ipcam_url">
    <option value="">&lt; Выберите камеру &gt;</option>
    <?php foreach($cameras as $camera) { ?>
    <option value="<?php echo $camera['url'] ?>"><?php echo $camera['name'] ?></option>
    <?php } ?>
  </select>
  </div>
  <div id="ipcam_wnd" class="align-center no-signal">
    <img class="img-polaroid2" src="/images/no-signal.gif" />
  </div>
  <div id="ipcam_time" class="align-center"></div>
</div>
</div>
</div>
</div>


<?php };

if (!has_access('home/warnings', 'home/stats')) { ?>

<div class="row">
<div class="span8">
<div class="hero-unit">
<h2>ООО «ТелеСпутник»</h2>
<p class="lead">Оператор кабельного телевидения</p>
</div>
</div>

<div class="span4">
<p></p>
<address>
  <strong>Наш офис</strong><br>
  г. Брест, ул. Мичурина, 52<br>
  Будние с 10:00 до 19:00<br>
  Суббота с 11:00 до 18:00<br>
</address>
<address>
  <strong>Наши контакты</strong><br>
  <?php echo phone_with_icon('+375333636565'); ?><br>
  <?php echo phone_with_icon('+375259324004'); ?><br>
  <?php echo phone_with_icon('+375162243596'); ?><br>
</address>
<?php
  $worker_id = Worker::worker_id_for_user($_SESSION['user_id']);
  $new_s = Subscriber::new_subscribers(1, null, $_SESSION['user_id']);
  $new_r = Request::request_stats('count', 'new', null, 1, $_SESSION['user_id']);
  $cls_r = Request::request_stats('count', 'closed', null, 1, $_SESSION['user_id']);
  $hdl_r = $worker_id ? 
           Request::request_stats('count', 'handled', null, 1, $worker_id) : 0;
?>
<?php if ($new_s > 0 || $new_r > 0 || $cls_r > 0 || $hdl_r > 0) { ?>
<address>
<strong>Мои достижения</strong><br>
<?php
if ($new_s > 0) echo '<strong>'.$new_s.'</strong> '.rus_word($new_s, 'договор внесен', 'договора внесено', 'договоров внесено').'<br>';
if ($new_r > 0) echo '<strong>'.$new_r.'</strong> '.rus_word($new_r, 'заявка принята', 'заявки принято', 'заявок принято').'<br>';
if ($hdl_r > 0) echo '<strong>'.$hdl_r.'</strong> '.rus_word($hdl_r, 'заявка принята', 'заявки принято', 'заявок принято').'<br>';
if ($cls_r > 0) echo '<strong>'.$cls_r.'</strong> '.rus_word($cls_r, 'заявка принята', 'заявки принято', 'заявок принято').'<br>';
?>
</address>
<?php } ?>
</div>
</div>

<?php } ?>