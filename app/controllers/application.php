<?php
class ApplicationController extends ControllerBase
{
  var $mobile_version = false;
  var $javascripts = array();

  function load_attachments($name, $id = null) {
    $this->attachments_for = $name;
    $this->attachments_id = $id;
    $this->attachments = Attachment::load(null, $this->attachments_for, $this->attachments_id);
  }
}
?>