<?php 
  global $selected_region;
  $selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : array();
  
  $selected_region['cities'] = Address::get_cities();
  $selected_region['districts'] = isset($selected_region['city_id']) ? 
    Address::get_districts($selected_region['city_id']) : array();
  $selected_region['microdistricts'] = isset($selected_region['city_district_id']) ? 
    Address::get_microdistricts($selected_region['city_district_id']) : array();

  $form = new FormBuilder('selected_region', false);
  echo $form->begin_form('/address/set_region', array('id'=>'choose_location_form', 'class'=>'form-horizontal'));
?>
<div id="choose_location_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3>Выбрать регион</h3>
</div>
<div class="modal-body">
  <?php echo $form->select('city_id', 'Город', get_field_collection($selected_region['cities'], 'id', 'name', '- Все города -')); ?>
  <div class="choose_city_district_div">
  <?php if (count($selected_region['districts']) > 0) {  
    echo $form->select('city_district_id', 'Район', get_field_collection($selected_region['districts'], 'id', 'name', '- Все районы -')); ?>
    <div class="choose_city_microdistrict_div">
    <?php if (count($selected_region['microdistricts']) > 0)
      echo $form->select('city_microdistrict_id', 'Микрорайон', get_field_collection($selected_region['microdistricts'], 'id', 'name', '- Все микрорайоны -')); ?>
    </div>
  <?php } ?>
  </div>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Отменить</a>
<input id="submit_choosen_region" type="submit" class="btn btn-primary" value="Применить" onclick="return submitChoosenRegion();"></input>
</div>
</div>

<?php echo $form->end_form(); ?>