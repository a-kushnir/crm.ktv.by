<?php
define('RTF_NEW_PAGE','\\rtf1 \\page');

function ordutf8($string, &$offset) {
  $code = ord(substr($string, $offset,1)); 
  if ($code >= 128) {        //otherwise 0xxxxxxx
     if ($code < 224) $bytesnumber = 2;                //110xxxxx
     else if ($code < 240) $bytesnumber = 3;        //1110xxxx
     else if ($code < 248) $bytesnumber = 4;    //11110xxx
     $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
     for ($i = 2; $i <= $bytesnumber; $i++) {
         $offset ++;
         $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
         $codetemp = $codetemp*64 + $code2;
     }
     $code = $codetemp;
  }
  $offset += 1;
  if ($offset >= strlen($string)) $offset = -1;
  return $code;
}

function rtf_encode($value) {
  if (!$value) return '';
  
  $result = '';
  
  $offset = 0;
  while ($offset >= 0) {
    $char = ordutf8($value, $offset);
    
    # UTF8 -> ASCII
    if ($char <= 128) {
      $char_code = $char;
    } else if ($char >= 1040 && $char <= 1103) {
      $char_code = $char - 848;
    } else if ($char == 1025) {
      $char_code = 168;
    } else if ($char == 1105) {
      $char_code = 184;
    } else {
      $char_code = null;
    }

    # ASCII -> RTF
    if ($char_code) {
      if ($char <= 128) {
        $result .= chr($char_code);
      } else {
        $result .= "\\'".dechex($char_code);
      }
    }
  }
    
  return $result;
}

?>