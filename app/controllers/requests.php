<?php
class RequestsController extends ApplicationController
{
  function index()
  {
    check_access('requests');

    $this->primary_menu_create_url = url_for('requests', 'new');

    $this->house_id = get_field_value('house');
    $this->request_type_id = get_field_value('type');
    $this->include_closed = get_field_value('include');
    $this->selection = $this->house_id == 'selection' ? get_field_value('selection') : null;
    
    $this->requests = $this->house_id ? Request::load(null, $this->house_id, $this->request_type_id, $this->include_closed, $this->selection) : array();
    if ($this->layout != 'print') {
      $this->houses = Request::houses($this->include_closed);
    }
    
    $this->title = count($this->requests) == 0 ? 'Заявки' : count($this->requests).' '.rus_word(count($this->requests), 'заявка', 'заявки', 'заявок');
    $this->subtitle = 'на '.date(DATE_FORMAT);
  }

  function add()
  {
    check_access('request/new');
    
    $this->title = 'Новая заявка';
    $this->subtitle = null;

    $this->request = new Request();
    $this->request['request_priority_id'] = Request::default_priority();
  }

  function create()
  {
    check_access('request/new');
  
    $this->title = 'Новая заявка';
    $this->subtitle = null;
  
    $this->request = new Request();
    $this->request->load_attributes($_POST['request']);
    if ($this->request->valid()) {
      $id = $this->request->create();
      
      $this->request = Request::load($this->request['id']);
      User::log_event('принял заявку', $this->request['request_type'].' для '.format_address($this->request, true), url_for('requests','show',$this->request['id']));
      flash_notice('Заявка была принята');
      
      redirect_to('/requests/new?index='.get_field_value('index'));
    } else {
      $this->request['city_id'] = get_field_value('request', 'city_id');
      $this->request['street_id'] = get_field_value('request', 'street_id');
    
      $this->subscriber = Subscriber::load_by_address($this->request['house_id'], $this->request['apartment']);
      $this->requests = $this->subscriber ? Request::load_for_subscriber($this->subscriber['id']) : Request::load_by_address($this->request['house_id'], $this->request['apartment']);
      
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('request/show');
    
    $this->request = Request::load($this->id, null, null, true);
    if (!$this->request) show_404();

    if (!$this->request['active']) flash_alert('Заявка была удалена. Для восстановления обратитесь к администратору.');
    if ($this->request['handled_on']) flash_alert('Заявка была закрыта. Для восстановления обратитесь к администратору.');
    
    if(!$this->request['handled_by'] && isset($_SESSION['request_handled_by']) && $_SESSION['request_handled_by'] && $_SESSION['request_handled_at'] >= time() - 2 * 60) {
      $this->request['handled_by'] = $_SESSION['request_handled_by'];
    }
    
    $this->title = 'Заявка';
    $this->subtitle = 'на '.format_address($this->request);
    $this->load_attachments('Request', $this->id);
  }

  function edit()
  {
    check_access('request/edit');
    
    $this->request = Request::load($this->id);
    if (!$this->request) show_404();
    
    $this->title = 'Редактирование';
    $this->subtitle = format_address($this->request);
  }

  function update()
  {
    check_access('request/edit');
  
    $this->request = new Request();
    $this->request->load_attributes($_POST['request'], $this->id);
    if ($this->request->valid()) {
      $this->request->update();
      
      $this->request = Request::load($this->request['id']);
      if (!$this->request) show_404();
      
      User::log_event('отредактировал заявку', $this->request['request_type'].' для '.format_address($this->request, true), url_for('requests','show',$this->request['id']));
      flash_notice('Заявка была обновлена');
      
      redirect_to('/requests/'.$this->id.'?index='.get_field_value('index'));
    } else {
      $this->request['city_id'] = get_field_value('request', 'city_id');
      $this->request['street_id'] = get_field_value('request', 'street_id');
      
      $this->title = 'Редактирование';
      $this->subtitle = format_address($this->request);

      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('request/destroy');
    
    if ($_POST)
    {
      $this->request = Request::load($this->id);
      if (!$this->request) show_404();
    
      $this->factory->deactivate('requests', $this->id);
      
      User::log_event('удалил заявку', $this->request['request_type'].' для '.format_address($this->request, true), url_for('requests','show',$this->request['id']));
      flash_notice('Заявка была удалена');
    }
    
    $this->redirect_to_index();
  }

  function restore()
  {
    check_access('request/restore');

    if ($_POST)
    {
      $this->factory->update('requests', $this->id, array('active' => 1, 'handled_on' => null));
      
      $this->request = Request::load($this->id);
      if (!$this->request) show_404();
      
      User::log_event('восстановил заявку', $this->request['request_type'].' для '.format_address($this->request, true), url_for('requests','show',$this->request['id']));
      flash_notice('Заявка была восстановлена');
    }
    
    redirect_to('/requests/'.$this->id.'?index='.get_field_value('index'));
  }

  function close()
  {
    check_access('request/close');

    $this->request = Request::load($this->id);
    if (!$this->request) show_404();
    
    if ($_POST) {
      $r = new Request();
      $r->load_attributes_for_close($this->id);
      if ($r->valid()) {
        $r->update();
        $_SESSION['request_handled_by'] = $r['handled_by'];
        $_SESSION['request_handled_at'] = time();
        
        $r = Request::load($this->id);
        User::log_event('закрыл заявку', $r['request_type'].' для '.format_address($r, true), url_for('requests','show',$r['id']));
        flash_notice('Заявка была закрыта');
        
        $this->redirect_to_index();
      } else {
        $this->request = Request::load($this->id);
        $this->request['handled_by'] = $r['handled_by'];
        $this->request['handled_comment'] = $r['handled_comment'];
        $this->request->errors = $r->errors;
      }
      
    } else {
    
      $this->request = Request::load($this->id);
    }
    
    $this->title = 'Заявка';
    $this->subtitle = 'на '.format_address($this->request);
    $this->load_attachments('Request', $this->id);

    render_action('show');
  }

  function subscriber()
  {
    $apartment = get_field_value('apartment');
    $house_id = get_field_value('house_id');
    
    $this->subscriber = Subscriber::load_by_address($house_id, $apartment);
    $this->requests = $this->subscriber ? Request::load_for_subscriber($this->subscriber['id']) : Request::load_by_address($house_id, $apartment);
  }

  function report_creator()
  {
    check_access('report/request_creator');
    $this->prepare_report();
    $this->requests = Request::daily_report('creator', $this->from_date, $this->to_date);
  }

  function report_updator()
  {
    check_access('report/request_updator');
    $this->prepare_report();
    $this->requests = Request::daily_report('updator', $this->from_date, $this->to_date);
  }

  function report_worker()
  {
    check_access('report/request_worker');
    $this->prepare_report();
    $this->requests = Request::daily_report('worker', $this->from_date, $this->to_date);
  }
  
  private function prepare_report()
  {
    $this->primary_menu_item = '/reports';
    
    $this->from_date = get_field_value('from');
    $this->to_date = get_field_value('to');
    if (!$this->to_date) $this->to_date = date(DATE_FORMAT);
    if (!$this->from_date) $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time()).'-7 days'));
    
    $this->title = 'Заявки';
    $this->subtitle = 'с '.$this->from_date.' по '.$this->to_date;
  }
  
  private function redirect_to_index()
  {
    redirect_to(
      get_field_value('index') ? 
      base64_decode(get_field_value('index')) : 
      url_for('requests', 'index')
    );
  }
}
?>