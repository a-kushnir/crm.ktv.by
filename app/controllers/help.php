<?php
class HelpController extends ApplicationController
{
  function index()
  {
    $this->title = 'Помощь';
    $this->load_attachments('Help');
  }
}
?>