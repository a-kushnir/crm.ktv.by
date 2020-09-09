<?php
class RolesController extends ApplicationController
{
  function index()
  {
    check_access('roles');
    
    $this->roles = Role::all();

    $this->title = 'Роли';
    $this->primary_menu_create_url = url_for('roles', 'new');
    $this->load_attachments('Role');
  }

  function add()
  {
    check_access('role/edit');
    
    $this->title = 'Новая роль';
    $this->role = new Role();
  }

  function create()
  {
    check_access('role/edit');
    
    $this->role = new Role();
    $this->role->load_attributes($_POST['role']);
    if ($this->role->valid()) {
      $id = $this->role->create();
      flash_notice('Роль была создана');
      redirect_to(url_for('roles', 'show', $id));
    } else {
      $this->title = 'Новая роль';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('roles');

    $this->role = Role::first($this->id);
    if (!$this->role) show_404();
    
    $this->title = 'Роль';
    $this->subtitle = $this->role['name'];
    $this->load_attachments('Role', $this->id);
  }

  function edit()
  {
    check_access('role/edit');

    $this->role = Role::first($this->id);
    if (!$this->role) show_404();
    
    $this->title = 'Редактирование';
    $this->subtitle = $this->role['name'];
  }

  function update()
  {
    check_access('role/edit');
    
    $this->role = Role::first($this->id);
    if (!$this->role) show_404();
    
    $this->role = new Role();
    $this->role->load_attributes($_POST['role'], $this->id);
    if ($this->role->valid()) {
      $this->role->update();
      flash_notice('Роль была обновлена');
      redirect_to(url_for('roles', 'show', $this->id));
    } else {
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('role/edit');

    $this->role = Role::first($this->id);
    if (!$this->role) show_404();
    if (!$role->has_users()) {
      flash_notice('Роль была удалена');
      $this->factory->destroy('roles', $this->id);
    }
    redirect_to(url_for('roles'));
  }

  function rights()
  {
    check_access('role/edit');

    $this->role = Role::first($this->id);
    if (!$this->role) show_404();
    
    $this->capabilities = Role::all_capabilities($this->id); // they are sorted for current role
    $this->rights = Role::get_capability_ids($this->id);
    $this->menu_items = Role::get_menu_items($this->id);
    
    $this->title = 'Права';
    $this->subtitle = $this->role['name'];
  }
  
  function update_rights()
  {
    check_access('role/edit');

    $this->role = Role::first($this->id);
    if (!$this->role) show_404();

    $stat = $this->role->update_rights(get_field_value('capabilities'));
    $this->role->update_menu(get_field_value('menu_items'));
    flash_notice('Права сохранены (добавлено: '.$stat['inserted'].', удалено: '.$stat['deleted'].')');
    
    redirect_to(url_for('roles', 'rights', $this->id));
  }
  
  function switch_role()
  {
    check_access('role/edit');
    
    if(!isset($_SESSION['original_role_id']))
      $_SESSION['original_role_id'] = $_SESSION['role_id'];
    $_SESSION['role_id'] = $this->id;
    
    redirect_to('/home');
  }
  
  function restore_role()
  {
    if(isset($_SESSION['original_role_id'])) {
      $role_id = $_SESSION['role_id'];
      $_SESSION['role_id'] = $_SESSION['original_role_id'];
      unset($_SESSION['original_role_id']);
      redirect_to('/roles/'.$role_id.'/rights');
    } else {
      check_access('role/edit');
      redirect_to('/home');
    }
  }
}
?>