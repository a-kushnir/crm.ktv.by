<div class="row">
<div class="span3">
<?php require APP_ROOT.'/app/views/layouts/_reports_menu.php'; ?>
</div>
<div class="span9">
<?php echo page_header($title, $subtitle); ?>

<table class="table table-bordered table-striped table-condensed table-hover">
<thead>
<th>Организация</th>
<th class="align-right">Дома</th>
<th class="align-right">Аб. Емкость</th>
<th class="align-right">Всего</th>
<th class="align-right">Ушли от нас</th>
<th class="align-right">Пришли к нам</th>
<th class="align-right">Ушли и вернулись</th>
</thead>
<tbody>
<?php foreach($houses as $house) {
$subscriber = null;
foreach ($subscribers as $s)
  if($s['competitor_id'] == $house['competitor_id'])
    $subscriber = $s;
echo '<tr>
<td>'.$house['name'].'</td>
<td class="align-right">'.$house['houses'].'</td>
<td class="align-right">'.$house['apartments'].'</td>
<td class="align-right">'.$subscriber['total'].'</td>
<td class="align-right">'.$subscriber['lost'].'</td>
<td class="align-right">'.$subscriber['won'].'</td>
<td class="align-right">'.$subscriber['returned'].'</td>
</tr>';
} ?>
</tbody>
</table>


</div>
</div>

