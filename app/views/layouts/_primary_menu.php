<?php 
function render_primary_menu_item($link, $text) {
  global $primary_menu_item, $controller;
  $selected_class = (isset($primary_menu_item) && $primary_menu_item == $link) || (!isset($primary_menu_item) && starts_with($link, '/'.$controller)) ? 'active' : '';
  return '<li class="'.$selected_class.'"><a href="'.$link.'">'.$text.'</a></li>';
}
?>

<ul class="nav">
<?php if (isset($_SESSION['user_id'])) {
  
  $has_other = false;
  $_role_menu_items = Role::get_menu($_SESSION['role_id']);
  foreach($_role_menu_items as $_role_menu_item)
    if (!$_role_menu_item['hide_in_other'])
      echo render_primary_menu_item($_role_menu_item['url'], $_role_menu_item['name']);
    else
      $has_other = true;
  
if (!$mobile_version && $has_other) {
echo '<li class="dropdown">
<a id="drop3" class="dropdown-toggle" data-toggle="dropdown" role="button" href="#">
Прочее <b class="caret"></b>
</a>'.
'<ul class="dropdown-menu" role="menu">';
}

  foreach($_role_menu_items as $_role_menu_item)
    if ($_role_menu_item['hide_in_other'])
      echo render_primary_menu_item($_role_menu_item['url'], $_role_menu_item['name']);

if (!$mobile_version && $has_other) {
echo '</ul>
</li>';
}

} 

if (!$mobile_version) { ?>
</ul>
<ul class="nav pull-right">
<?php }

if (isset($_SESSION['user_id'])) { 

if (isset($_SESSION['original_role_id']))
  echo '<li>'.link_to(url_for('roles', 'restore'), '<strong>Вернуться</strong>').'</li>';

if (!$mobile_version) {
echo '<li class="dropdown">
<a id="drop3" class="dropdown-toggle" data-toggle="dropdown" role="button" href="#">
<i class="icon-user"></i> '.$_SESSION['user_name'].' <b class="caret"></b>
</a>'.
'<ul class="dropdown-menu" role="menu">';
}
echo '<li><a href="#" onclick="chooseRegion();" id="region_name_menu_item"><!--i class="icon-globe"></i--> '.(isset($_SESSION['selected_region']) ? $_SESSION['selected_region']['name'] : 'Мой регион').'</a></li>';
echo render_primary_menu_item('/profile', '<!--i class="icon-lock"></i0--> Мой пароль');
echo render_primary_menu_item('/help', '<!--i class="icon-question-sign"></i--> Помощь');
echo '<li class="divider"></li>';
echo render_primary_menu_item('/sign_out', '<!--i class="icon-off"></i--> Выход');
if (!$mobile_version) {
echo '</ul>
</li>';
}
  
} else {
  echo render_primary_menu_item('/sign_in', '<i class="icon-user"></i> Войти');
} ?>
</ul>

