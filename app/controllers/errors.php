<?php
class ErrorsController extends ApplicationController
{
  var $suppress_authorization = true;

  function show_403() {
    if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403); }
    $this->title = 'Ошибка 403';
    $this->subtitle = 'Доступ запрещен';
    render_action('403');
  }
  
  function show_404() {
    if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404); }
    $this->title = 'Ошибка 404';
    $this->subtitle = 'Страница не найдена';
    render_action('404');
  }
  
  function show_500() {
    if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500); }
    $this->title = 'Ошибка 500';
    $this->subtitle = 'Внутренняя ошибка сервера';
    render_action('500');
  }
}
?>