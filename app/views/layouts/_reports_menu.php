<?php 
$rendered_reports_menu_item = 0;
function render_reports_menu_item($link, $text) {
  global $rendered_reports_menu_item;
  $rendered_reports_menu_item ++;
  
  $selected_class = starts_with($_SERVER["REQUEST_URI"], $link) ? 'active' : '';
  return '<li class="'.$selected_class.'"><a href="'.$link.'"> <i class="icon-chevron-right"></i> '.$text.'</a>';
}

function render_reports_menu_divider() {
  global $rendered_reports_menu_item;
  $result = '';
  if ($rendered_reports_menu_item > 1) $result = '<li class="divider"></li>';
  $rendered_reports_menu_item = 0;
  return $result;
}

?>
<?php if ($layout != 'print') { ?>
<ul class="nav nav-list bs-docs-sidenav affix_menu">
<?php
if (has_access('report/city')) {
  $cities = Address::get_cities();
  foreach($cities as $city)
    echo render_reports_menu_item('/reports/'.$city['id'].'/city', $city['name']);
}
  if (has_access('report/regions')) echo render_reports_menu_item('/reports/regions', 'Регионы');
  if (has_access('report/subscribers')) echo render_reports_menu_item('/reports/subscribers', 'Абоненты');
  if (has_access('report/competitors')) echo render_reports_menu_item('/reports/competitors', 'Конкуренты');
  echo render_reports_menu_divider();
  if (has_access('report/request_creator')) echo render_reports_menu_item('/requests/report_creator', 'Принятые заявки');
  if (has_access('report/request_updator')) echo render_reports_menu_item('/requests/report_updator', 'Закрытые заявки');
  if (has_access('report/request_worker')) echo render_reports_menu_item('/requests/report_worker', 'Выполненные заявки');
  echo render_reports_menu_divider();
  if (has_access('report/events')) echo render_reports_menu_item('/reports/events', 'События');
  if (has_access('report/timesheet')) echo render_reports_menu_item('/timesheet/report', 'Табель');
  if (has_access('report/video')) echo render_reports_menu_item('/reports/video', 'Видео');
  echo render_reports_menu_divider();
  if (has_access('report/billing')) echo render_reports_menu_item('/reports/billing', 'Биллинг');
  if (has_access('report/revenue')) echo render_reports_menu_item('/billing/report', 'Поступления');
  if (has_access('report/arrears')) echo render_reports_menu_item('/arrears/report', 'Должники');
}
?>
</ul>