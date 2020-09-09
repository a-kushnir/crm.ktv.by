<?php
abstract class ControllerBase
{
  public function __construct(
    $factory,
    $controller,
    $action,
    $routing_params
  ) {
    // Can be overridden by routing_params
    $this->layout = 'application';
    $this->read_only = $action == 'index' || $action == 'show';
    $this->title = APPLICATION_NAME;
    $this->subtitle = null;
    
    // Create custom route variables (such as id, format etc)
    foreach($routing_params as $var => $val)
      $this->{$var} = $val;

    // Cannot be overridden by routing_params
    $this->factory = $factory;
    $this->controller = $controller;
    $this->action = $action;
  }
}
?>