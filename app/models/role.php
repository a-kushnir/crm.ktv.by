<?php
class Role extends ModelBase {
  var $table_name = 'roles';
  
  static function first($id) {
    global $factory;
    $query = "SELECT r.*, cu.name creator, uu.name updator
FROM roles r
LEFT JOIN users cu ON r.created_by = cu.id LEFT JOIN users uu ON r.updated_by = uu.id
WHERE r.id = '".mysql_real_escape_string($id)."'
LIMIT 1";
    $row = $factory->connection->execute_row($query);
    return $row ? new Role($row) : null;
  }
  
  static function all() {
    global $factory;
    $query = "SELECT r.* FROM roles r ORDER BY r.name";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Role($row);
    unset($rows);
    
    return $result;
  }

  function has_users() {
    global $factory;
    $query = "SELECT 1 FROM users WHERE role_id = '".mysql_real_escape_string($this['id'])."'";
    return $factory->connection->execute_scalar($query);
  }
  
  function users($limit = 5) {
    global $factory;
    
    $names = "SELECT login 
FROM users
WHERE role_id = '".mysql_real_escape_string($this['id'])."'
LIMIT ".mysql_real_escape_string($limit);

    $count = "SELECT COUNT(1)
FROM users
WHERE role_id = '".mysql_real_escape_string($this['id'])."'";

    return array(
      'names' => $factory->connection->execute_column($names),
      'count' => $factory->connection->execute_scalar($count),
    );
  }
  
  static function get_capability_ids($role_id)
  {
    global $factory;
    $query = "SELECT rr.capability_id
FROM role_rights rr
WHERE rr.role_id = '".mysql_real_escape_string($role_id)."'";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[$row['capability_id']] = true;
    unset($rows);
    
    return $result;
  }
  
  static function get_capabilities($role_id)
  {
if (defined('CHECK_CAPABILITY_EXISTENCE') && CHECK_CAPABILITY_EXISTENCE == 'true') {
    $query = "SELECT c.lookup_code, rr.id
FROM capabilities c
LEFT JOIN role_rights rr ON c.id = rr.capability_id AND rr.role_id = '".mysql_real_escape_string($role_id)."'";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[$row['lookup_code']] = ($row['id'] ? true : false);
    unset($rows);
    
    return $result;
} else { // don't check capability existence.
    $query = "SELECT c.lookup_code, rr.id
FROM capabilities c
LEFT JOIN role_rights rr ON c.id = rr.capability_id
WHERE rr.role_id = '".mysql_real_escape_string($role_id)."'";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[$row['lookup_code']] = true;
    unset($rows);
    
    return $result;
}
  }
  
  static function get_menu($role_id)
  {
    $query = "SELECT mi.url, cg.name, rmi.hide_in_other
FROM role_menu_items rmi
JOIN menu_items mi ON mi.id = rmi.menu_item_id
JOIN capability_groups cg ON mi.capability_group_id = cg.id
JOIN role_rights rr ON rr.capability_id = mi.capability_id
WHERE rmi.role_id = '".mysql_real_escape_string($role_id)."' and rmi.role_id = rr.role_id
ORDER BY rmi.hide_in_other, rmi.position";
    
    global $factory;
    return $factory->connection->execute_table($query);
  }
  
  static function get_menu_items($role_id)
  {
    $query = "SELECT mi.capability_group_id, rmi.*
FROM role_menu_items rmi
JOIN menu_items mi ON mi.id = rmi.menu_item_id
WHERE rmi.role_id = '".mysql_real_escape_string($role_id)."'";
  
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[$row['capability_group_id']] = $row;
    unset($rows);
    
    return $result;
  }
  
  function validate()
  {
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;
  }
  
  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'name',
        'description',
      )
    );
    
    $this->map_attributes($attributes);
    
    $this['id'] = $id;
    
    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  static function all_capabilities($role_id)
  {
    $query = "SELECT c.*, cg.name `group`
FROM capabilities c
JOIN capability_groups cg ON cg.id = c.capability_group_id
LEFT JOIN menu_items mi ON cg.id = mi.capability_group_id
LEFT JOIN role_menu_items rmi ON mi.id = rmi.menu_item_id AND rmi.role_id = '".mysql_real_escape_string($role_id)."'
ORDER BY IFNULL(rmi.position, cg.position), cg.name, cg.id, c.position, c.name";
    global $factory;
    return $factory->connection->execute_table($query);
  }
  
  function update_rights($rights)
  {
    global $factory;
    $today = date(MYSQL_TIME, time());
  
    // DELETE
    $condition = "role_id = '".mysql_real_escape_string($this['id'])."'";
    if ($rights) {
      $safe_rights = array();
      foreach ($rights as $key => $value)
        if ($value == '1')
          $safe_rights[] = mysql_real_escape_string($key);
      if (count($safe_rights) > 0) 
        $condition .= " AND capability_id NOT IN ('".implode($safe_rights, "','")."')";
    }
    $deleted = $factory->delete_all('role_rights', $condition);

    // INSERT
    $inserted = 0;
    $existing_caps = Role::get_capability_ids($this['id']);
    if ($rights)
      foreach ($rights as $key => $value)
        if ($value == '1' && !isset($existing_caps[$key])) {
          $factory->create('role_rights', array(
            'role_id' => $this['id'],
            'capability_id' => $key,
            'created_at' => $today,
            'created_by' => $_SESSION['user_id'],
          ));
          $inserted++;
        }

    return array('deleted' => $deleted, 'inserted' => $inserted);
  }
  
  function update_menu($menu_items)
  {
    global $factory;
    $factory->delete_all('role_menu_items', "role_id = '".mysql_real_escape_string($this['id'])."'");
    
    foreach($menu_items as $capability_group_id => $menu_item) {

    $query = "SELECT id FROM menu_items WHERE capability_group_id = '".mysql_real_escape_string($capability_group_id)."' LIMIT 1";
      $menu_item_id = $factory->connection->execute_scalar($query);
      if (!$menu_item_id) continue;

      $factory->create('role_menu_items', array(
        'role_id' => $this['id'],
        'menu_item_id' => $menu_item_id,
        'position' => $menu_item['position'],
        'hide_in_other' => (isset($menu_item['hide_in_other']) && $menu_item['hide_in_other'] == '1') ? 1 : 0,
      ));
      
    }
  }
}
?>