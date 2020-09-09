<?php
  $billing_file_log_id = get_object_value($_GET, 'billing_file_log_id'); 
  $has_open_request = false;
  if (isset($requests))
    foreach($requests as $r)
      if (!$r['handled_on']) {
        $has_open_request = true;
        break;
      }
?>
<?php if (isset($requests) && count($requests) > 0) { ?>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th>Принята</th>
  <th>Тип</th>
  <th>Подробности</th>
  <th>Закрыта</th>
  <th>Исполнитель</th>
  <th>Отчет</th>
<?php if ($billing_file_log_id && $has_open_request) { ?>
  <th style="width:1px;">Действия</th>
<?php } ?>
  </thead>
<?php 
$house_id = null;
$house_schema = null;
$entrance = null;
$floor = null;

foreach($requests as $r) {
  echo '<tr>'.
  '<td nowrap>'.link_to(url_for('requests', $r['id']), human_date($r['created_at'])).'</td>'.
  '<td nowrap>'.$r['request_type'].'</td>'.
  '<td>'.$r['comment'].'</td>'.
  '<td nowrap>'.human_date($r['handled_on']).'</td>'.
  '<td nowrap>'.$r['worker'].'</td>'.
  '<td>'.$r['handled_comment'].'</td>'.
  ($billing_file_log_id && $has_open_request ? '<td>'.
    ($billing_file_log_id && !$r['handled_on'] ? '<a href="/billing/'.$billing_file_log_id.'/assign_request?request='.$r['id'].'" class="btn btn-success btn-mini" data-method="post">Назначить</a>' : null).'</td>' : null
  ).
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>