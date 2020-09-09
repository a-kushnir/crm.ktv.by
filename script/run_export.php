<?php
include('../lib/engine/startup.php');
include('../lib/data/export.php');

$export_trace = export_data();
foreach($export_trace as $et)
  echo $et."\r\n";
?>