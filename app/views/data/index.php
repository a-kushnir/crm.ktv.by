<?php echo page_header($title, $subtitle); ?>

<fieldset>
<legend>Экспорт данных</legend>
<p><strong>Получатель:</strong> <?php echo defined('DS_URL') ? DS_URL : 'не настроен в /config/datasync.php' ?></p>
<div class="alert alert-error"><strong>Предупреждение:</strong> Это может занять длительное время</div>
<a href='/data/export' class='btn btn-large btn-primary'>Запустить</a>
</fieldset>
<br>
<br>
<fieldset>
<legend>Очистка данных</legend>
<div class="alert alert-error"><strong>Предупреждение:</strong> Это может занять длительное время</div>
<a href='/data/cleanup' class='btn btn-large btn-primary'>Запустить</a>
</fieldset>
<br>