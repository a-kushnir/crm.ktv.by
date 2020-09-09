<?php

function redirect_to($url) {
  header('Location: '.$url);
  exit;
}

function render_action($action) {
  global $render_action;
  $render_action = $action;
}

function url_for($model, $action = 'index', $id = null, $params = null) {
  $url = null;
  if ($action == 'index' || $action == 'create')
    $url = '/'.$model;
  else if ($action == 'show' || $action == 'update')
    $url = '/'.$model.'/'.$id;
  else if ($action == 'new')
    $url = '/'.$model.'/'.$action;
  else if ($action == 'edit' || $action == 'destroy' || $id != null)
    $url = '/'.$model.'/'.$id.'/'.$action;
  else
    $url = '/'.$model.'/'.$action;
  
  if ($params)
    $url .= $params;
  
  return $url;
}

function link_to($url, $text, $html_options = null) {
  return "<a href='".$url."' ".tag_it($html_options).">".$text."</a>";
}

function link_to_index($model, $text = 'Список') {
  return "<a href='".url_for($model, 'index')."' class='icon icon-index'>".$text."</a>";
}

function link_to_new($model, $text = 'Добавить') {
  return "<a href='".url_for($model, 'new')."' class='btn btn-large btn-success'>".$text."</a>";
}

function link_to_show($model, $id, $text = 'Просмотр') {
  return "<a href='".url_for($model, 'show', $id)."'>".$text."</a>";
}

function link_to_edit($model, $id, $text = 'Редактировать') {
  return "<a href='".url_for($model, 'edit', $id)."' rel='nofollow' class='btn btn-large btn-primary'>".$text."</a>";
}

function link_to_destroy($model, $id, $text = 'Удалить') {
  return "<a href='".url_for($model, 'destroy', $id)."' rel='nofollow' data-method='delete' class='btn btn-large btn-danger' data-confirm='Вы уверены?'>".$text."</a>";
}

function link_to_back($url, $text = 'Назад') {
  return link_to($url, $text, array('class' => 'btn btn-large'));
}

function breadcrumb($items) {
  $html = '';

  $count = count($items);
  if ($count > 0) 
  {
    $div = "<span class='divider'>&gt;</span>";
    $html .= "<ul class='breadcrumb visible-html'>";
    
    $index = 0;
    foreach($items as $name => $url) {
      $index += 1;
      $html.= ($url == '' || $url == null) ? 
        "<li>".$name.($index < $count ? $div : '')."</li>" :
        "<li><a href='".$url."'>".$name."</a>".($index < $count ? $div : '')."</li>";
    }
    $html.= "</ul>";
  }

  return $html;
}

function pagination($records, $page_size, $current_page = null, $params = null, $inside_window = 3, $outside_window = 2) {
  if ($current_page == null) $current_page = 1;
  $pages = ceil($records / $page_size);
  if ($pages > 1) {
    $html = '<div class="pagination pagination-centered">';
    $html.= '<ul>';
    
    $locked = false;
    for($i=1; $i<=$pages; $i++){
      $css_class = $i == $current_page ? 'active' : '';
      
      if ($i > $outside_window && $pages - $i >= $outside_window && abs($current_page - $i) > $inside_window) {
        if (!$locked) $html.= '<li class="disabled"><a href="#">...</a></li>';
        $locked = true;
      } else {
        if ($i == 1) $html.= '<li class="'.$css_class.'"><a href="?page='.($current_page-1).'">←<span class="hidden-phone"> Назад</span></a></li>';
        $html.= '<li class="'.$css_class.'"><a href="?page='.$i.$params.'">'.$i.'</a></li>';
        if ($i == $pages) $html.= '<li class="'.$css_class.'"><a href="?page='.($current_page+1).'"><span class="hidden-phone">Вперед </span>→</a></li>';
        $locked = false;
      }
    }
    
    $html.= '</ul>';
    $html.= '</div>';
    
    return $html;
  } else {
    return '';
  }  
}

?>