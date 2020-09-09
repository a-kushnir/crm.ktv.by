<?php
class Routing extends ArrayObject
{
  var $routes = array();

  var $controller;
  var $action;
  var $params;

  function parse($method, $uri)
  {    
    foreach($this->routes as $route) {
      $_method = $route[0];
      $pattern = $route[3];
      $destination = $route[2];
    
      if ($_method && $method != $_method)
        continue;
    
      if (preg_match($pattern, $uri)) {
        preg_match_all($pattern, $uri, $matches);
      
        if (isset($matches['controller']))
          $this->controller = $matches['controller'][0];
        else if ($destination)
          $this->controller = Routing::get_controller_from_dst($destination);
              
      if (isset($matches['action']))
          $this->action = $matches['action'][0];
        else if ($destination)
          $this->action = Routing::get_action_from_dst($destination);
      }
      
      if ($this->controller && file_exists(APP_ROOT.'/app/controllers/'.$this->controller.'.php'))
        break;
      else
        $this->controller = null;
    }
  
    if (!$this->action) $this->action = 'index';
    if (!$this->controller) $this->action = null;
    
    $this->params = array();
    if ($this->controller)
      foreach($matches as $key => $value)
        if (!is_int($key) && $key != 'controller' && $key != 'action')
          $this->params[$key] = $value[0];
    
    /*
    echo $this->controller.'#'.$this->action.' ';
    echo var_dump($this->params);
    die;
    */
    
    return !!$this->controller;
  }
  
  private static function get_controller_from_dst($destination)
  {
    if (strpos($destination, '#') !== false) {
      $arr = explode('#', $destination);
      return $arr[0];
    } else {
      return $destination;
    }
  }
  
  private static function get_action_from_dst($destination)
  {
    
    if (strpos($destination, '#') !== false) {
      $arr = explode('#', $destination);
      return $arr[1];
    } else {
      return null;
    }
  }
  
  function root($destination = null)
  {
    $this->routes[] = array(null, '', $destination, '/^$/');
  }
  
  function get($pattern, $destination = null, $constraints = null)
  {
    $this->routes[] = array('GET', $pattern, $destination, Routing::convert_to_regexp($pattern, $constraints));
  }
  
  function post($pattern, $destination = null, $constraints = null)
  {
    $this->routes[] = array('POST', $pattern, $destination, Routing::convert_to_regexp($pattern, $constraints));
  }
  
  function delete($pattern, $destination = null, $constraints = null)
  {
    $this->routes[] = array('DELETE', $pattern, $destination, Routing::convert_to_regexp($pattern, $constraints));
  }
  
  function match($pattern, $destination = null, $constraints = null)
  {
    $this->routes[] = array(null, $pattern, $destination, Routing::convert_to_regexp($pattern, $constraints));
  }
  
  private static function convert_to_regexp($pattern, $constraints)
  {
    $result = '/'.$pattern;
    $result = str_replace('.','\.',$result);
    $result = str_replace('/','\/',$result);
    $result = str_replace('(','(:?',$result);
    $result = str_replace(')',')?',$result);
    
    if ($constraints)
      foreach ($constraints as $name => $regex)
        $result = preg_replace('/\:'.$name.'/','(?<'.$name.'>'.$regex.')',$result);
    
    $result = preg_replace('/\:([a-zA-Z0-9]+)/','(?<$1>.+)',$result);
    $result = '/^'.$result.'$/U';
    return $result;
  }
  
  function offsetExists($offset) { return isset($this->params) && isset($this->params[$offset]); }
  function offsetGet($offset) { return isset($this->params) ? $this->params[$offset] : null; }
  function offsetSet($offset, $value) { if(!isset($this->params)) $this->params = array(); $this->params[$offset] = $value; }
  function offsetUnset($offset) { if (isset($this->params)) unset($this->params[$offset]); }
}

$routing = new Routing();
?>