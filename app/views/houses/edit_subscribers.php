<?php if (isset($house)) echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => url_for('houses', 'show', $id),
  'Абоненты' => null,
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
  <?php echo print_version_button(); ?>
</div>

<?php echo page_header($title, $subtitle); ?>

<form method="post">
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
$house_schema = House::generate_schema($house);
$billing_tariffs = BillingTariff::load(null, $house['city_id'], has_access('subscriber/all_tariffs'));

foreach($house_schema as $entrance => $floors)
  foreach($floors as $floor => $apartments)
    foreach($apartments as $apartment) {

  $subscriber = null;
  foreach($subscribers as $s)
    if ($s['apartment'] == $apartment) {
      $subscriber = $s;
      break;
    }
    
  $si = House::search_arartment($house_schema, $subscriber['apartment']);
  $disabled = $subscriber && $house['billing_account_id'] != $subscriber['billing_account_id'];
  echo '<tr>'.
  '<td nowrap class="align-right align-middle">'.($subscriber ? link_to('/subscribers/'.$subscriber['id'], $apartment) : $apartment).'</td>'.
  '<td nowrap class="align-right align-middle">'.($entrance+1).'</td>'.
  '<td nowrap class="align-right align-middle">'.($floor+1).'</td>'.
  '<td class="align-center">'.select_tag('subscribers['.$apartment.'][billing_tariff_id]', null, get_field_collection($billing_tariffs, 'id', 'name', ''), array('value' => $subscriber['billing_tariff_id'], 'disabled' => $disabled)).'</td>'.
  '<td class="align-center">'.text_field_tag('subscribers['.$apartment.'][tariff_justification]', null, array('value' => $subscriber['tariff_justification'], 'class' => 'span4', 'disabled' => $disabled)).'</td>'.
  '<td class="align-center">'.date_field_tag('subscribers['.$apartment.'][tariff_ends_on]', null, array('value' => $subscriber['tariff_ends_on'], 'disabled' => $disabled)).'</td>'.
  '</tr>';
    
    }
?>
  </tbody>
</table>

<div class="form-actions">
  <?php echo link_to_back(url_for('houses', 'subscribers', $id)); ?>
  <?php echo submit_button('Сохранить'); ?>
</div>
</form>
