<?php
class ProfileController extends ApplicationController
{
  function index()
  {
    $this->title = 'Профиль';
    $this->user = new User();
  }

  function create()
  {
    $this->title = 'Профиль';

    $user_id = $_SESSION['user_id'];

    if ($_POST) {
      $this->user = new User();
      $this->user->load_attributes_for_change_password($_POST['user'], $user_id);
      
      if ($this->user->password_valid(true)) {
        $this->user->update_password();
        flash_notice('Профиль был обновлен');
      }
      
    } else {
      $this->user = new User();
    }
    
    render_action('index');
  }
}
?>