<?php

function process_billing_period() {
  $last_bp = BillingPeriod::load(null, 1);  
  $curr_bp_id = $curr_bp = null;
  
  $actual_date = calc_next_actual_date($last_bp);
  
  if ($actual_date) {
    $curr_bp = create_billing_period($actual_date);
    if (create_billing_details($curr_bp)) {
      BillingTariff::save_history($curr_bp['id']);
      finish_billing_period($curr_bp);
    }
  }
  
  return $curr_bp;
}

function calc_next_actual_date($last_bp) {
  $actual_date = null;
  if ($last_bp['id'] == null) { // first time
    $actual_date = strtotime(date('Y-m').'-01 00:00:00');
  } else { // last time
    $actual_date = strtotime(date("Y-m-d", strtotime($last_bp['actual_date']))."+1 month");
    if ($actual_date > time()) $actual_date = null;
  }
  return $actual_date;
}

function create_billing_period($actual_date) {
  $curr_bp = new BillingPeriod();
  $curr_bp['actual_date'] = date(MYSQL_DATE, $actual_date);
  $curr_bp['started_at'] = date(MYSQL_TIME, time());
  $curr_bp['total_subscribers'] = 0;
  $curr_bp['total_subscription_fee'] = 0;
  $curr_bp['id'] = $curr_bp->create();
  return $curr_bp;
}

function finish_billing_period($curr_bp) {
  $curr_bp['finished_at'] = date(MYSQL_TIME, time());
  $curr_bp->update();
}

function create_billing_details($curr_bp) {
  global $factory;

  $starts_on = strtotime(date("Y-m-d", strtotime($curr_bp['actual_date']))."-1 month");
  $comment = "Абонентская плата за ".date('m.Y', $starts_on);
  $starts_on = date(MYSQL_DATE, $starts_on);
  
  $query = "SELECT ba.*, bt.subscription_fee, s.id subscriber_id, s.billing_tariff_id
            FROM billing_accounts ba
            JOIN subscribers s ON s.billing_account_id = ba.id
            JOIN billing_tariffs bt ON s.billing_tariff_id = bt.id
            WHERE ba.active = true AND s.active = true AND s.starts_on <= '".$starts_on."' AND s.ends_on IS NULL";
  
  $rows = $factory->connection->execute($query);
  while($row = mysql_fetch_array($rows)) {
    $subscription_fee = $row['subscription_fee'];
    
    $ba = new BillingAccount($row);
    $success = $ba->change_actual_balance(-$subscription_fee, 'subscription_fee', $curr_bp['actual_date'], $comment, $ba['subscriber_id'], $curr_bp['id'], null);
    if (!$success)
      return false;
   
    $curr_bp['total_subscribers'] += 1;
    $curr_bp['total_subscription_fee'] += $subscription_fee;
  }
  
  $curr_bp['total_subscription_fee'] = prepare_float($curr_bp['total_subscription_fee']);
  unset($rows);
  
  return true;
}

?>