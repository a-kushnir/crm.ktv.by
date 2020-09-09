<?php
class AddressController extends ApplicationController
{
  function districts()
  {
    $this->districts = Address::get_districts($this->id);
  }

  function microdistricts()
  {
    $this->microdistricts = Address::get_microdistricts($this->id);
  }

  function streets()
  {
    $this->streets = Address::get_streets($this->id, isset($_GET['all']));
  }

  function houses()
  {
    $this->houses = Address::get_houses($this->id);
  }

  function tariffs()
  {
    $this->billing_tariffs = BillingTariff::load(null, $this->id, has_access('subscriber/all_tariffs'));
    
    foreach($this->billing_tariffs as $billing_tariff)
      if ($billing_tariff['default']) {
        $this->billing_tariff_id = $billing_tariff['id'];
        break;
      }
  }

  function set_region()
  {
    if ($_POST) {
      if (!isset($_POST['selected_region'])) unset($_SESSION['selected_region']);
      else $_SESSION['selected_region'] = $_POST['selected_region'];
      
      User::normalize_selected_region();
      User::save_selected_region();
    }
  }

}
?>