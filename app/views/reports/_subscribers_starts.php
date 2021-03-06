<link href="/stylesheets/jquery.treetable.css" rel="stylesheet" type="text/css" />
<?php $javascripts[] = '/javascripts/jquery.treetable.js'; ?>
<style>
table.treetable tbody tr td { padding-left: 1.5em; }
</style>
<script type="text/javascript">
$(document).ready(function()  {
  $(".treetable").treeTable();
});
</script>

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
?>

<table id="region_report" class="table table-bordered table-striped table-condensed treetable table-hover">
<thead>
<th></th>
<th class="align-center" colspan="2">Подключения</th>
</thead>
<thead>
<th>Адрес</th>
<th class="align-right">Заявки</th>
<th class="align-right">Абоненты</th>
</thead>
<tbody>
<?php foreach($regions as $region) {

$row = array();
$row['name'] = "Итого";
if ($region['city']) $row['name'] = $region['city'];
if ($region['city_district']) $row['name'] = $region['city_district'];
if ($region['city_microdistrict']) $row['name'] = $region['city_microdistrict'];

$rows = array();

$row['subs'] = 0;  
$row['reqs'] = 0;  
foreach ($subscribers as $subscriber) {
  if((is_null($region['city_id']) || $region['city_id'] == $subscriber['city_id']) &&
     (is_null($region['city_district_id']) || $region['city_district_id'] == $subscriber['city_district_id']) &&
     (is_null($region['city_microdistrict_id']) || $region['city_microdistrict_id'] == $subscriber['city_microdistrict_id'])
    ) {
    $row['subs'] += $subscriber['subs'];
    $row['reqs'] += $subscriber['reqs'];
  }
  if($region['city_id'] == $subscriber['city_id'] &&
     $region['city_district_id'] == $subscriber['city_district_id'] &&
     $region['city_microdistrict_id'] == $subscriber['city_microdistrict_id']
    ) {
    
    $last_idx = count($rows)-1;
    if ($last_idx < 0 || $rows[$last_idx]['house_id'] != $subscriber['house_id']) {
      $rows[] = $subscriber;
    } else {
      $rows[$last_idx]['subs'] += $subscriber['subs'];
      $rows[$last_idx]['reqs'] += $subscriber['reqs'];
    }
      
  }  
}

$node_id = ($region['city_microdistrict_id'] ? 'cmd_'.$region['city_microdistrict_id'] : ($region['city_district_id'] ? 'cd_'.$region['city_district_id'] : 'c_'.$region['city_id']));
$parent_id = ($region['city_microdistrict_id'] ? 'cd_'.$region['city_district_id'] : ($region['city_district_id'] ? 'c_'.$region['city_id'] : null));

$row_tag = $region['city_id'] ? 'tr' : 'tfoot';

if ($row['subs'] + $row['reqs'] > 0) { echo '<'.$row_tag.' id="'.$node_id.'"'.($parent_id ? ' class="child-of-'.$parent_id.'"' : null).'>
<td><strong>'.$row['name'].'</strong></td>
<td class="align-right"><strong>'.($row['reqs'] > 0 ? $row['reqs'] : '').'</strong></td>
<td class="align-right"><strong>'.($row['subs'] > 0 ? $row['subs'] : '').'</strong></td>
</'.$row_tag.'>';

  foreach($rows as $r) {
    echo '<tr class="child-of-'.$node_id.'">
<td>'.format_address($r).'</td>
<td class="align-right">'.($r['reqs'] > 0 ? $r['reqs'] : '').'</td>
<td class="align-right">'.($r['subs'] > 0 ? $r['subs'] : '').'</td>
</tr>';
  }

 }
} ?>
</tbody>
</table>
