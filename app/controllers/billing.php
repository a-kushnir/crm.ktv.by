<?php
class BillingController extends ApplicationController
{
  function index()
  {
    check_access('billing');
    
    $this->title = 'Биллинг';

    $this->billing_periods = BillingPeriod::load();
    $this->billing_period_rollback = BillingPeriod::last();
    $this->billing_files = BillingFile::load(null, get_field_value('filter'), get_field_value('page'), BillingFile::$page_size);
    $this->records = BillingFile::records(get_field_value('filter'));
  }

  function run_now()
  {
    check_access('billing/new_period');
    
    $billing_period = process_billing_period();
    
    if ($billing_period) {
      User::log_event('начислил абонентскую плату', 'Для '.$billing_period['total_subscribers'].' абонентов на сумму '.format_money($billing_period['total_subscription_fee']).' за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date']."-1 month"))), url_for('billing','period_details',$billing_period['id']));
      flash_notice('Абонентская плата успешно начислена для '.$billing_period['total_subscribers'].' абонентов на сумму '.format_money($billing_period['total_subscription_fee']));
    } else {
      flash_alert('Время для начисления абонентской платы еще не наступило.');
    }
    
    redirect_to('/billing');
  }

  function rollback_file()
  {
    check_access('billing/rollback_file');
    
    if ($_POST) {
      $billing_file = BillingFile::load($this->id);
      if ($billing_file) {
        if ($billing_file->rollback()) {
          User::log_event('откатил файл', $billing_file['file_name']);
          flash_notice('Файл был откачен.');
        }
      }
    }
      
    redirect_to('/billing');
  }

  function rollback_period()
  {
    check_access('billing/rollback_period');
    
    if ($_POST) {
      $billing_period = BillingPeriod::last();
      if ($billing_period) {
        $billing_period->rollback();
        User::log_event('откатил период', 'за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date']."-1 month"))));
        flash_notice('Последний период успешно удален.');
      } else {
        flash_alert('Подходящий период не найден.');
      }
    }
    
    redirect_to('/billing');
  }

  function rollback_detail()
  {
    has_access('subscriber/new_billing');

    $billing_detail = BillingDetail::load($this->id);
    
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

  function period_details()
  {
    check_access('billing');
    
    $this->full_file = isset($_GET['full']);
    $this->billing_period = BillingPeriod::load($this->id);
    $this->billing_details = BillingDetail::load(null, null, "billing_period_id = '".mysql_real_escape_string($this->id)."'");
    
    $this->title = 'Детализация периода';
    $this->subtitle = format_month(date(MYSQL_DATE,strtotime($this->billing_period['actual_date']."-1 month")));
    $this->load_attachments('BillingPeriod', $this->id);
  }

  function file_details()
  {
    check_access('billing');
    
    $this->full_file = isset($_GET['full']);
    $this->billing_file = BillingFile::load($this->id);
    $this->billing_file_logs = BillingFile::logs($this->id);
    
    $this->title = 'Импорт файла';
    $this->subtitle = $this->billing_file['file_name'];
    $this->load_attachments('BillingFile', $this->id);
  }

  function download_file()
  {
    check_access('billing');
    
    $full_file = isset($_GET['full']);
    $billing_file = BillingFile::load($this->id);
    
    download_file_by_content($billing_file['file_content'], $billing_file['file_name'], false, 'text/plain; charset=UTF-8');
  }

  function fix_file_detail()
  {
    check_access('billing');
    
    $this->title = 'Ручное исправление';
    
    $this->billing_file_log = BillingFile::log($this->id);
    
    if ($this->billing_file_log['billing_detail_id']) {
      flash_alert('Эта запись уже исправлена');
      redirect_to(url_for('billing','file_details',$this->billing_file_log['billing_file_id']));
    }
    
    $this->request = $this->billing_file_log['request_id'] ? Request::load($this->billing_file_log['request_id']) : array();
    if (isset($this->request) && isset($this->request['active']) && !$this->request['active']) $this->request = array();
    
    if ($_POST) {
      if (!$this->billing_file_log['billing_detail_id']) {
        $subscriber_id = get_field_value('subscriber');
        if ($subscriber_id) { // Apply Payment for Subscriber 
          $subscriber = Subscriber::load($subscriber_id);
          $billing_account = BillingAccount::load($subscriber['billing_account_id']);
          
          $bd_id = $billing_account->change_actual_balance($this->billing_file_log['value'], 'payment', $this->billing_file_log['actual_date'], $this->billing_file_log['payment_comment'], $subscriber_id, null, $this->billing_file_log['billing_file_id']);
          
          if ($bd_id) {
            $attributes = array(
              'message' => $this->billing_file_log['message'].' (исправлен вручную)',
              'billing_detail_id' => $bd_id,
              'ours' => $billing_account['lookup_code']."\n".$subscriber->name()."\n".format_address($subscriber)
            );
            $this->factory->update('billing_file_logs', $this->id, $attributes);
            
            $billing_file = BillingFile::load($this->billing_file_log['billing_file_id']);
            $attributes = array(
              'success_count' => $billing_file['success_count'] + 1,
              'failed_count' => $billing_file['failed_count'] - 1,
              'unhandled' => ($billing_file['failed_count'] > 1 ? '1' : '0')
            );
            $this->factory->update('billing_files', $billing_file['id'], $attributes);
            
            flash_notice('Движение по счету произведено успешно');
            redirect_to(url_for('billing','file_details',$this->billing_file_log['billing_file_id']));
          }
        } else { // Add request
          $this->request = new Request();
          $this->request->load_attributes($_POST['request']);
          
          $this->request['home_phone'] = '';
          $this->request['cell_phone'] = '';
          $this->request['request_type_id'] = 1; // Подключение
          $this->request['request_priority_id'] = Request::default_priority();
          $this->request['comment'] = Config::get('fix_billing_file_comment');
          
          if ($this->request->valid()) {
            $r_id = $this->request->create();
            
            $attributes = array(
              'request_id' => $r_id
            );
            $this->factory->update('billing_file_logs', $this->id, $attributes);
            
            flash_notice('Заявка подана успешно');
            redirect_to(url_for('billing','fix_file_detail',$this->id));
          } else {
            if(!isset($this->request->errors['address'])) flash_alert('Произошла ошибка при создании заявки');
            $this->request['city_id'] = get_field_value('request', 'city_id');
            $this->request['street_id'] = get_field_value('request', 'street_id');
          }
        }
      }
    }

    $filter = trim(get_field_value('filter'));
    if (!$filter) {
      $theirs = explode("\n", $this->billing_file_log['theirs']);
      $filter = $theirs[0];
    }
    $this->subscribers = $filter ? Subscriber::load(null, $filter, get_field_value('page'), Subscriber::$page_size) : array();
    $this->records = $filter ? Subscriber::records($filter) : 0;
    
    $this->load_attachments('BillingFileLog', $this->id);
  }

  function assign_request()
  {
    check_access('billing');
    
    $this->factory->update('billing_file_logs', $this->id, array('request_id' => get_object_value($_GET, 'request')));
    
    redirect_to(url_for('billing', 'fix_file_detail', $this->id));
  }

  function dismiss_request()
  {
    check_access('billing');
    
    if ($_POST) {
      $this->factory->update('billing_file_logs', $this->id, array('request_id' => null));
    }
    
    redirect_to(url_for('billing', 'fix_file_detail', $this->id));
  }

  function account_details()
  {
    check_access('subscriber/billing');
  
    $this->billing_account = BillingAccount::load($this->id);
    if (!$this->billing_account) show_404();
    
    $this->owner = $this->billing_account->owner();
    $this->billing_details = BillingDetail::load(null, null, "bd.billing_account_id = '".mysql_real_escape_string($this->id)."'");
    
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
    
    $this->title = 'Выписка по счету';
    $this->subtitle = $this->billing_account['lookup_code'];
    $this->primary_menu_item = '/subscribers';
  }

  function new_account_detail()
  {
    check_access('subscriber/new_billing');
    
    $this->billing_account = BillingAccount::load($this->id);
    
    if ($_POST) {
      $this->billing_detail = new BillingDetail();
      $this->billing_detail->load_attributes($_POST['billing_detail']);
      if ($this->billing_detail->valid()) {
        $owner = $this->billing_account->owner();
        $subscriber_id = get_class($owner) == 'Subscriber' ? $owner['id'] : null;
        $success = $this->billing_account->change_actual_balance($this->billing_detail['value'], $this->billing_detail['billing_detail_type'], $this->billing_detail['actual_date'], $this->billing_detail['comment'], $subscriber_id, null, null);
        if ($success) {
          
          $operation = $this->factory->connection->execute_scalar("SELECT name FROM billing_detail_types WHERE id='".mysql_real_escape_string($this->billing_detail['billing_detail_type_id'])."'");
          User::log_event('произвел движение по счету', $operation.' для '.$this->billing_account['lookup_code'].' за '.format_date($this->billing_detail['actual_date']).' на сумму '.format_money($this->billing_detail['value']).' с пометкой '.$this->billing_detail['comment'], url_for('billing','account_details',$this->billing_account['id']));
          flash_notice('Движение по счету произведено успешно');
          
          redirect_to(url_for('billing','account_details',$this->id));
        } else {
          flash_alert('Произошел сбой во время проведения операции');
        }
      } else {
      }
    }
    
    $this->subscriber = Subscriber::load($this->billing_account['subscriber_id']);
    
    $this->title = 'Движение по счету';
    $this->subtitle = $this->billing_account['lookup_code'];
  }

  function download_memos()
  {
    check_access('billing/download_memo');
    
    $records = BillingPeriod::load_new_memos($this->id);
    $records = sort_client_memos($records);
    $rtf_file = generate_client_memos($records);
    
    BillingPeriod::inc_client_memo_downloads($this->id);
    
    $billing_period = BillingPeriod::load($this->id);
    $file_name = 'Памятки за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date'].'-1 month'))).'.rtf';
    download_file_by_content($rtf_file, $file_name, true);
  }

  function send_sms()
  {
    check_access('billing/send_messages');
    
    $template_sms = Config::get('template_sms');
    
    BillingPeriod::mark_sms_sent($this->id);
    $subscribers = BillingAccount::load_for_sms($this->id);
    
    foreach($subscribers as $subscriber) {
      $replaces = array(
        '{first_name}' => $subscriber['first_name'],
        '{last_name}' => $subscriber['last_name'],
        '{middle_name}' => $subscriber['middle_name'],
        '{value}' => -(int)$subscriber['value'],
        '{actual_balance}' => (int)$subscriber['actual_balance'],
        '{period}' => format_month(date(MYSQL_DATE,strtotime($subscriber['actual_date']."-1 month"))),
        '{date}' => format_date($subscriber['actual_date']),
      );
    
      $message = $template_sms;
      foreach($replaces as $key => $value)
        $message = str_replace($key, $value, $message);
    
      Sms::send($subscriber, $message);
    }
    
    $billing_period = BillingPeriod::load($this->id);
    User::log_event('сгенерировал сообщения', count($subscribers).' сообщений сформировано за '.format_month(date(MYSQL_DATE,strtotime($billing_period['actual_date'].'-1 month'))), url_for('billing','period_details',$this->id));

    flash_notice(count($subscribers).' сообщений успешно сформировано');
    redirect_to(url_for('billing', 'period_details', $this->id));
  }

  function report()
  {
    check_access('report/revenue');
    
    $this->from_date = get_field_value('from');
    $this->to_date = get_field_value('to');
    if (!$this->to_date) $this->to_date = date(DATE_FORMAT);
    if (!$this->from_date) $this->from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time())."-1 month +1 day"));
    
    $this->title = 'Отчет по платежам';
    $this->subtitle = 'с '.$this->from_date.' по '.$this->to_date;
    $this->primary_menu_item = '/reports';

    $this->billing_files = BillingFile::report(prepare_date($this->from_date), prepare_date($this->to_date));
    $this->billing_sources = BillingFile::sources(prepare_date($this->from_date), prepare_date($this->to_date));
    $this->tax_on_turnover = tofloat(Config::get('tax_on_turnover'));
  }

  function download_report()
  {
    check_access('report/billing');
    
    $from_date = get_field_value('from');
    $to_date = get_field_value('to');
    if (!$to_date) $to_date = date(DATE_FORMAT);
    if (!$from_date) $from_date = date(DATE_FORMAT, strtotime(date(DATE_FORMAT, time())."-1 month"));
    
    $billing_files = BillingFile::report_details(prepare_date($from_date), prepare_date($to_date));
    
    //$file_content = chr(239).chr(187).chr(191); # UTF-8 BOM (0xEF 0xBB 0xBF)
    $file_content = '';
    $file_content .= "Дата;Агент;Ордер;Сумма\n";
    
    foreach($billing_files as $billing_file) {
      $file_content .= format_date($billing_file['order_date']).';';
      $file_content .= $billing_file['billing_source'].';';
      $file_content .= $billing_file['order_code'].';';
      $file_content .= format_money($billing_file['order_fee'], array('suffix' => '', 'thousands_sep' => ''));
      $file_content .= "\n";
    }
    
    $file_content = iconv("UTF-8", "WINDOWS-1251", $file_content);
    
    $file_name = 'Поступления с '.$from_date.' по '.$to_date.'.csv';
    download_file_by_content($file_content, $file_name, true);
  }
}

?>