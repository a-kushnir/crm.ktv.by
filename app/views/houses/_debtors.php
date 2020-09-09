<?php
if (count($evil_subscribers) > 0) {
  $house_schema = House::generate_schema($house);
?>
<p><?php echo '<strong>'.count($evil_subscribers).'</strong> '.rus_word(count($evil_subscribers), 'должник', 'должника', 'должников') ?></p>
<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="min-width align-right">Кв.</th>
  <th class="min-width align-right">П.</th>
  <th class="min-width align-right">Э.</th>
  <th>ФИО</th>
  <th>Телефоны</th>
  <th class="align-center min-width">Договор</th>
  <th>Тариф</th>
  <th class="align-right min-width">Долг</th>
  </thead>
  <tbody>
<?php 
foreach($evil_subscribers as $sub) {
  $subscriber = Subscriber::load($sub['id']);
  $si = House::search_arartment($house_schema, $subscriber['apartment']);
  echo '<tr>'.
  '<td class="align-right">'.link_to('/subscribers/'.$subscriber['id'], $subscriber['apartment']).'</td>'.
  '<td nowrap class="align-right">'.$si['entrance'].'</td>'.
  '<td nowrap class="align-right">'.$si['floor'].'</td>'.
  '<td>'.format_name($subscriber).'</td>'.
  '<td>'.phone_with_icon($subscriber['cell_phone']).' '.phone_with_icon($subscriber['home_phone']).'</td>'.
  '<td class="align-center">'.$subscriber['lookup_code'].'</td>'.
  '<td>'.$subscriber['billing_tariff'].'</td>'.
  '<td class="align-right" nowrap>'.format_money(-$subscriber['actual_balance']).'</td>'.
  '</tr>';
}
?>
</tbody>
</table>
<?php } ?>