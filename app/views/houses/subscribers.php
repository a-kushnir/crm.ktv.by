<?php if (isset($house)) echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $id),
  'Абоненты' => null,
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<table class="table table-bordered table-striped table-condensed table-hover">
  <thead>
  <th class="min-width align-right">Кв.</th>
  <th class="min-width align-right">П.</th>
  <th class="min-width align-right">Э.</th>
  <th class="align-center">Тарифный план</th>
  <th class="align-center">Обоснование тарифа</th>
  <th class="min-width nobr">Завершение тарифа</th>
  </thead>
  <tbody>
<?php 
$house_schema = House::generate_schema($house);;
$entrance = null;
$floor = null;

foreach($subscribers as $subscriber) {
  $si = House::search_arartment($house_schema, $subscriber['apartment']);
 
  echo '<tr>'.
  '<td nowrap class="align-right">'.link_to('/subscribers/'.$subscriber['id'], $subscriber['apartment']).'</td>'.
  '<td nowrap class="align-right">'.$si['entrance'].'</td>'.
  '<td nowrap class="align-right">'.$si['floor'].'</td>'.
  '<td>'.format_tariff($subscriber->billing_tariff()).'</td>'.
  '<td>'.$subscriber['tariff_justification'].'</td>'.
  '<td>'.format_date($subscriber['tariff_ends_on']).'</td>'.
  '</tr>';
}
?>
  </tbody>
</table>

<div class="form-actions">
  <?php echo link_to_back(url_for('houses', 'show', $id)); ?>
  <a class="btn btn-primary btn-large" href="/houses/<?php echo $id; ?>/subscribers/edit">Редактировать</a>
</div>
