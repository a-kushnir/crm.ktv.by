<?php
class SignOutController extends ApplicationController
{
  function index()
  {
    if(!isset($_SESSION)) session_start();
    session_destroy();
    setcookie("PHPSESSID","",time()-3600,"/");
    redirect_to('index');
  }
}
?>