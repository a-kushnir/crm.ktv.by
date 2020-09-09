<?php
define('CALL_DELAY', 24 * 60 * 60);

class ArrearsController extends ApplicationController
{
  function index()
  {
    $filter = trim(get_field_value('filter'));
    $this->subscribers = Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size, true);
    $this->records = Subscriber::records($filter, true);

    $_SESSION['arrears'] = Subscriber::arrear_indexes($filter);
    $_SESSION['arrears_filter'] = $filter;
    
    $this->title = 'Должники';
    $this->subtitle = has_access('arrear/show_sum') ? format_money(-Subscriber::total_debt()).' / '.$this->records : null;
  }

  function show()
  {
    $this->subscriber = Subscriber::load($this->id);
    if (!$this->subscriber) show_404();
    
    $this->requests = Request::load_for_subscriber($this->id);
    $this->messages = Sms::load_for_subscriber($this->id);
    $this->calls = Call::load_for_subscriber($this->id);
    if (!$this->subscriber['active']) flash_alert('Абонент был отключен. Для восстановления обратитесь к администратору.');
    
    $this->billing_account = BillingAccount::load($this->subscriber['billing_account_id']);
    $this->billing_details = BillingDetail::load(null, null, "bd.billing_account_id = '".mysql_real_escape_string($this->subscriber['billing_account_id'])."'");
    
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
    
    $this->title = 'Должник';
    $this->subtitle = $this->subscriber->name();
    $this->load_attachments('Subscriber', $this->id);
  }

  function cell()
  {
    $this->save_call('cell');
  }

  function home()
  {
    $this->save_call('home');
  }
  
  private function save_call($phone_type)
  {
    $subscriber = Subscriber::load($this->id);
    $phone_number = $subscriber[$phone_type.'_phone'];
    $phone_number = Call::corrected_phone_number($subscriber['id'], $phone_type, $phone_number);

    $attributes = isset($_POST['call']) ? $_POST['call'] : array();
    $attributes['subscriber_id'] = $this->id;
    $attributes['call_result_id'] = $_GET['answer'];
    $attributes['phone_type'] = $phone_type;
    $attributes['phone_number'] = $phone_number;
    
    $this->call = new Call();
    $this->call->load_attributes($attributes);
    if ($this->call->valid()) {
      $this->call->save_call();
      flash_notice('Звонок был сохранен');
      redirect_to(url_for('arrears', 'show', $this->id));
    } else {
      $this->show();
      render_action('show');
    }
  }

  function prev()
  {
    $arrears = $_SESSION['arrears'];
    $index = array_search($this->id, $arrears);
    $index--;
    if ($index < 0) {
      redirect_to(url_for('arrears', 'index', null, '?filter='.$_SESSION['arrears_filter']));
    } else {
      redirect_to(url_for('arrears', 'show', $arrears[$index]));
    }
  }

  function next()
  {
    $arrears = $_SESSION['arrears'];
    $index = array_search($this->id, $arrears);
    $index++;
    if ($index >= count($arrears)) {
      redirect_to(url_for('arrears', 'index', null, '?filter='.$_SESSION['arrears_filter']));
    } else {
      redirect_to(url_for('arrears', 'show', $arrears[$index]));
    }
  }

  function report()
  {
    check_access('report/arrears');
    
    $this->call_summary = Call::report_summary(30);
    $this->call_details = Call::report_details(30);
    
    $this->title = 'Отчет о звонках';
    $this->subtitle = 'за 30 дней';
    
    $this->primary_menu_item = '/reports';
  }
  
  function destroy()
  {
    check_access('subscriber/destroy');
    
    $subscriber = Subscriber::load($this->id);
    
    $req_attributes = array(
      'request_type_id' => 3, // TODO config
      'request_priority_id' => Request::default_priority(), // TODO config
      'comment' => Config::get('arrear_termination_comment')
    );
    
    $request = new Request();
    $request->load_attributes_for_subscriber($subscriber, $req_attributes);

    $subscriber = new Subscriber();
    $subscriber->load_attributes_for_termination(array(
      'termination_reason_id' => 10 // TODO config
    ), $this->id);

    if ($request->valid()) {
      if ($subscriber->valid_termination()) {
        $request->create();
        $subscriber->terminate();
        flash_notice('Абонент отключен и создана заявка');
      } else {
        flash_alert('Произошла ошибка при отключении абонента');
      }
    } else {
      flash_alert('Произошла ошибка при создании заявки');
    }
    
    redirect_to(url_for('arrears', 'show', $this->id));
  }
}
?>