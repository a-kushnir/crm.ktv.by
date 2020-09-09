<?php

function format_datetime($value, $format = DATETIME_FORMAT) {
  if ($value) {
    $ts = strtotime($value);
    return date($format, $ts);
  } else {
    return null;
  }
}

function format_float($value, $decimals = 0, $options = null) {
  $dec_point = get_object_value($options, 'dec_point', defined('NUMBER_DEC_POINT') ? NUMBER_DEC_POINT : '');
  $thousands_sep = get_object_value($options, 'thousands_sep', defined('NUMBER_THOUSANDS_SEP') ? NUMBER_THOUSANDS_SEP : '');
  return number_format($value, $decimals, $dec_point, $thousands_sep);
}

function prepare_float($value) {
	$dotPos = strrpos($value, '.');
    $commaPos = strrpos($value, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : 
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
   
    if (!$sep) {
        return floatval(preg_replace("/[^0-9-]/", "", $value));
    } 

	return preg_replace("/[^0-9-]/", "", substr($value, 0, $sep)) . '.' . preg_replace("/[^0-9]/", "", substr($value, $sep+1, strlen($value)));
}

function isfloat($value) {
	return is_numeric(prepare_float($value));
}

function tofloat($value) {
    return floatval(prepare_float($value));
}

function format_percent($value, $decimals = 2) {
  return format_float($value * 100, $decimals).'%';
}

function format_money($value, $options = null) {
  if (!$options) $options = array();
  
  $prefix = get_object_value($options, 'prefix', defined('CURRENCY_PREFIX') ? CURRENCY_PREFIX : '');
  $suffix = get_object_value($options, 'suffix', defined('CURRENCY_SUFFIX') ? CURRENCY_SUFFIX : '');
  $decimals = get_object_value($options, 'decimals', defined('CURRENCY_DECIMALS') ? CURRENCY_DECIMALS : '');
  
  $dec_point = get_object_value($options, 'dec_point', defined('NUMBER_DEC_POINT') ? NUMBER_DEC_POINT : '');
  $thousands_sep = get_object_value($options, 'thousands_sep', defined('NUMBER_THOUSANDS_SEP') ? NUMBER_THOUSANDS_SEP : '');
  
  return $prefix.number_format($value, $decimals, $dec_point, $thousands_sep).$suffix;
}

function js_date($value) {
  if ($value) {
    $ts = strptime($value, MYSQL_DATE);
    return 'new Date('.($ts['tm_year'] + 1900).','.($ts['tm_mon'] - 1).','.$ts['tm_mday'].')';
  } else {
    return null;
  }
}

function format_date($value, $format = DATE_FORMAT) {
  if ($value) {
    $ts = strptime($value, MYSQL_DATE);
    $ts = mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900));
    return date($format, $ts);
  } else {
    return null;
  }
}

function human_date($value) {
  if ($value) {
    $result = null;

    global $layout;
    if ($layout == 'print') {
      $result = format_date($value);
    } else {
      $ts = strtotime($value);
      if (date("Y", $ts) != date("Y")) {
        $result = format_date($value);
      } else if ($ts >= mktime(0, 0, 0, date('m'), date('d')-1, date('Y')) && $ts < mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
        $result = 'вчера'; 
      } else if ((date("m", $ts) != date("m")) || (date("d", $ts) != date("d"))) {
        global $SHORT_MONTHS;
        $result = date("j", $ts).' '.$SHORT_MONTHS[date("m", $ts)-1];
      } else {
        $result = 'сегодня';
      }
    }

    return '<span class="datetime" title="'.format_date($value).' ('.date_since($value).')">'.$result.'</span>';
  } else {
    return null;
  }
}

function human_datetime($value, $format = DATETIME_FORMAT) {
  if ($value) {
    $result = null;

    global $layout;
    if ($layout == 'print') {
      $result = format_datetime($value, $format);
    } else {
      $ts = strtotime($value);
      if (date("Y", $ts) != date("Y")) {
        $result = format_date($value);
      } else if ((date("m", $ts) != date("m")) || (date("d", $ts) != date("d"))) {
        global $SHORT_MONTHS;
        $result = date("j", $ts).' '.$SHORT_MONTHS[date("m", $ts)-1];
      } else {
        $result = format_datetime($value, SHORT_TIME_FORMAT);
      }
    }

    return '<span class="datetime" title="'.format_datetime($value).' ('.date_since($value).')">'.$result.'</span>';
  } else {
    return null;
  }
}

function parse_db_date($value) {
  if ($value) {
    $ts = strptime($value, MYSQL_DATE);
    return mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900));
  } else {
    return null;
  }
}

function parse_db_datetime($value) {
  if ($value) {
    return strtotime($value);
  } else {
    return null;
  }
}

function prepare_date($value) {
  if ($value) {
    $value = str_replace(',','.',$value);
    $ts = strptime($value, DATE_FORMAT);
    $ts = mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900));
    return date(MYSQL_DATE, $ts);
  } else {
    return null;
  }
}

function format_month($value) {
  if ($value) {
    return date(MONTH_FORMAT, parse_db_date($value));
  } else {
    return null;
  }
}

function format_backtrace($backtrace, $start_from = 0)
{
  $result = array();

  $index = 0;
  foreach($backtrace as $backline) {
    if ($index >= $start_from)
      $result[] = isset($backline['file']) && isset($backline['line']) ? 
        (starts_with($backline['file'], APP_ROOT) ? 'APP_ROOT'.substr($backline['file'], strlen(APP_ROOT)) : $backline['file']).':'.$backline['line'].': in `'.$backline['function'].'`' :
        'in `'.$backline['function'].'`';
    $index++;
  }
  
  return implode("\n", $result);
}
?>