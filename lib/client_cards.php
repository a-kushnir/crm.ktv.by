<?php
define('MEMOS_PER_PAGE', 6);

function sort_client_memos($records) {
  $total_records = count($records);
  $pages = floor($total_records / MEMOS_PER_PAGE);
  
  $result = array();
  for($page = 0; $page < $pages; $page++)
    for($pos = 0; $pos < MEMOS_PER_PAGE; $pos++) {
      $index = $page + $pos * ($pages + 1);
      if ($index < $total_records)
        $result[$page * MEMOS_PER_PAGE + $pos] = $records[$index];
    }
    
  return $result;
}

function generate_client_memos($records) {
  $keys = array_keys($records);
  $total_records = end($keys);
  
  $template_path = $_SERVER['DOCUMENT_ROOT'].'/lib/files/client_cards.rtf';
  $template_page = implode('', file($template_path));

  $result = array();
  $first_page = true;
  $index = -1;
  $record = null;
  $replaces = null;
  
  while($index <= $total_records) {
    $page = $template_page;

    $first_on_page = true;
    do {
      if ($first_page || !$first_on_page) {
        $index += 1;
        $record = isset($records[$index]) ? $records[$index] : null;
        $replaces = array(
          rtf_encode('Счет_абонента')  => rtf_encode($record ? $record['lookup_code'] : ''),
          rtf_encode('Фио_абонента')   => rtf_encode($record ?  format_name($record) : ''),
          rtf_encode('Адрес_абонента') => rtf_encode($record ? format_address($record, true) : '')
        );
      }      
      
      $found = false;
      foreach($replaces as $key => $value) {
        $page = first_replace($key, $value, $page, $found);
      }
        
        
      $first_on_page = false;
    } while($found);

    // Join files
    if (!$first_page) {
      $page = substr($page,1); // remove first char
    }
    if ($index < $total_records) {
      $first_page = false;
      $page = substr($page,0,strlen($page)-1); // remove last char
      $page .= RTF_NEW_PAGE;
    }
    
    $result[] = $page;
  }
  
  return implode('', $result);
}

function first_replace($search,$replace,$subject,&$found){
  $pos = strpos($subject,$search);
  if ($pos !== false) {
    $found = true;
    return substr_replace($subject,$replace,$pos,strlen($search));
  }
  //$found = false;
  return $subject;
}
?>