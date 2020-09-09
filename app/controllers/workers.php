<?php
class WorkersController extends ApplicationController
{
  function index()
  {
    check_access('workers');

    $this->title = 'Работники';
    if (has_access('worker/new')) $this->primary_menu_create_url = url_for('workers', 'new');

    $this->workers = Worker::load(null, get_field_value('filter'), get_field_value('page'), Worker::$page_size);
    $this->records = Worker::records(get_field_value('filter'));
  }

  function add()
  {
    check_access('worker/new');
    $this->title = 'Новый работник';
    
    $this->worker = new Worker();
    $this->user = $this->worker; // copies attributes
  }

  function create()
  {
    check_access('worker/new');

    $this->worker = new Worker();
    $this->worker->load_attributes($_POST['worker']);

    if (has_access('worker/user')){
      $this->user = new User();
      $this->user->load_attributes_for_worker($_POST['user'], $this->worker);
    }
    
    if ($this->worker->valid() && (!isset($this->user) || !$this->user || $this->user->valid_for_worker())) {
      if (isset($this->user) && $this->user && $this->user['login']) {
        $this->worker['user_id'] = $this->user->create();
        $this->user->update_password();
      }
      $id = $this->worker->create();
        
      $this->worker = Worker::load($id);
      if (!$this->worker) show_404();
      
      User::log_event('нанял работника', format_name($this->worker), url_for('workers','show',$id));
      
      flash_notice('Работник был нанят');
      redirect_to(url_for('workers', 'show', $id));
    } else {
      $this->title = 'Новый работник';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('worker/show');

    $this->worker = Worker::load($this->id);
    if (!$this->worker) show_404();
    
    $this->user = $this->worker; // copies attributes
    if (!$this->worker['active']) flash_alert('Работник был уволен. Для восстановления обратитесь к администратору.');
    
    $this->title = 'Работник';
    $this->subtitle = format_name($this->worker);
    $this->load_attachments('Worker', $this->id);
  }

  function edit()
  {
    check_access('worker/edit');
    
    $this->worker = Worker::load($this->id);
    if (!$this->worker) show_404();
    
    $this->user = $this->worker; // copies attributes
    
    $this->title = 'Редактирование';
    $this->subtitle = format_name($this->worker);
  }

  function update()
  {
    check_access('worker/edit');

    $this->worker = new Worker();
    $this->worker->load_attributes($_POST['worker'], $this->id);
    $w = Worker::load($this->id);
    $this->worker['user_id'] = $w['user_id'];
    
    if (has_access('worker/user')) {
      $this->user = new User();
      $this->user->load_attributes_for_worker($_POST['user'], $this->worker);
    }
      
    if ($this->worker->valid() && (!isset($this->user) || !$this->user || $this->user->valid_for_worker())) {
      if (isset($this->user) && $this->user) {
        if ($this->user['id']) {
          $this->user->update();
          $this->user->update_password();
          
          // Reset session if password is changed or user is disabled
          if (!$this->user['enabled'] || $this->new_password)
            db_session_reset_user($this->user['id']);
            
        } else if ($this->user['login']) {
          $this->worker['user_id'] = $this->user->create();
          $this->user->update_password();
        }
      }
      $this->worker->update();
      
      $this->worker = Worker::load($this->id);
      User::log_event('отредактировал работника', format_name($this->worker), url_for('workers','show',$this->id));
      flash_notice('Работник был обновлен');
      
      redirect_to(url_for('workers', 'show', $this->id));
    } else {
      $this->title = 'Редактирование';
      $this->subtitle = format_name($this->worker);
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('worker/destroy');

    if ($_POST)
    {
      $worker = Worker::load($this->id);
      if (!$worker) show_404();
      
      $this->factory->deactivate('workers', $this->id);
    
      if ($worker['user_id'])
        $this->factory->update('users', $worker['user_id'], array('disabled_at' => date(MYSQL_TIME, time())));
    
      User::log_event('уволил работника', format_name($worker), url_for('workers','show',$this->id));
      flash_notice('Работник был уволен');
    }
    
    redirect_to(url_for('workers', 'index'));
  }
}
?>