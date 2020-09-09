<?php

function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function ends_with($haystack, $needle) {
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function first_word($text) {
  $words = explode(' ',trim($text));
  return $words[0];
}

function last_word($text) {
  $words = explode(' ',trim($text));
  return $words[count($words)-1];
}

if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) { 
  function mb_ucfirst($string) {  
    $string = mb_ereg_replace("^[\ ]+","", $string);  
    $string = mb_strtoupper(mb_substr($string, 0, 1, "UTF-8"), "UTF-8").mb_substr($string, 1, mb_strlen($string), "UTF-8" );  
    return $string;  
  }  
}

function mb_trim( $string ) { 
     $string = preg_replace( "/(^\s+)|(\s+$)/us", "", $string ); 
     return $string; 
}

function rand_string($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
{
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

function constanize($string) {
  $result = str_replace('_', ' ', $string); // "hello_world" -> "hello world"
  $result = ucwords($result);               // "hello world" -> "Hello World"
  return str_replace(' ', '', $result);     // "Hello World" -> "HelloWorld"
}

function insert_arr($array, $pos, $value)
{
  $result = array_merge(array_slice($array, 0 , $pos), array($value), array_slice($array,  $pos));
  return $result;
}
?>