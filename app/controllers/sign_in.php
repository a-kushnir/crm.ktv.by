<?php
class SignInController extends ApplicationController
{
  var $suppress_authorization = true;

  function index()
  {
    $this->title = "Вход";;

    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    unset($_SESSION['user_session_id']);
    unset($_SESSION['ip_address']);
    
    session_destroy();
  }

  function create()
  {
    $this->title = "Вход";
    $this->title = null;

    $user = User::try_auth($_POST['login'], $_POST['password']);
    if ($user != null) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role_id'] = $user['role_id'];
      $_SESSION['user_name'] = $user['name'];
      $_SESSION['user_role'] = $user['role'];
      $_SESSION['user_session_id'] = $user['user_session_id'];
      $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    
      $_SESSION['selected_region'] = array(
        'city_id' => $user['selected_city_id'],
        'city_district_id' => $user['selected_city_district_id'],
        'city_microdistrict_id' => $user['selected_city_microdistrict_id']
      );
      User::normalize_selected_region();

      load_capabilities();
      
      $message = 'Добро пожаловать, '.$_SESSION['user_name'].'!';
      if (has_access('home/last_session')) {
        $last_sesssion = User::last_session($user['user_session_id']);
        echo '1';
        if ($last_sesssion) $message .= " В последний раз вы входили <b>".time_since(floor($last_sesssion['seconds_ago'])).'</b> назад с '.($last_sesssion['ip_address'] == $_SERVER['REMOTE_ADDR'] ? '<b>этого же</b>' : '<b>другого</b>').' адреса.';
      }
      
      flash_notice($message);
      $url = get_field_value('for');
      redirect_to($url ? base64_decode($url) : '/home');
    }
    else
    {
      flash_alert('Неправильное имя пользователя или пароль');
      $url = get_field_value('for');
      redirect_to('/sign_in'.($url ? '?for='.$url : ''));
    }
  }

}
?>