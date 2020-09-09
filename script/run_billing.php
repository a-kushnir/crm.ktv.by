<?php
include('../lib/engine/startup.php');

$billing_period = process_billing_period();

if ($billing_period) {
  echo('Subscription fee was successfully accrued for '.$billing_period['total_subscribers'].' subscribers to the amount of '.$billing_period['total_subscription_fee'].' rub.');
} else {
  echo('Everything in its own time...');
}

?>