<?php
function notify_data() {
  if (!isset($_POST['auth'])) {
  echo 'ERROR: No auth header';
  
  } else if (!try_auth($_POST['auth'])){
  echo 'ERROR: Auth failed';
  
  } else if (isset($_POST['data'])) {
    $data = $_POST['data'];
    $data = explode("|", $data);
    
    $event = isset($data[0]) ? $data[0] : '';
    $station = isset($data[1]) ? $data[1] : '';
    $frequency = isset($data[2]) ? $data[2] : '';
    
    if (!$event || 
        !$station || !is_numeric($station) || 
        ($frequency && !is_numeric($frequency))) {
      echo 'ERROR: Invalid data format';
      return;
    }
    
    $station_id = (int)$station;
    $freq = $frequency ? ((int)$frequency)/1000000 : null;
    
    if ($event == 'ping') {
      Channel::save_notification($event, $station_id);
      Channel::nofity_station($station_id);
      
    } else if ($event == 'broken' || $event == 'already_broken') {
      $channel_id = Channel::find_by_station_and_frequency($station_id, $freq);
      Channel::save_notification($event, $station_id, $channel_id, $freq);
      if ($channel_id) {
        $wasnt_broken = !Channel::is_broken($channel_id);
        Channel::mark_broken($channel_id);
        
        // GENERATE SMS IF BROKEN
        $phone_number = Config::get('monitoring_cell');
        $template_sms = Config::get('monitoring_sms');
        $allowed_hours = Config::get('monitoring_hours');

        $is_allowed_hour = true;
        if ($allowed_hours) {
          $allowed_hours = explode('-', $allowed_hours, 3);
          if (count($allowed_hours) == 2 && is_numeric($allowed_hours[0]) && is_numeric($allowed_hours[1])) {
            $curr_hour = (integer)date('G');
            $allowed_hours[0] = (integer)$allowed_hours[0];
            $allowed_hours[1] = (integer)$allowed_hours[1];
            $is_allowed_hour = ($allowed_hours[0] < $allowed_hours[1] && ($allowed_hours[0] <= $curr_hour && $curr_hour < $allowed_hours[1])) ||
                               ($allowed_hours[0] > $allowed_hours[1] && ($allowed_hours[0] <= $curr_hour || $curr_hour < $allowed_hours[1])) || 
                               ($allowed_hours[0] == $allowed_hours[1] && ($allowed_hours[0] == $curr_hour));
          }
        }
        
        if ($wasnt_broken && $phone_number && $template_sms && $is_allowed_hour) {
          $channel = Channel::load($channel_id);
          $replaces = array(
            '{code}' => $channel['channel_code'],
            '{name}' => $channel['name'],
            '{frequency}' => format_float($channel['frequency'], 2),
            '{station}' => $channel['station'],
            '{datetime}' => date('d.m H:i'),
            '{date}' => date('d.m'),
            '{time}' => date('H:i'),
          );
      
          $message = $template_sms;
          foreach($replaces as $key => $value)
            $message = str_replace($key, $value, $message);
        
          Sms::send_to_admin($phone_number, $message);
        }
      }
      
    } else if ($event == 'repair') {
      $channel_id = Channel::find_by_station_and_frequency($station_id, $freq);
      Channel::save_notification($event, $station_id, $channel_id, $freq);
      if ($channel_id) Channel::mark_repaired($channel_id);
      
    } else {
      echo "ERROR: Event isn't supported";
      return;
    }
    
  } else {
    echo 'ERROR: No data to notify';
  }
}

function try_auth($auth)
{
  $values = explode(':', $auth);
  $login = $values[0];
  $password = $values[1];
  return (abs(time() - $login) <= DS_TIMEOUT) && $password == md5($login.DS_SECRET);
}
?>