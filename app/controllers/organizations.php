<?php
class OrganizationsController extends ApplicationController
{

  function index()
  {
    check_access('organizations');

    $this->title = 'Организации';    

    $this->filter = trim(get_field_value('filter'));
    $this->organizations = Organization::load(null, $this->filter, get_field_value('page'), Organization::$page_size);
    $this->records = Organization::records($this->filter);
  }

  function add()
  {
    check_access('organization/edit');
    $this->title = 'Новая организация';
  }

  function create()
  {
    check_access('organization/edit');

    $this->organization = new Organization();
    $this->organization->load_attributes($_POST['organization']);
    
    if ($this->organization->valid()) {
      $this->id = $this->organization->create();
      User::log_event('добавил организацию', $this->organization['name'], url_for('organizations','show',$this->organization['id']));

      flash_notice('Организация была добавлена');
      redirect_to(url_for('organizations'));
    } else {
      $this->title = 'Новая организация';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('organizations');

    $this->organization = Organization::load($this->id);
    if (!$this->organization) show_404();
    
    if (!$this->organization['active']) flash_alert('Огранизация была удалена. Для восстановления обратитесь к администратору.');
    
    $this->title = 'Организация';
    $this->subtitle = $this->organization['name'];
    $this->load_attachments('Organization', $this->id);
  }

  function edit()
  {
    check_access('organization/edit');

    $this->organization = Organization::load($this->id);
    
    $this->title = 'Редактирование';
    $this->subtitle = $this->organization['name'];
  }
  
  function update()
  {
    check_access('organization/edit');

    $this->organization = new Organization();
    $this->organization->load_attributes($_POST['organization'], $this->id);
    
    if ($this->organization->valid()) {
      $this->organization->update();
      
      $this->organization = Organization::load($this->organization['id']);
      User::log_event('отредактировал организацию', $this->organization['name'], url_for('organizations','show',$this->organization['id']));
      flash_notice('Организация был обновлена');
      
      redirect_to(url_for('organizations', 'show', $this->id));
    } else {
      $this->title = 'Редактирование';
      $this->subtitle = $this->organization['name'];
      render_action('edit');
    }
  }

  function destroy()
  {
    check_access('organization/destroy');

    if ($_POST)
    {
      $organization = Organization::load($this->id);
      $this->factory->deactivate('organizations', $this->id);
    
      User::log_event('удалил организацию', $organization['name'], url_for('organizations','show',$organization['id']));
      flash_notice('Организация был удалена');
    }
    redirect_to(url_for('organizations', 'index'));
  }
}
?>