<?php
include('../lib/engine/startup.php');
include('../lib/data/cleanup.php');

$cleanup_trace = cleanup_data();
foreach($cleanup_trace as $et)
  echo $et."\r\n";
?>