<?php echo page_header($title, $subtitle); ?>
<div class="row">
<div class="span4">
  <img src="/images/error.jpg" class="img-polaroid">
  <br/><br/>
  <p>
<?php 
switch(rand(1, 4)) {
  case 1: echo '<i>Сайт, который ты ищешь,<br/>найти невозможно,<br/>но ведь не счесть других</i>'; break;
  case 2: echo '<i>Вы вошли в ручей,<br/>Но вода уже ушла.<br/>Этой страницы здесь нет.</i>'; break;
  case 3: echo '<i>Вчера он работал.<br/>Сегодня не работает.<br/>Таков Windows.</i>'; break;
  case 4: echo '<i>Твой файл был так велик<br/>и, должно быть, весьма полезен,<br/>но его больше нет.</i>'; break;
}
?>
  </p>
</div>
<div class="span8">
  <?php $curr_url = $_SERVER['REQUEST_URI']; ?>
  <?php $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null; ?>
  <?php if ($curr_url != '/404') { ?>
  <p>Запрошенный адрес <?php echo $curr_url; ?> не найден на сервере.</p>
  <?php } ?>
  <p><b>Что произошло?</b></p>
  <ul>
    <li>Eсли вы набрали адрес вручную, проверьте его.<br/>Учитывайте, что регистр букв может быть важен.</li>
    <li>Если вы нажали на ссылку, возможно она повреждена.<?php if ($back_url && $back_url != $curr_url) { ?><br/>Вы можете <a href='<?php echo $back_url ?>'>вернуться обратно</a> на страницу с ссылкой.<?php } ?></li>
    <li>Также вы можете вернуться <a href='/home'>на главную страницу</a>.</li>
  </ul>
</div>
</div>
