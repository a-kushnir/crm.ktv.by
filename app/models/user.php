<?php
class User extends ModelBase {
  var $table_name = 'users';
  
  var $old_password;
  var $new_password;
  var $password_confirm;
  
  static function try_auth($login, $password)
  {
      global $factory;

      $auth_user_id = null;
      if ($login && $password) {
        $query = "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE login='".mysql_real_escape_string($login)."' and password=MD5('".mysql_real_escape_string($password)."') AND disabled_at IS NULL AND locked_at IS NULL";
        $auth_user_id = $factory->connection->execute_scalar($query);
      }

      if ($auth_user_id) {
      
        $us_id = User::create_session($login, 1, $auth_user_id);
        $query = "SELECT u.*
          FROM users u
          WHERE u.id = '".mysql_real_escape_string($auth_user_id)."'";
        
        $row = $factory->connection->execute_row($query);
        $user = $row ? new User($row) : null;
        if ($user) $user['user_session_id'] = $us_id;
        return $user;
        
      } else {
      
        $query = "SELECT id FROM users WHERE login='".mysql_real_escape_string($login)."'";
        $found_user_id = $factory->connection->execute_scalar($query);
        User::create_session($login, 0, $found_user_id);
        return null;
        
      }
  }
  
  static function password_matches($id, $password)
  {
    global $factory;
    
    $query = "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id='".mysql_real_escape_string($id)."' and password=MD5('".mysql_real_escape_string($password)."')";
    return $factory->connection->execute_scalar($query);    
  }

  function update_password()
  {
    if ($this->new_password) {
      global $factory;

      $query = "UPDATE users SET password = MD5('".mysql_real_escape_string($this->new_password)."') WHERE id='".mysql_real_escape_string($this['id'])."'";
      $factory->connection->execute($query);        
      return true;
    } else {
      return false;
    }
  }
  
  static function create_session($login, $success, $user_id)
  {
    global $factory;
    
    $now = time();
    $attributes = array(
      'login' => $login,
      'success' => $success,
      'ip_address' => $_SERVER['REMOTE_ADDR'],
      'user_agent' => $_SERVER['HTTP_USER_AGENT'],
      'created_at' => date(MYSQL_TIME, $now),
      'updated_at' => date(MYSQL_TIME, $now),
    );
    if ($user_id) $attributes['created_by'] = $user_id;
    
    return $factory->create('user_sessions', $attributes);
  }
  
  static function update_session($session_id)
  {
    global $factory;
    
    $now = time();
    $attributes = array(
      'updated_at' => date(MYSQL_TIME, $now),
    );
    
    return $factory->update('user_sessions', $session_id, $attributes);
  }
  
  static function last_session($session_id)
  {
    global $factory;
    return $factory->connection->execute_row("SELECT ip_address, TIME_TO_SEC(TIMEDIFF(NOW(),updated_at)) seconds_ago
      FROM user_sessions
      WHERE id <> '".mysql_real_escape_string($session_id)."' and created_by = (SELECT created_by FROM user_sessions WHERE id = '".mysql_real_escape_string($session_id)."')
      ORDER BY id DESC
      LIMIT 1");
  }
  
  static function recent_sessions($minutes)
  {
    global $factory;
  
    $timestamp = date(MYSQL_TIME, strtotime('- '.$minutes.' min'));
  
    $rows = $factory->connection->execute("SELECT u.name, TIME_TO_SEC(TIMEDIFF(NOW(),updated_at)) seconds_ago, ua.created_by, ua.updated_at
    FROM user_sessions ua JOIN users u ON ua.created_by = u.id
    WHERE updated_at > '".$timestamp."'
    ORDER BY updated_at DESC");

    $result = array();    
    $users = array();
    while($row = mysql_fetch_array($rows))
      if (!isset($users[$row['created_by']])) {
        $users[$row['created_by']] = true;
        $result[] = new User($row);
      }      
    unset($rows);
    
    return $result;
  }
  
  static function recent_events($minutes)
  {
    global $factory;
  
    $timestamp = date(MYSQL_TIME, strtotime('- '.$minutes.' min'));
  
    $rows = $factory->connection->execute("SELECT u.name, TIME_TO_SEC(TIMEDIFF(NOW(),created_at)) seconds_ago, ue.*
    FROM user_events ue JOIN users u ON ue.created_by = u.id
    WHERE created_at > '".$timestamp."'
    ORDER BY created_at DESC");
    
    $result = array();
    while($row = mysql_fetch_array($rows))
      $result[] = new User($row);
    unset($rows);
    
    return $result;
  }
  
  static function log_event($message, $about = null, $link = null)
  {
    global $factory;
    
    $now = time();
    $attributes = array(
      'user_session_id' => $_SESSION['user_session_id'],
      'message' => $message,
      'about' => $about,
      'link' => $link,
      'created_by' => $_SESSION['user_id'],
      'created_at' => date(MYSQL_TIME, $now)
    );
    
    return $factory->create('user_events', $attributes);
  }
  
  static function normalize_selected_region()
  {
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      if (!isset($selected_region['city_id']) || !$selected_region['city_id']) {
        unset($_SESSION['selected_region']);
      }
      else if (!isset($selected_region['city_district_id']) || !$selected_region['city_district_id']) { 
        unset($_SESSION['selected_region']['city_district_id']);
        unset($_SESSION['selected_region']['city_microdistrict_id']);
      }
      else if (!isset($selected_region['city_microdistrict_id']) || !$selected_region['city_microdistrict_id']) { 
        unset($_SESSION['selected_region']['city_microdistrict_id']);
      }
    }
    if (isset($_SESSION['selected_region'])) $_SESSION['selected_region']['name'] = User::get_selected_region_name();
  }
  
  static function save_selected_region()
  {
    if (isset($_SESSION['user_id'])) {
      global $factory;
    
      $selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null;
      $attributes = array(
        'selected_city_id' => isset($selected_region['city_id']) ? $selected_region['city_id'] : null,
        'selected_city_district_id' => isset($selected_region['city_district_id']) ? $selected_region['city_district_id'] : null,
        'selected_city_microdistrict_id' => isset($selected_region['city_microdistrict_id']) ? $selected_region['city_microdistrict_id'] : null,
      );
      
      return $factory->update('users', $_SESSION['user_id'], $attributes);
    }
  }
  
  static function get_selected_region_name()
  {
    $result = '';
    
    global $factory;
    if ($selected_region = isset($_SESSION['selected_region']) ? $_SESSION['selected_region'] : null) {
      $result = $factory->connection->execute_scalar("select name from cities where id = '".mysql_real_escape_string($selected_region['city_id'])."'");
      if (isset($selected_region['city_district_id'])) {
        $result .= ', '.$factory->connection->execute_scalar("select name from city_districts where id = '".mysql_real_escape_string($selected_region['city_district_id'])."'");
        if (isset($selected_region['city_microdistrict_id'])) {
           $result .= ', '.$factory->connection->execute_scalar("select name from city_microdistricts where id = '".mysql_real_escape_string($selected_region['city_microdistrict_id'])."'");
        }
      }
    }
    
    return $result;
  }

  function load_attributes_for_change_password($attributes, $id)
  {
    $this['id'] = $id;
    $this->old_password = get_object_value($attributes, 'old_password');
    $this->new_password = get_object_value($attributes, 'new_password');
    $this->password_confirm = get_object_value($attributes, 'password_confirm');
  }
  
  function load_attributes_for_worker($attributes, $worker)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'login',
        'new_password',
        'password_confirm',
        'enabled',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = isset($worker['user_id']) ? $worker['user_id'] : null;
    $this['name'] = $worker['first_name'].' '.$worker['last_name'];

    // set password
    if (!$this['id']) $this['password'] = '<invalid>';
    $this->new_password = $this['new_password'];
    $this->password_confirm = $this['password_confirm'];
    unset($this['new_password'], $this['password_confirm']);
    
    // enable or disable access
    $this['disabled_at'] = get_object_value($attributes, 'enabled') == '1' ? null : date(MYSQL_TIME, time());
    unset($this['enabled']);
    
    // set worker role
    if (!$this['id']) $this['role_id'] = 2; // Worker // TEMP!!
  }
  
  function password_valid($check_old_password)
  {
    if ($check_old_password && $this->old_password == '') $this->errors['old_password'] = ERROR_BLANK;
    if ($this->new_password == '') $this->errors['new_password'] = ERROR_BLANK;
    if ($this->password_confirm == '') $this->errors['password_confirm'] = ERROR_BLANK;
    if ($this->new_password != $this->password_confirm) $this->errors['password_confirm'] = ERROR_PASSWORD_CONFIRM;
    if (count($this->errors) == 0 && $this->new_password != $this->password_confirm) $this->errors['password_confirm'] = ERROR_PASSWORD_CONFIRM;
    if (count($this->errors) == 0 && mb_strlen($this->new_password, 'UTF-8') < 6) $this->errors['new_password'] = ERROR_NEW_PASSWORD;
    if ($check_old_password && count($this->errors) == 0 && !User::password_matches($this['id'], $this->old_password)) $this->errors['old_password'] = ERROR_OLD_PASSWORD;
    return count($this->errors) == 0;
  }
  
  function valid_for_worker() {
    global $factory;
    $this->errors = array();

    if ((!$this['id'] && $this['login']) ||
      ($this['id'] && ($this->new_password || $this->password_confirm))) {
      
      $this->password_valid(false);
    }

    if ($this['id'] || $this['login'] || $this->new_password || $this->password_confirm) {
      if (!$this['login']) $this->errors['login'] = ERROR_BLANK;
      else if (count($this->errors) == 0) {
        $same_login = $factory->connection->execute_scalar($this['id'] ?
        "SELECT id FROM users WHERE login = '".mysql_real_escape_string($this['login'])."' AND id <> '".mysql_real_escape_string($this['id'])."'" :
        "SELECT id FROM users WHERE login = '".mysql_real_escape_string($this['login'])."'");
        if ($same_login) $this->errors['login'] = ERROR_EXIST;
      }
    }
  
    return count($this->errors) == 0;
  }

  static function cameras()
  {
    $query = "SELECT name, url FROM cameras WHERE active = true";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new User($row);
    unset($rows);
    
    return $result;
  }
  
}

?>