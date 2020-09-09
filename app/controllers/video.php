<?php
class VideoController extends ApplicationController
{
  function index()
  {
    check_access('video');
    
    $this->cameras = Camera::all();

    $this->title = 'Видео';
    $this->primary_menu_create_url = url_for('video', 'new');
    $this->load_attachments('Camera');
  }

  function add()
  {
    check_access('video/edit');
    
    $this->title = 'Новая камера';
    $this->camera = array('active' => true);
  }

  function create()
  {
    check_access('video/edit');
    
    $this->camera = new Camera();
    $this->camera->load_attributes($_POST['camera']);
    if ($this->camera->valid()) {
      $id = $this->camera->create();
      flash_notice('Камера была создана');
      redirect_to(url_for('video', 'show', $id));
    } else {
      $this->title = 'Новая камера';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('video');

    $this->camera = Camera::first($this->id);
    if (!$this->camera) show_404();
    
    $this->title = 'Видео';
    $this->subtitle = $this->camera['name'];
    $this->load_attachments('Camera', $this->id);
  }

  function edit()
  {
    check_access('video/edit');

    $this->camera = Camera::first($this->id);
    if (!$this->camera) show_404();
    
    $this->title = 'Редактирование';
    $this->subtitle = $this->camera['name'];
  }

  function update()
  {
    check_access('video/edit');
    
    $this->camera = Camera::first($this->id);
    if (!$this->camera) show_404();

    $this->camera->load_attributes($_POST['camera'], $this->id);
    if ($this->camera->valid()) {
      $this->camera->update();
      flash_notice('Камера была обновлена');
      redirect_to(url_for('video', 'show', $this->id));
    } else {
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('video/destroy');

    $this->camera = Camera::first($this->id);
    if (!$this->camera) show_404();
    
    flash_notice('Камера была удалена');
    $this->factory->destroy('cameras', $this->id);
    
    redirect_to(url_for('video'));
  }
}
?>