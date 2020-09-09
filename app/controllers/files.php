<?php
class FilesController extends ApplicationController
{
  function download()
  {
    if (isset($this->id) && isset($this->filename)) {
      $attachment = Attachment::load($this->id);
      if ($attachment && $attachment['name'] == $this->filename) {
        $file_path = FILE_STORE.'/'.$attachment['path'];
        if (is_file($file_path)) {
          download_file_by_name($file_path, $attachment['name'], true, $attachment['mime']);
        } else {
          show_404();
        }
      } else {
        show_404();
      }
    } else {
      show_404();
    }
  }

  function upload()
  {
    if(isset($_FILES['attachments_file']) && 
      $_FILES['attachments_file']['error'] == UPLOAD_ERR_OK && 
      is_uploaded_file($_FILES['attachments_file']['tmp_name']))
    {
      $uploaded_file = $_FILES['attachments_file'];    

      $extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
      $file_path = date('Y').'/'.date('m').'/'.date('d');
      
      make_path(FILE_STORE.'/'.$file_path);
      
      $attachment = new Attachment(array(
        'owner_type' => get_field_value('for'),
        'owner_id' => get_field_value('id'),
        'name' => $uploaded_file['name'],
        'path' => $file_path,
        'size' => $uploaded_file['size'],
        'mime' => $uploaded_file['type'],
      ));
      $attachment->add_timestamp();
      $attachment->add_userstamp();
      $attachment_id = $attachment->create();
      
      $file_path = $file_path.'/'.$attachment_id;
      $attachment['path'] = $file_path;
      $attachment->update();
      
      if(move_uploaded_file($uploaded_file['tmp_name'], FILE_STORE.'/'.$file_path)){
        echo '{"id":"'.$attachment['id'].'", "name":"'.$attachment['name'].'"}';
        exit;
      }
    }

    if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500); }
    exit;
  }
  
  function destroy()
  {
    if (isset($this->id) && isset($this->filename)) {
      $attachment = Attachment::load($this->id);
      if ($attachment && $attachment['name'] == $this->filename) {
        global $factory;
        $factory->deactivate('attachments', $this->id);
        exit;
      } else {
        if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404); }
      }
    } else {
      if (!headers_sent()) { header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404); }
    }
  }
}
?>