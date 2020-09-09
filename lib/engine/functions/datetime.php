<?php
function delta_days($from, $to) {
  if(!$from || !$to) return '';
  
  $sec_since = ($from - $to) / (60 * 60 * 24);

  $chunks = array(
      array(5, 365.25, 'лет'),
      array(2, 365.25, 'года'),
      array(1, 365.25, 'год'),
      array(5, 30.4, 'месяцев'),
      array(2, 30.4, 'месяца'),
      array(1, 30.4, 'месяц'),
      array(2, 7, 'недели'),
      array(1, 7, 'неделя'),
      array(5, 1, 'дней'),
      array(2, 1, 'дня'),
      array(1, 1, 'день'),
  );

  $sign = $sec_since >= 0 ? 1 : -1;
  $sec_since = $sign * $sec_since;
  
  $count = 0;
  for ($i = 0, $j = count($chunks); $i < $j; $i++) {
      $days = $chunks[$i][0] * $chunks[$i][1];
      if (floor($sec_since / $days) != 0) {
          $name = $chunks[$i][2];
          $count = floor($sec_since / $chunks[$i][1]);
          break;
      }
  }
  
  return ($sign*$count)." ".$name;
}

function date_since($date) {
    if(!$date) return '';
    
    $sec_since = (time() - parse_db_date($date)) / (60 * 60 * 24);

    $chunks = array(
        array(5, 365.25, 'лет'),
        array(2, 365.25, 'года'),
        array(1, 365.25, 'год'),
        array(5, 30.4, 'месяцев'),
        array(2, 30.4, 'месяца'),
        array(1, 30.4, 'месяц'),
        array(2, 7, 'недели'),
        array(1, 7, 'неделя'),
        array(5, 1, 'дней'),
        array(2, 1, 'дня'),
        array(1, 1, 'день'),
    );

    $sign = $sec_since >= 0 ? 1 : -1;
    $sec_since = $sign * $sec_since;
    
    $count = 0;
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $days = $chunks[$i][0] * $chunks[$i][1];
        if (floor($sec_since / $days) != 0) {
            $name = $chunks[$i][2];
            $count = floor($sec_since / $chunks[$i][1]);
            break;
        }
    }

    return $count == 0 ? "сегодня" : ($sign*$count)." ".$name." назад";
}

function time_since($sec_since) {
    $chunks = array(
        array(60 * 60 * 24 * 365 , 'год', 'года', 'лет'),
        array(60 * 60 * 24 * 30 , 'месяц', 'месяца', 'месяцев'),
        array(60 * 60 * 24 * 7, 'неделю', 'недели', 'недель'),
        array(60 * 60 * 24 , 'день', 'дня', 'дней'),
        array(60 * 60 , 'час', 'часа', 'часов'),
        array(60 , 'минута', 'минуты', 'минут'),
        array(1 , 'секунда', 'секунды', 'секунд')
    );

    $sign = $sec_since >= 0 ? 1 : -1;
    $sec_since = $sign * $sec_since;
    
    $chunk = $chunks[count($chunks)-1];
    $name = rus_word($sec_since, $chunk[1], $chunk[2], $chunk[3]);
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        if (($count = floor($sec_since / $seconds)) != 0) {
            $name = rus_word($count, $chunks[$i][1], $chunks[$i][2], $chunks[$i][3]);
            break;
        }
    }

    return ($sign*$count)." ".$name." назад";
}

if (!function_exists('strptime')) { 
  function strptime($date, $format) { 
   $masks = array( 
     'd' => '(?P<d>[0-9]{2})', 
     'm' => '(?P<m>[0-9]{2})', 
     'Y' => '(?P<Y>[0-9]{4})', 
     'H' => '(?P<H>[0-9]{2})', 
     'M' => '(?P<M>[0-9]{2})', 
     'S' => '(?P<S>[0-9]{2})', 
    // usw.. 
   ); 

   $rexep = "#".strtr(preg_quote($format), $masks)."#"; 
   if(!preg_match($rexep, $date, $out)) 
     return false; 
     
   $ret = array( 
     "tm_sec"  => isset($out['S']) ? (int) $out['S'] : 0, 
     "tm_min"  => isset($out['M']) ? (int) $out['M'] : 0, 
     "tm_hour" => isset($out['H']) ? (int) $out['H'] : 0, 
     "tm_mday" => (int) $out['d'], 
     "tm_mon"  => $out['m'] ? $out['m'] : 0, 
     "tm_year" => $out['Y'] > 1900 ? $out['Y'] - 1900 : 0, 
   ); 
   return $ret; 
  }
}
?>