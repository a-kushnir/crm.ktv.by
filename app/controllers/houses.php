<?php
class HousesController extends ApplicationController
{
  function index()
  {
    check_access('houses');

    $this->filter = trim(get_field_value('filter'));
    
    if (has_access('house/full_index')) {
      $this->houses = House::load(null, $this->filter, null, null, get_field_value('page'), House::$page_size);
      $this->records = House::records($this->filter);
    } else {
      $this->houses = $this->filter ? House::load(null, $this->filter, null, null, get_field_value('page'), House::$page_size) : array();
      $this->records = $this->filter ? House::records($this->filter) : 0;
      if ($this->records > 100) $this->records = 100;
    }
    
    $this->title = 'Дома';
    if (has_access('house/new')) $this->primary_menu_create_url = url_for('houses', 'new');
    $this->load_attachments('House');
  }

  function add()
  {
    check_access('house/new');
    $this->title = 'Новый дом';
    $this->house = new House();
  }

  function create()
  {
    check_access('house/new');
    
    $this->house = new House();
    $this->house->load_attributes($_POST['house']);
    if ($this->house->valid() && $this->prepare_billing_account()) {
      $this->save_billing_account();
      $id = $this->house->create();
      $this->house->save_house_competitors();
      flash_notice('Дом был создан');
      redirect_to(url_for('houses', 'show', $id));
    } else {
      $this->title = 'Новый дом';
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('house/show');

    $ids = get_field_value('ids');
    if ($ids) {
      $this->houses = House::load(null, null, 'h.id in ('.mysql_real_escape_string($ids).')');
      $this->title = 'Печать выбранных домов';
    
    } else {
      $this->house = House::load($this->id);
      if (!$this->house) show_404();
      
      if ($this->house['billing_account_id'])
        $this->billing_account = BillingAccount::load($this->house['billing_account_id']);
      
      $this->debtors = isset($_GET['debtors']);
    
      $this->title = 'Дом';
      $this->subtitle = format_address($this->house);
      $this->load_attachments('House', $this->id);
    }
  }

  function edit()
  {
    check_access('house/edit');

    $this->house = House::load($this->id);
    if (!$this->house) show_404();
    
    if ($this->house['billing_account_id'])
      $this->billing_account = BillingAccount::load($this->house['billing_account_id']);

    $this->title = 'Редактирование';
    $this->subtitle = format_address($this->house);
  }

  function update()
  {
    check_access('house/edit');
    
    $this->house = House::load($this->id);
    if (!$this->house) show_404();
    
    $this->house = new House();
    $this->house->load_attributes($_POST['house'], $this->id);
    if ($this->house->valid() && $this->prepare_billing_account()) {
      $this->save_billing_account();
      $this->house->update();
      $this->house->save_house_competitors();
      flash_notice('Дом был обновлен');
      redirect_to(url_for('houses', 'show', $this->id));
    } else {
      $h = House::load($this->id);
      $this->house['city'] = $h['city'];
      $this->house['street'] = $h['street'];
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('destroy');

    $this->house = House::load($this->id);
    if (!$this->house) show_404();
    if (!$house->has_subscribers()) {
      flash_notice('Дом был удален');
      $this->factory->destroy('houses', $this->id);
    }
    redirect_to(url_for('houses'));
  }

  function download_memos()
  {
    check_access('house/download_memo');

    $records = BillingAccount::load_memos_for_house($this->id);
    $rtf_file = generate_client_memos($records);
    
    $house = House::load($this->id);
    $file_name = 'Памятки для '.format_address($house, true).'.rtf';
    download_file_by_content($rtf_file, $file_name, true);
  }
  
  function passport()
  {
    check_access('house/passport');

    $ids = get_field_value('ids');
    if ($ids) {
      $this->houses = House::load(null, null, 'h.id in ('.mysql_real_escape_string($ids).')');
      $this->title = 'Печать выбранных домов';
    
    } else {
      $this->house = House::load($this->id);
      if (!$this->house) show_404();
      
      $this->evil_subscribers = Subscriber::evil_subscribers($this->house['id']);
      $this->requests = Request::load(null, $this->house['id']);
      $this->amplifiers = Amplifier::all($this->house['id']);
    
      $this->title = format_address($this->house);
      $this->subtitle = 'на '.date(DATE_FORMAT);
      $this->load_attachments('House', $this->id);
    }
  }
  
  function subscribers()
  {
    $this->house = isset($this->id) ? House::load($this->id) : null;
    $this->subscribers = Subscriber::load_for_house($this->house['id']);
    
    $this->title = 'Абоненты';
    $this->subtitle = format_address($this->house);
  }
  
  function edit_subscribers()
  {
    $this->house = isset($this->id) ? House::load($this->id) : null;
    $this->subscribers = Subscriber::load_for_house($this->house['id']);
    
    $this->title = 'Абоненты';
    $this->subtitle = format_address($this->house);
  }
  
  function update_subscribers()
  {
    $this->house = isset($this->id) ? House::load($this->id) : null;
    $this->subscribers = Subscriber::load_for_house($this->house['id']);
    
    $stat = $this->house->update_subscribers(get_field_value('subscribers'));
    flash_notice('Абоненты сохранены (добавлено: '.$stat['inserted'].', обновлено: '.$stat['updated'].', удалено: '.$stat['deleted'].')');
    
    redirect_to('/houses/'.$this->id.'/subscribers');
  }
  
  private function prepare_billing_account()
  {
    $house = isset($this->id) ? House::load($this->id) : null;
    $billing_account_id = $house ? $house['billing_account_id'] : null;
    
    $this->billing_account = new BillingAccount();
    $this->billing_account->load_attributes($_POST['billing_account'], $billing_account_id);
    return (!$billing_account_id && !$this->billing_account['lookup_code'])
      || $this->billing_account->valid();
  }
  
  private function save_billing_account()
  {
    if ($this->billing_account['id']) {
      $this->billing_account->update();
    } else if ($this->billing_account['lookup_code']) {
      $id = $this->billing_account->create();
      $this->house['billing_account_id'] = $id;
    }
  }
  
  function rollback_billing_detail()
  {
    check_access('subscriber/new_billing');

    $billing_detail = BillingDetail::load($this->billing_detail_id);
    
    if ($_POST) {
      if ($billing_detail->rollbackable()) {
        $billing_account = BillingAccount::load($billing_detail['billing_account_id']);
        $operation = $this->factory->connection->execute_scalar("SELECT name FROM billing_detail_types WHERE id='".mysql_real_escape_string($billing_detail['billing_detail_type_id'])."'");

        $billing_account->rollback($billing_detail['id']);

        User::log_event('отменил движение по счету', $operation.' для '.$billing_account['lookup_code'].' за '.format_date($billing_detail['actual_date']).' на сумму '.format_money($billing_detail['value']).' с пометкой '.$billing_detail['comment'], url_for('billing','account_details',$billing_account['id']));
        flash_notice('Движение по счету отменено успешно');
      }
    }
    
    redirect_to('/billing/'.$billing_detail['billing_account_id'].'/account_details');
  }
}
?>