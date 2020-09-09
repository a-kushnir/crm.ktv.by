<?php
class TariffsController extends ApplicationController
{
  function index()
  {
    check_access('tariffs');

    $this->title = 'Тарифы';
    $this->billing_tariffs = BillingTariff::load(null, null, true);
  }

  function add()
  {
    check_access('tariff/edit');

    $this->title = 'Новый тариф';
  }

  function create()
  {
    check_access('tariff/edit');
    
    $this->billing_tariff = new BillingTariff();
    $this->billing_tariff->load_attributes($_POST['billing_tariff']);
    
    if ($this->billing_tariff->valid()) {
      $id = $this->billing_tariff->create();
      User::log_event('добавил тариф', $this->billing_tariff['name'], url_for('tariffs','show',$this->billing_tariff['id']));

      flash_notice('Тариф был создан');
      redirect_to(url_for('tariffs', 'new'));
    }
    else
    {
      $this->title = 'Новый тариф';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('tariffs');

    $this->billing_tariff = BillingTariff::load($this->id);
    if (!$this->billing_tariff) show_404();
    
    if (!$this->billing_tariff['active']) flash_alert('Тариф был удален. Для восстановления обратитесь к администратору.');
    
    $this->title = 'Тариф';
    $this->subtitle = $this->billing_tariff['name'];
    $this->load_attachments('BillingTariff', $this->id);
  }

  function edit()
  {
    check_access('tariff/edit');

    $this->billing_tariff = BillingTariff::load($this->id);
    if (!$this->billing_tariff) show_404();
    
    $this->title = 'Редактирование';
    $this->subtitle = $this->billing_tariff['name'];
  }

  function update()
  {
    check_access('tariff/edit');
    
    $this->billing_tariff = new BillingTariff();
    $this->billing_tariff->load_attributes($_POST['billing_tariff'], $this->id);
    
    if ($this->billing_tariff->valid()) {
      $this->billing_tariff->update();
      
      $this->billing_tariff = BillingTariff::load($this->billing_tariff['id']);
      User::log_event('отредактировал тариф', $this->billing_tariff['name'], url_for('tariffs','show',$this->billing_tariff['id']));
      flash_notice('Тариф был обновлен');
      
      redirect_to(url_for('tariffs', 'show', $this->id));
    } else {
      $this->title = 'Редактирование';
      $this->subtitle = $this->billing_tariff['name'];
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('tariff/destroy');

    if ($_POST)
    {
      $this->billing_tariff = BillingTariff::load($this->id);
      if (!$this->billing_tariff) show_404();
      
      $this->factory->deactivate('billing_tariffs', $this->id);
      
      User::log_event('удалил тариф', $this->billing_tariff['name'], url_for('tariffs','show',$this->billing_tariff['id']));
      flash_notice('Тариф был удален');
    }
    redirect_to(url_for('tariffs', 'index'));
  }
}
?>