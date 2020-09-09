<?php
class Benchmark
{
  var $view_time = 0;
  var $query_time = 0;
  var $action_time = 0; 
  var $total_time = 0; 
  
  static function begin()
  {
    return microtime(true);
  }
  
  static function end($start_time)
  {
    return microtime(true) - $start_time;
  }
}

$benchmark = new Benchmark();
?>