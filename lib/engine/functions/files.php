<?php
function make_path($pathname, $is_filename=false){
  if($is_filename){
    $pathname = substr($pathname, 0, strrpos($pathname, '/'));
  }

  // Check if directory already exists
  if (is_dir($pathname) || empty($pathname)) {
    return true;
  }

  // Ensure a file does not already exist with the same name
  $pathname = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pathname);
  if (is_file($pathname)) {
    trigger_error('mkdirr() File exists', E_USER_WARNING);
    return false;
  }

  // Crawl up the directory tree
  $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
  if (make_path($next_pathname, $is_filename)) {
    if (!file_exists($pathname)) {
      return mkdir($pathname, $is_filename);
    }
  }

  return false;
}

function download_file_by_name($full_path, $file_name = null, $is_attachment = false, $mime_type = null) {
  //$full_path = $_SERVER['DOCUMENT_ROOT'].'/'.$full_path;
  if ($fd = fopen($full_path, "r")) {
    $fsize = filesize($full_path);
    $path_info = pathinfo($full_path);
    
    if (!$file_name) $file_name = $path_info["basename"];
    if (!$mime_type) $mime_type = mime_type($file_name);
    
    header("Content-type: ".$mime_type);
    header("Content-Disposition: ".($is_attachment ? "attachment; " : "")."filename=\"".$file_name."\"");
    header("Content-length: ".$fsize);
    header("Cache-control: private"); // proxy didn't cache this file
    
    while(!feof($fd)) {
      $buffer = fread($fd, 2048);
      echo $buffer;
    }
    
    fclose ($fd);
  }
  exit;
}

function download_file_by_content($file_content, $file_name = null, $is_attachment = false, $mime_type = null) {
  if (is_array($file_content)) $file_content = implode('', $file_content);

  $fsize = strlen($file_content);
  if (!$mime_type) $mime_type = mime_type($file_name);
  
  header("Content-type: ".$mime_type);
  header("Content-Disposition: ".($is_attachment ? "attachment; " : "")."filename=\"".$file_name."\"");
  header("Content-length: ".$fsize);
  header("Cache-control: private"); // proxy didn't cache this file
  
  echo $file_content;
  exit;
}

function format_file_size($bytes, $precision = 2) {
  $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

  $bytes = max($bytes, 0); 
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
  $pow = min($pow, count($units) - 1); 

  $bytes /= pow(1024, $pow);
  //$bytes /= (1 << (10 * $pow)); 

  return str_replace('.', ',', round($bytes, $precision) . ' ' . $units[$pow]);
}
?>