<?php echo page_header($title, $subtitle); ?>
<div class="row">
<div class="span4">
  <img src="/images/error.jpg" class="img-polaroid">
  <br/><br/>
  <p>
<?php 
switch(rand(1, 4)) {
  case 1: echo '<i>Три вещи вечны:</br>смерть, налоги и потеря данных</br>догадайся, что случилось</i>'; break;
  case 2: echo '<i>Серьезная ошибка.</br>Все связи потеряны.</br>Экран. Ум. Все пусто.</i>'; break;
  case 3: echo '<i>Вчера он работал.</br>Сегодня не работает.</br>Таков Windows.</i>'; break;
  case 4: echo '<i>Отказ в попытке.</br>Закройте все.</br>Вы просите слишком многого.</i>'; break;
}
?>
  </p>
</div>
<div class="span8">
  <?php $curr_url = $_SERVER['REQUEST_URI']; ?>
  <?php $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null; ?>
  <?php if ($curr_url != '/403') { ?>
  <p>Запрошенный адрес <?php echo $curr_url; ?> требует дополнительных прав.</p>
  <?php } ?>
  <p><b>Что произошло?</b></p>
  <ul>
    <li>Eсли вы набрали адрес вручную, проверьте его.<br/>Учитывайте, что регистр букв может быть важен.</li>
    <li>Если вы нажали на ссылку, возможно она повреждена.<?php if ($back_url && $back_url != $curr_url) { ?><br/>Вы можете <a href='<?php echo $back_url ?>'>вернуться обратно</a> на страницу с ссылкой.<?php } ?></li>
    <li>Также вы можете вернуться <a href='/home'>на главную страницу</a>.</li>
    <li>Или <a href='/sign_in'>войти</a> за другого пользователя.</li>
  </ul>
</div>
</div>