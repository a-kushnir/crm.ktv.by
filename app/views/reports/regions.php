<link href="/stylesheets/jquery.treetable.css" rel="stylesheet" type="text/css" />
<style>table.treetable tbody tr td { padding-left: 1.5em; }</style>
<?php $javascripts[] = '/javascripts/jquery.treetable.js' ?>
<?php $javascripts[] = '<script type="text/javascript">$(document).ready(function(){$(".treetable").treeTable();});</script>'; ?>

<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

<?php
  $regions = Address::regions();
  $regions[] = array(
    'city_id' => null,
    'city_district_id' => null,
    'city_microdistrict_id' => null,
    'city' => null,
    'city_district' => null,
    'city_microdistrict' => null
  );
  $houses = House::regions();
  $subscribers = Subscriber::regions();
  
  $subscription_fee = 0;
  foreach ($subscribers as $subscriber)
    $subscription_fee += $subscriber['subscription_fee'];
?>

<table id="region_report" class="table table-bordered table-striped table-condensed treetable table-hover">
<thead>
<th></th>
<th class="align-center" colspan="3">Дома</th>
<th class="align-center" colspan="3">Абоненты</th>
<th class="align-center" colspan="3">Финансы</th>
</thead>
<thead>
<th>Название</th>
<th class="align-right">Онлайн</th>
<th class="align-right">Всего</th>
<th class="align-right">%</th>
<th class="align-right">Онлайн</th>
<th class="align-right">Всего</th>
<th class="align-right">%</th>
<th class="align-right">ARPU</th>
<th class="align-right">Всего</th>
<th class="align-right">%</th>
</thead>
<tbody>
<?php foreach($regions as $region) {

$row = array();
$row['name'] = "Итого";
if ($region['city']) $row['name'] = $region['city'];
if ($region['city_district']) $row['name'] = $region['city_district'];
if ($region['city_microdistrict']) $row['name'] = $region['city_microdistrict'];

$row['online_h'] = 0;
$row['total_h'] = 0;
$row['total_s'] = 0;
foreach ($houses as $house)
  if((is_null($region['city_id']) || $region['city_id'] == $house['city_id']) &&
     (is_null($region['city_district_id']) || $region['city_district_id'] == $house['city_district_id']) &&
     (is_null($region['city_microdistrict_id']) || $region['city_microdistrict_id'] == $house['city_microdistrict_id'])) {
    $row['online_h'] += $house['online_h'];
    $row['total_h'] += $house['total_h'];
    $row['total_s'] += $house['total_s'];
  }

$row['online_s'] = 0;  
$row['subscription_fee'] = 0; 
foreach ($subscribers as $subscriber)
  if((is_null($region['city_id']) || $region['city_id'] == $subscriber['city_id']) &&
     (is_null($region['city_district_id']) || $region['city_district_id'] == $subscriber['city_district_id']) &&
     (is_null($region['city_microdistrict_id']) || $region['city_microdistrict_id'] == $subscriber['city_microdistrict_id'])
    ) {
    $row['online_s'] += $subscriber['online_s'];
    $row['subscription_fee'] += $subscriber['subscription_fee'];
  }

$row['arpu'] = $row['online_s'] > 0 ? $row['subscription_fee'] / $row['online_s'] : 0;

$node_id = ($region['city_microdistrict_id'] ? 'cmd_'.$region['city_microdistrict_id'] : ($region['city_district_id'] ? 'cd_'.$region['city_district_id'] : 'c_'.$region['city_id']));
$parent_id = ($region['city_microdistrict_id'] ? 'cd_'.$region['city_district_id'] : ($region['city_district_id'] ? 'c_'.$region['city_id'] : null));

$row_tag = $region['city_id'] ? 'tr' : 'tfoot';
echo '<'.$row_tag.' id="'.$node_id.'"'.($parent_id ? ' class="child-of-'.$parent_id.'"' : null).'>
<td>'.$row['name'].'</td>
<td class="align-right">'.$row['online_h'].'</td>
<td class="align-right">'.$row['total_h'].'</td>
<td class="align-right">'.($row['total_h'] > 0 ? format_percent($row['online_h']/$row['total_h'], 0) : '').'</td>
<td class="align-right">'.$row['online_s'].'</td>
<td class="align-right">'.$row['total_s'].'</td>
<td class="align-right">'.($row['total_s'] > 0 ? format_percent($row['online_s']/$row['total_s'], 0) : '').'</td>
<td class="align-right">'.($row['arpu'] ? format_money($row['arpu']) : '').'</td>
<td class="align-right">'.($row['subscription_fee'] ? format_money($row['subscription_fee']) : '').'</td>
<td class="align-right">'.($row['subscription_fee'] ? format_percent($row['subscription_fee']/$subscription_fee, 0) : '').'</td>
</'.$row_tag.'>';
} ?>
</tbody>
</table>


</div>
</div>

