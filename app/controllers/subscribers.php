<?php
class SubscribersController extends ApplicationController
{
  function index()
  {
    check_access('subscribers');
  
    $this->title = 'Абоненты';
    
    $this->filter = trim(get_field_value('filter'));
    
    if (has_access('subscriber/full_index')) {
      $this->subscribers = Subscriber::load(null, $this->filter, get_field_value('page'), Subscriber::$page_size);
      $this->records = Subscriber::records($this->filter);
    } else {
      $this->subscribers = $this->filter ? Subscriber::load(null, $this->filter, get_field_value('page'), Subscriber::$page_size) : array();
      $this->records = $this->filter ? Subscriber::records($this->filter) : 0;
      if ($this->records > 1000) $this->records = 1000;
    }
    
    $this->primary_menu_search_url = url_for('subscribers');
    $this->primary_menu_create_url = url_for('subscribers', 'new');
    $this->primary_menu_filter = $this->filter;
  }

  function add()
  {
    check_access('subscriber/new');
    
    $this->title = 'Новый абонент';
    $this->primary_menu_back_url = url_for('subscribers');

    $this->subscriber = new Subscriber(array(
      'allow_calls' => true,
      'allow_sms' => true,
      'starts_on' => date(MYSQL_DATE, time()),
    ));

    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $this->subscriber['city_id'] = $selected_region['city_id'];
      $this->billing_tariffs = BillingTariff::load(null, $this->subscriber['city_id'], has_access('subscriber/all_tariffs'));
      foreach($this->billing_tariffs as $billing_tariff)
      if ($billing_tariff['default']) {
        $this->subscriber['billing_tariff_id'] = $billing_tariff['id'];
        break;
      }
    } else {
      $this->billing_tariffs = array();
    }
  }

  function create()
  {
    check_access('subscriber/new');
    
    $this->subscriber = new Subscriber();
    $this->subscriber->load_attributes($_POST['subscriber']);

    $this->billing_account = new BillingAccount();
    $this->billing_account->load_attributes($_POST['billing_account']);
    $this->billing_account->generate_lookup_code($this->subscriber);
    
    if ($this->subscriber->valid() && $this->billing_account->valid()) {
      
      $billing_account_id = $this->billing_account->create();
    
      $this->subscriber['billing_account_id'] = $billing_account_id;
      $id = $this->subscriber->create();

      $activation_fee = House::get_activation_fee($this->subscriber['house_id']);
      if (is_null($activation_fee)) {
        $billing_tariff = $this->subscriber->billing_tariff();
        $activation_fee = tofloat($billing_tariff['activation_fee']);
      }
      $this->billing_account->change_actual_balance(-$activation_fee, 'activation_fee', $this->subscriber['starts_on'], 'Плата за подключение', $id, null, null);
      
      Request::assign_requests($this->subscriber);
      
      $this->subscriber = Subscriber::load($this->subscriber['id']);
      User::log_event('добавил абонента', $this->subscriber->name().' '.format_address($this->subscriber), url_for('subscribers','show',$this->subscriber['id']));

      flash_notice('Абонент был создан');
      redirect_to(url_for('subscribers', 'new'));
    } else {
      $this->subscriber['city_id'] = get_field_value('subscriber', 'city_id');
      $this->subscriber['street_id'] = get_field_value('subscriber', 'street_id');

      $this->billing_tariffs = $this->subscriber['city_id'] ? 
        BillingTariff::load(null, $this->subscriber['city_id'], has_access('subscriber/all_tariffs')) : 
        array();
      
      $this->title = 'Новый абонент';
      $this->primary_menu_back_url = url_for('subscribers');
      render_action('add');
    }
  }
  
  function show()
  {
    check_access('subscriber/show');
  
    $this->primary_menu_back_url = url_for('subscribers');
    $this->primary_menu_edit_url = url_for('subscribers', 'edit', $this->id);
    $this->primary_menu_destroy_url = url_for('subscribers', 'destroy', $this->id);
    
    $sub_errors = isset($this->subscriber) ? $this->subscriber->errors : array();
    $this->subscriber = Subscriber::load($this->id);
    if (!$this->subscriber) show_404();
    $this->subscriber->errors = $sub_errors;
    
    if (!$this->subscriber['active']) flash_alert('Абонент был отключен. Для восстановления обратитесь к администратору.');
    
    $this->requests = Request::load_for_subscriber($this->id);
    if (!isset($this->request)) $this->request = new Request(array('request_priority_id' => Request::default_priority()));

    $this->messages = Sms::load_for_subscriber($this->id);
    if (!isset($this->message)) $this->message = new Sms();
    
    $this->calls = Call::load_for_subscriber($this->id);
    
    // Billing tab
    $this->billing_account = BillingAccount::load($this->subscriber['billing_account_id']);
    $this->billing_details = BillingDetail::load(null, null, "bd.billing_account_id = '".mysql_real_escape_string($this->subscriber['billing_account_id'])."'");
    if (!isset($this->billing_detail)) $this->billing_detail = new BillingDetail();
    
    if (count($this->billing_details) > 0) {
      $this->from_date = $this->billing_details[0]['actual_date'];
      $this->to_date = $this->billing_details[count($this->billing_details) - 1]['actual_date'];
      
      // Search incoming and outcoming balance
      $min_id = $this->billing_details[0]['id'];
      $max_id = $this->billing_details[0]['id'];
      $this->from_money = $this->billing_details[0]['actual_balance'] - $this->billing_details[0]['value'];
      $this->to_money = $this->billing_details[0]['actual_balance'];
      foreach($this->billing_details as $billing_detail) {
        if ($min_id > $billing_detail['id']) {
          $min_id = $billing_detail['id'];
          $this->from_money = $billing_detail['actual_balance'] - $billing_detail['value'];
        } else if ($max_id < $billing_detail['id']) {
          $max_id = $billing_detail['id'];
          $this->to_money = $billing_detail['actual_balance'];
        }
      }
    }
    
    $this->title = 'Абонент';
    $this->subtitle = $this->subscriber->name();
    $this->load_attachments('Subscriber', $this->id);
  }

  function edit()
  {
    check_access('subscriber/edit');
    
    $this->primary_menu_back_url = url_for('subscribers', 'show', $this->id);
    
    $this->subscriber = Subscriber::load($this->id);
    if (!$this->subscriber) show_404();

    $this->billing_account = BillingAccount::load($this->subscriber['billing_account_id']);
    $this->billing_tariffs = BillingTariff::load(null, $this->subscriber['city_id'], has_access('subscriber/all_tariffs'));
      
    $this->title = 'Редактирование';
    $this->subtitle = $this->subscriber->name();
  }

  function update()
  {
    check_access('subscriber/edit');
    
    $s = Subscriber::load($this->id);
    if (!$s) show_404();
    
    $this->subscriber = new Subscriber();
    $this->subscriber->load_attributes($_POST['subscriber'], $this->id);

    if ($s->self_billing()) {
      $ba = BillingAccount::load($s['billing_account_id']);
      $this->billing_account = new BillingAccount();
      $this->billing_account->load_attributes($_POST['billing_account'], $ba['id']);
    }
    
    $valid = false;
    if ($s->self_billing()) {
      $valid = $this->subscriber->valid() && $this->billing_account->valid();
    } else {
      $valid = $this->subscriber->valid_for_house_billing();
    }
    
    if ($valid) {
      $this->subscriber->update();
      if(isset($this->billing_account)) $this->billing_account->update();
      Request::assign_requests($this->subscriber);
      
      $this->subscriber = Subscriber::load($this->subscriber['id']);
      User::log_event('отредактировал абонента', $this->subscriber->name().' '.format_address($this->subscriber), url_for('subscribers','show',$this->subscriber['id']));
      flash_notice('Абонент был обновлен');
      
      redirect_to(url_for('subscribers', 'show', $this->id));
    } else {
      $this->subscriber['city_id'] = get_field_value('subscriber', 'city_id');
      $this->subscriber['street_id'] = get_field_value('subscriber', 'street_id');
      $this->billing_tariffs = $this->subscriber['city_id'] ? BillingTariff::load(null, $this->subscriber['city_id'], has_access('subscriber/all_tariffs')) : array();
      
      $this->title = 'Редактирование';
      $this->subtitle = $this->subscriber->name();
      render_action('edit');
    }
  }
  
  function destroy()
  {
    check_access('subscriber/destroy');

    if ($_POST)
    {
      $this->subscriber = new Subscriber();
      $this->subscriber->load_attributes_for_termination($_POST['subscriber'], $this->id);
      if ($this->subscriber->valid_termination()) {
        $this->subscriber->terminate();
        
        User::log_event('отключил абонента', $this->subscriber->name().' '.format_address($this->subscriber), url_for('subscribers','show',$this->subscriber['id']));
        flash_notice('Абонент был отключен');
        redirect_to(url_for('subscribers', 'index'));
      } else {
        $this->show();
        $this->subscriber->load_attributes_for_termination($_POST['subscriber'], $this->id);
        render_action('show');
      }
    } else {
      redirect_to(url_for('subscribers', 'index'));
    }
  }

  function restore()
  {
    check_access('subscriber/restore');

    if ($_POST)
    {
      if (!Subscriber::occupied_address($this->id)) {    
        Subscriber::reactivate($this->id);
        
        $subscriber = Subscriber::load($this->id);
        
        $this->factory->connection->execute("UPDATE requests 
          SET subscriber_id = '".mysql_real_escape_string($this->id)."'
          WHERE subscriber_id is null and house_id = '".mysql_real_escape_string($subscriber['house_id'])."' and apartment = '".mysql_real_escape_string($subscriber['apartment'])."'");
        
        User::log_event('восстановил абонента', $subscriber->name().' '.format_address($subscriber), url_for('subscribers','show',$subscriber['id']));
        flash_notice('Абонент был восстановлен');
      } else {
        flash_alert('Договор по данному адресу уже существует');
      }
    }
    
    redirect_to(url_for('subscribers', 'show', $this->id));
  }

  function download_memo()
  {
    check_access('subscriber/download_memo');

    $this->subscriber = Subscriber::load($this->id);
    if (!$this->subscriber) show_404();
    
    $records = BillingAccount::load_memos_for_subscriber($this->id);
    $rtf_file = generate_client_memos($records);
    
    $file_name = 'Памятка '.$this->subscriber['lookup_code'].'.rtf';
    download_file_by_content($rtf_file, $file_name, true);
  }

  function download_template_file($template_path, $file_name)
  {
    $template_path = $_SERVER['DOCUMENT_ROOT'].'/lib/files/'.$template_path;
	  
    $subscriber = Subscriber::load($this->id);
    if (!$subscriber) show_404();
    $billing_account = BillingAccount::load($subscriber['billing_account_id']);
    $billing_tariff = $subscriber->billing_tariff();
    
    $actual_balance = $subscriber['actual_balance'] + (date('d') == '01' ? $billing_tariff['subscription_fee'] : 0);
    $subscription_fee = $billing_tariff['subscription_fee'];
    $sum = -($actual_balance - $subscription_fee);
    
    $rtf_file = implode('', file($template_path));

    $replaces = array(
      rtf_encode('Аб.имя')   => rtf_encode($subscriber->name()),
      rtf_encode('Аб.адрес') => rtf_encode(format_address($subscriber, true)),
      rtf_encode('Лиц.счет') => rtf_encode($subscriber['lookup_code']),
      rtf_encode('Аб.долг')  => format_money(-$actual_balance, array('prefix' => '', 'suffix' => '')),
      rtf_encode('Аб.плата') => format_money($subscription_fee, array('prefix' => '', 'suffix' => '')),
      rtf_encode('Аб.сумма') => format_money($sum, array('prefix' => '', 'suffix' => '')),
      rtf_encode('Дата.Сегодня') => date(DATE_FORMAT),
      rtf_encode('Тек.Месяц') => date(MONTH_FORMAT),
      rtf_encode('След.Месяц') => date('d') == '01' ? date(MONTH_FORMAT) : date(MONTH_FORMAT, strtotime('+1 month', strtotime(date("Y-m-01")))),
    );
    
    foreach($replaces as $key => $value)
      $rtf_file = str_replace($key, $value, $rtf_file);

	$file_name = str_replace('<lookup_code>', $subscriber['lookup_code'], $file_name);
    download_file_by_content($rtf_file, $file_name, true);
  }

  function download_cancel()
  {
    check_access('subscriber/download_cancel');

	$subscriber = Subscriber::load($this->id);
    if (!$subscriber) show_404();
    $billing_account = BillingAccount::load($subscriber['billing_account_id']);
    $billing_tariff = $subscriber->billing_tariff();
    
    $actual_balance = $subscriber['actual_balance'] + (date('d') == '01' ? $billing_tariff['subscription_fee'] : 0);
    $subscription_fee = $billing_tariff['subscription_fee'];
    $sum = -($actual_balance - $subscription_fee);
	
	$template_path = $sum > 0 ? 'cancel_with_debt.rtf' : 'cancel_without_debt.rtf';
	$file_name = 'Заявление на отключение <lookup_code>.rtf';

	$this->download_template_file($template_path, $file_name);
  }
  
  function download_complaint()
  {
    check_access('subscriber/download_cancel');

	$template_path = 'complaint.rtf';
	$file_name = 'Претензия <lookup_code>.rtf';

	$this->download_template_file($template_path, $file_name);
  }
  
  function download_envelope()
  {
	check_access('subscriber/download_cancel');

	$template_path = 'envelope.rtf';
	$file_name = 'Конверт <lookup_code>.rtf';

	$this->download_template_file($template_path, $file_name);
  }
  
  function new_request()
  {
    check_access('request/new');
  
    $this->request = new Request();
    $this->request->load_attributes_for_subscriber(Subscriber::load($this->id), $_POST['request']);
    if ($this->request->valid()) {
      $request_id = $this->request->create();
      
      $this->request = Request::load($request_id);
      User::log_event('принял заявку', $this->request['request_type'].' для '.format_address($this->request), url_for('requests','show',$this->request['id']));
      flash_notice('Заявка была принята');
      
      redirect_to(url_for('subscribers', 'show', $this->id.'#requests'));
    } else {
      $this->show();
      render_action('show');
    }
  }
  
  function new_message()
  {
    has_access('subscriber/new_message');
  
    $text = get_object_value($_POST['message'], 'text');
    if ($text) {
      $subscribers = Sms::load_subscribers($this->id);
      foreach($subscribers as $subscriber) {
        Sms::send($subscriber, $text);
      }

      flash_notice($this->id ? 'Сообщение отправлено' : 'Сообщений отправлено: '.count($subscribers));
      
      redirect_to(url_for('subscribers', 'show', $this->id.'#messages'));
    } else {
      $this->message = new Sms(array('text' => $text));
      $this->message->errors['text'] = ERROR_BLANK;
    
      $this->show();
      
      render_action('show');
    }
  }

  function new_billing_detail()
  {
    has_access('subscriber/new_billing');
    
    $this->subscriber = Subscriber::load($this->id);
    if (!$this->subscriber) show_404();
    $this->billing_account = BillingAccount::load($this->subscriber['billing_account_id']);
    
    if ($_POST) {
      $this->billing_detail = new BillingDetail();
      $this->billing_detail->load_attributes($_POST['billing_detail']);
      if ($this->billing_detail->valid()) {
        $success = $this->billing_account->change_actual_balance($this->billing_detail['value'], $this->billing_detail['billing_detail_type'], $this->billing_detail['actual_date'], $this->billing_detail['comment'], $this->id, null, null);
        if ($success) {
          
          $operation = $this->factory->connection->execute_scalar("SELECT name FROM billing_detail_types WHERE id='".mysql_real_escape_string($this->billing_detail['billing_detail_type_id'])."'");
          User::log_event('произвел движение по счету', $operation.' для '.$this->billing_account['lookup_code'].' за '.format_date($this->billing_detail['actual_date']).' на сумму '.format_money($this->billing_detail['value']).' с пометкой '.$this->billing_detail['comment'], url_for('billing','account_details',$this->billing_account['id']));
          flash_notice('Движение по счету произведено успешно');
          
          redirect_to(url_for('subscribers', 'show', $this->id.'#billing'));
        } else {
          flash_alert('Произошел сбой во время проведения операции');
          $this->show();
          render_action('show');
        }
      } else {
        $this->show();
        render_action('show');
      }
    }
    
    $this->subscriber = Subscriber::load($this->id);
    
    $this->title = 'Движение по счету';
    $this->subtitle = $this->billing_account['lookup_code'];
  }
  
  function rollback_billing_detail()
  {
    has_access('subscriber/new_billing');

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
    
    redirect_to('/subscribers/'.$this->id.'#billing');
  }
  
  function notify()
  {
    has_access('subscriber/new_message');

    if ($_POST) {
      
      $message = get_object_value($_POST['sms'], 'message');
      if ($message) {
        $subscribers = Sms::load_subscribers($this->id);
        foreach($subscribers as $subscriber) {
          Sms::send($subscriber, $message);
        }
        flash_notice('Сообщений отправлено: '.count($subscribers));
        redirect_to(url_for('subscribers', 'notify', $this->id));
        
      } else {
        $this->sms = new Sms();
        $this->sms['message'] = $message;
        $this->sms->errors['message'] = ERROR_BLANK;
      }
    }

    if ($this->id) {
      $this->subscriber = Subscriber::load($this->id);
      $this->sms_available = Sms::available($this->id);
    } else {
      $this->count = Sms::count_subscribers();
    }
    
    $this->title = 'Отправка сообщения';
  }
  
}
?>