<?php
class Amplifier extends ModelBase {
  var $table_name = 'amplifiers';
  var $amp_channels = array();
  
  static function all($house_id)
  {
    global $factory;
    $query = "SELECT * FROM amplifiers WHERE house_id = '".mysql_real_escape_string($house_id)."' ORDER BY `name`, id";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Amplifier($row);
    unset($rows);
    
    return $result;
  }
  
  static function first($amplifier_id, $house_id = null)
  {
    global $factory;
    $query = "SELECT a.*, cu.name creator, uu.name updator
    FROM amplifiers a 
    LEFT JOIN users cu ON a.created_by = cu.id LEFT JOIN users uu ON a.updated_by = uu.id
    WHERE a.id = '".mysql_real_escape_string($amplifier_id)."'";
    if ($house_id) $query .= " AND a.house_id = '".mysql_real_escape_string($house_id)."'";
    
    $row = $factory->connection->execute_row($query);
    return $row ? new Amplifier($row) : null;
  }
  
  function validate()
  {
    global $factory;
  
    if ($this['id'] == null && $this['house_id'] == null) $this->errors['house_id'] = ERROR_BLANK;
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;
    if ($this['floor'] == null) $this->errors['floor'] = ERROR_BLANK;
    if ($this['entrance'] == null) $this->errors['entrance'] = ERROR_BLANK;
  }
  
  function load_attributes($attributes, $id = null, $house_id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'house_id',
        'parent_amplifier_id',
        'floor',
        'entrance',
        'name',
        'description',
      )
    );
    
    $this->map_attributes($attributes);
    
    if ($id) $this['id'] = $id;
    if ($house_id) $this['house_id'] = $house_id;
    if (!$this['parent_amplifier_id']) $this['parent_amplifier_id'] = null;
    
    $this->add_userstamp();
    $this->add_timestamp();
  }
  
  static function entrances($max_entrance, $include_blank = null)
  {
    $result = array();
    
    if ($include_blank !== null) $result[] = array(null,$include_blank);
    for($i = 0; $i < $max_entrance; $i++)
      $result[] = array($i+1,$i+1);
    
    return $result;
  }
  
  static function floors($max_floor, $include_blank = null)
  {
    $result = array();
    
    if ($include_blank !== null) $result[] = array(null,$include_blank);
    $result[] = array(-1,'Подвал');
    for($i = 0; $i < $max_floor; $i++)
      $result[] = array($i+1,$i+1);
    $result[] = array(99,'Крыша');
    
    return $result;
  }
 
  static function parent_amplifiers($house_id, $amplifier = null, $read_only = false, $include_blank = null)
  {
    $query = $read_only ? 
      "SELECT a.*,
  streets.id street_id, streets.name street,
  h1.id house_id, h1.house, h1.building
FROM amplifiers a
JOIN houses h1 ON a.house_id = h1.id
LEFT JOIN streets ON streets.id = h1.street_id
JOIN amplifiers a2 ON a.id = a2.parent_amplifier_id
WHERE a2.id = '".mysql_real_escape_string($amplifier['id'])."'"
    : "SELECT a.*,
  streets.id street_id, streets.name street,
  h1.id house_id, h1.house, h1.building
FROM amplifiers a
JOIN houses h1 ON a.house_id = h1.id
LEFT JOIN streets ON streets.id = h1.street_id
JOIN houses h2 ON h1.city_id = h2.city_id
  AND IFNULL(h1.city_district_id, 0) = IFNULL(h2.city_district_id, 0)
  AND IFNULL(h1.city_microdistrict_id, 0) = IFNULL(h2.city_microdistrict_id, 0)
WHERE h2.id = '".mysql_real_escape_string($house_id)."'
ORDER BY IF(h1.id = h2.id, 0, 1), streets.name, h1.house, h1.building, a.name";
    
    global $factory;
    $result = array();
    $curr_house_id = null;
    if ($include_blank !== null) {
      $result[] = array(null,$include_blank);
      $result[] = array('-', '———————————————————————————————————', 'disabled' => 'disabled');
    }
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows)) {
      if ($amplifier && $amplifier['id'] == $row['id']) continue;
      if ($curr_house_id != $row['house_id']) {
        if ($curr_house_id == $house_id)
          $result[] = array('-', '———————————————————————————————————', 'disabled' => 'disabled');
        $curr_house_id = $row['house_id'];
      }
      $amplifier = new Amplifier($row);
      $result[] = array($amplifier['id'], format_address($amplifier).' - '.$amplifier['name'].' ('.$amplifier->location().')');
    }
    unset($rows);
    return $result;
  }
 
  static function frequencies_map()
  {
    $query = "SELECT id, frequency FROM frequencies t";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[$row['frequency']] = $row['id'];
    unset($rows);
    return $result;
  }
 
  static function station_id($amplifier_id)
  {
    $query = "SELECT s.id
    FROM amplifiers a
    INNER JOIN houses h ON h.id = a.house_id
    INNER JOIN streets ss ON ss.id = h.street_id
    INNER JOIN stations s ON s.city_id = ss.city_id
    WHERE a.id = '".mysql_real_escape_string($amplifier_id)."'
    LIMIT 1";
    
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
 
  static function last_scan($amplifier_id)
  {
    $query = "SELECT t.*, cu.name creator, uu.name updator
    FROM amplifier_scans t
    LEFT JOIN users cu ON t.created_by = cu.id LEFT JOIN users uu ON t.updated_by = uu.id
    WHERE t.amplifier_id = '".mysql_real_escape_string($amplifier_id)."' and t.active = true
    ORDER BY t.id DESC
    LIMIT 1";
    
    global $factory;
    $row = $factory->connection->execute_row($query);
    return $row ? new Amplifier($row) : null;
  }
 
  static function scan_details($amplifier_scan_ids, $station_id = null)
  {
    $ids = array();
    foreach($amplifier_scan_ids as $id)
      $ids[] = mysql_real_escape_string($id);
    $ids = implode("','", $ids);
  
    $query = "SELECT t.*, f.name, f.frequency, f.type, c.name channel_name
    FROM amplifier_scan_details t
    INNER JOIN amplifier_scans ass ON ass.id = t.amplifier_scan_id
    INNER JOIN amplifiers a ON a.id = ass.amplifier_id
    LEFT JOIN frequencies f ON t.frequency_id = f.id
    LEFT JOIN channels c ON f.id = c.frequency_id AND c.station_id = '".mysql_real_escape_string($station_id)."' AND c.active = true
    WHERE t.amplifier_scan_id IN ('".$ids."')
    ORDER BY f.frequency ASC, a.`name`, a.id";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Amplifier($row);
    unset($rows);
    return $result;
  }
  
  function location()
  {
    return $this['entrance'].'-й подъезд, '.
      ($this['floor'] < 0 ? 'Подвал' : ($this['floor'] >= 99 ? 'Крыша' : $this['floor'].'-й этаж'));
  }
  
  function frequencies()
  {
    $query = "SELECT id FROM frequencies ORDER BY frequency ASC";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = $row['id'];
    unset($rows);
    return $result;
  }
  
  static function parse_nbc_scan($file_content, $frequencies_map = null)
  {
    if (!$file_content) return null;
    if (!$frequencies_map) $frequencies_map = Amplifier::frequencies_map();
  
    $amplifier_scan_details = array();
    $rows = explode("\r\n", $file_content);
    
    for($i = 6; $i < count($rows); $i++) {
      if(strlen($rows[$i]) <= 1) break;
      $cells = explode("\t", $rows[$i]);
      if(count($cells) <= 10) break;

      $frequency = tofloat(prepare_float($cells[0]));
      $frequency = number_format($frequency, 2, '.', '');
       
      $amplifier_scan_details []= array(
        'amplifier_scan_id' => null,
        'frequency_id' => $frequencies_map[$frequency],
        'level' => (int)$cells[2],
        'video_to_audio' => (int)$cells[4],
        'level_to_noise' => (int)$cells[6],
      );
    }
    
    return $amplifier_scan_details;
  }
  
  static function save_scan($amplifier_id, $amplifier_scan_details, $file_name = '', $inspection_id = null)
  {
    if (count($amplifier_scan_details) == 0) return null;
  
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $now = date(MYSQL_TIME, time());
    
    $amplifier_scan = array(
      'amplifier_id' => $amplifier_id,
      'inspection_id' => $inspection_id,
      'file_name' => $file_name,
      'created_by' => $user_id,
      'updated_by' => $user_id,
      'created_at' => $now,
      'updated_at' => $now
    );
    
    global $factory;
    $amplifier_scan_id = $factory->create('amplifier_scans', $amplifier_scan);
    
    foreach ($amplifier_scan_details as $asd) {
      $asd['amplifier_scan_id'] = $amplifier_scan_id;
      $asd['id'] = $factory->create('amplifier_scan_details', $asd);
    }
    
    return $amplifier_scan_id;
  }
  
  static function import_scan_files($files, $house_id, $inspection_id = null)
  {
    $amplifier_ids = array();
    if (isset($files))
      foreach($files['error'] as $key => $value)
        $amplifier_ids []= $key;

    $errors = array();
    $success = 0;
    foreach ($amplifier_ids as $amplifier_id)
    {
      if($files['error'][$amplifier_id] == UPLOAD_ERR_OK && 
        is_uploaded_file($files['tmp_name'][$amplifier_id]))
      {
        $amplifier = Amplifier::first($amplifier_id, $house_id);
        if (!$amplifier) {
          $errors[] = $files['name'][$amplifier_id].' - усилитель не найден';
          continue;
        }
      
        $ext = strtolower(pathinfo($files['name'][$amplifier_id], PATHINFO_EXTENSION));
        if ($ext != 'nbc') {
          $errors[] = $files['name'][$amplifier_id].' - неправильное расширение файла';
          continue;
        }
      
        $file_content = file_get_contents($files['tmp_name'][$amplifier_id]);
        if ($file_content) {
          $amplifier_scan_details = null;
          switch ($ext) {
            case 'nbc':
              $amplifier_scan_details = Amplifier::parse_nbc_scan($file_content);
          }
          
          if (!$amplifier_scan_details) {
            $errors[] = $files['name'][$amplifier_id].' - неправильный формат файла';
            continue;
          }

          Amplifier::save_scan($amplifier_id, $amplifier_scan_details, $files['name'][$amplifier_id], $inspection_id);
          $success++;
        }
      }
    }
    return array('success' => $success, 'errors' => $errors);
  }
}

?>