<?php echo page_header($title, $subtitle); ?>
<div class="row">
<div class="span4">
  <img src="/images/error.jpg" class="img-polaroid">
  <br/><br/>
  <p>
<?php 
switch(rand(1, 5)) {
  case 1: echo '<i>Хаос царит в системе:</br>подумай, раскайся и перезагрузись,</br>порядок должен вернуться</i>'; break;
  case 2: echo '<i>Три вещи вечны:</br>смерть, налоги и потеря данных</br>догадайся, что случилось</i>'; break;
  case 3: echo '<i>Серьезная ошибка.</br>Все связи потеряны.</br>Экран. Ум. Все пусто.</i>'; break;
  case 4: echo '<i>Поломка превращает</br>Ваш дорогой компьютер</br>В простой камень.</i>'; break;
  case 5: echo '<i>Вчера он работал.</br>Сегодня не работает.</br>Таков Windows.</i>'; break;
}
?>
</div>
<div class="span8">
  <?php $curr_url = $_SERVER['REQUEST_URI']; ?>
  <?php $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null; ?>
  <?php if ($curr_url != '/500') { ?>
    <p>При обработке адреса <?php echo $curr_url; ?> произошла внутренняя ошибка сервера.</p>
  <?php } else { ?>
    <p>Произошла внутренняя ошибка сервера.</p>
  <?php } ?>
  
  <p><b>Что делать?</b></p>
  <ul>
    <li>Перейти на <a href='/home'>главную страницу</a></li>
    <?php if ($back_url && $back_url != $curr_url) { ?><li><a href='<?php echo $back_url ?>'>Вернуться обратно</a></li><?php } ?>
  </ul>
</div>
</div>