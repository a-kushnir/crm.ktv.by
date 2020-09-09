<?php
class SettingsController extends ApplicationController
{
  private $lookup_codes = array(
    'template_sms',
    'tax_on_turnover',
    'monitoring_hours',
    'monitoring_cell',
    'monitoring_sms',
    'fix_billing_file_comment',
    'change_tariff_call_comment',
    'termination_call_comment',
    'arrear_termination_comment',
  );

  function index()
  {
    check_access('settings');
    
    $this->settings = array();
    foreach($this->lookup_codes as $lookup_code)
      $this->settings[$lookup_code] = Config::get($lookup_code);
    
    $this->title = 'Настройки';
    $this->load_attachments('Settings');
  }

  function create()
  {
    check_access('settings');
    
    if (isset($_POST['settings'])) {
      $settings = $_POST['settings'];
      
      foreach($this->lookup_codes as $lookup_code) {
          $value = $settings[$lookup_code];
          
          if ($lookup_code == 'monitoring_cell')
            $value = prepare_phone($value);
          
          if (Config::get($lookup_code) != $value)
            Config::set($lookup_code, $value);
        }
    }
    
    flash_notice('Настройки успешно сохранены');
    redirect_to(url_for('settings', 'index'));
  }
}
?>