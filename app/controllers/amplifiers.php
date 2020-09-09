<?php
class AmplifiersController extends ApplicationController
{
  function index()
  {
    check_access('house/show_amps');

    $this->house = House::load($this->id);
    $this->amplifiers = Amplifier::all($this->id);

    $this->title = 'Усилители';
    if (has_access('house/edit_amps')) $this->primary_menu_create_url = url_for('amplifiers/'.$this->id.'/new');
    $this->load_attachments('Amplifier');
  }

  function add()
  {
    check_access('house/edit_amps');
    
    $this->house = House::load($this->id);
    $this->amplifier = new Amplifier();
    
    $this->title = 'Новый усилитель';
  }

  function create()
  {
    check_access('house/edit_amps');
    
    $this->amplifier = new Amplifier();
    $this->amplifier->load_attributes($_POST['amplifier'], null, $this->id);
    if ($this->amplifier->valid()) {
      $id = $this->amplifier->create();
      flash_notice('Усилитель был создан');
      redirect_to(url_for('amplifiers', 'show', $id));
    } else {
      $this->title = 'Новый усилитель';
      $this->house = House::load($this->id);
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('house/show_amps');

    $this->amplifier = Amplifier::first($this->id);
    if (!$this->amplifier) show_404();
    $this->house = House::load($this->amplifier['house_id']);
  
    $this->title = 'Усилитель';
    $this->subtitle = $this->amplifier['name'];
    $this->load_attachments('Amplifier', $this->id);
  }

  function edit()
  {
    check_access('house/edit_amps');

    $this->amplifier = Amplifier::first($this->id);
    if (!$this->amplifier) show_404();
    $this->house = House::load($this->amplifier['house_id']);

    $this->title = 'Усилитель';
    $this->subtitle = $this->amplifier['name'];
  }

  function update()
  {
    check_access('house/edit_amps');
    
    $this->amplifier = Amplifier::first($this->id);
    if (!$this->amplifier) show_404();
    
    $this->amplifier = new Amplifier();
    $this->amplifier->load_attributes($_POST['amplifier'], $this->id);
    if ($this->amplifier->valid()) {
      $this->amplifier->update();
      flash_notice('Усилитель был обновлен');
      redirect_to(url_for('amplifiers', 'show', $this->id));
    } else {
      $h = Amplifier::first($this->id);
      $this->house = House::load($h['house_id']);
      $this->title = 'Усилитель';
      $this->subtitle = $this->amplifier['name'];
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('house/destroy_amps');

    $amplifier = Amplifier::load($this->id);
    if (!$this->amplifier) show_404();
    if (!$amplifier->has_subscribers()) {
      flash_notice('Усилитель был удален');
      $this->factory->destroy('amplifiers', $this->id);
    }
    redirect_to(url_for('amplifiers'));
  }
  
  function upload_scans()
  {
    check_access('house/upload_amp_scans');
  
    $house = House::load($this->id);
    if (!$house) show_404();
  
    if (isset($_FILES['scan_file'])) {
      $result = Amplifier::import_scan_files($_FILES['scan_file'], $house['id']);
      flash_notice($result['success'].' '.rus_word($result['success'], 'скан загружен', 'скана загружено', 'сканов загружено'));
      if (count($result['errors']) > 0) flash_alert('При загрузке возникли следующие ошибки: <ul><li>'.implode('<li>', $result['errors']).'</ul>');
    }
    redirect_to('/houses/'.$house['id'].'/amplifiers');
  }
}
?>