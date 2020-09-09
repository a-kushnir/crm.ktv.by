<?php
class Channel extends ModelBase {
  var $table_name = 'channels';
  
  public static $types = array('analog' => 'Аналоговый', 'digital' => 'Цифровой');
  
  public static $lost_connection = 15; // mins
  public static $forget_connection = 30; // days
  public static $cleanup_pings = 30; // days
  public static $cleanup_channels = 360; // days
  
  static function generate_query($count_only, $id = null, $station_id = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(ch.id)" : "ch.*, f.name channel_code, f.frequency, f.jumpers, f.type, s.name station, s.notified_at";
     $query.= " FROM channels ch
    LEFT JOIN frequencies f ON f.id = ch.frequency_id
    JOIN stations s ON ch.station_id = s.id
    WHERE ch.active = true";
    if ($id != null) $query.= " and ch.id = '".mysql_real_escape_string($id)."'";
    if ($station_id != null) $query.= " and ch.station_id = '".mysql_real_escape_string($station_id)."'";
    if (!$count_only) $query.= " ORDER BY s.name, f.frequency, ch.name";
    
    return $query;
  }
  
  static function load($id = null, $station_id = null)
  {
    global $factory;
    $query = Channel::generate_query(false, $id, $station_id);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new Channel($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new Channel($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function types()
  {
    $result = array();
    foreach (Channel::$types as $code => $name) {
      $result[$code] = array('code' => $code, 'name' => $name);
    }
    return $result;
  }
  
  static function analog_frequencies()
  {
    return Channel::frequencies('analog');
  }

  static function digital_frequencies()
  {
    return Channel::frequencies('digital');
  }
  
  static function frequencies($type)
  {
    global $factory;
    
    $result = array();
    $rows = $factory->connection->execute("SELECT * 
      FROM frequencies 
      WHERE type = '".mysql_real_escape_string($type)."'
      ORDER BY frequency");
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    foreach($result as $row)
      $row['title'] = $row['name'].' ('.format_float($row['frequency'],2).')';
    
    return $result;
  }
  
  static function stations()
  {
    $query = "SELECT s.* FROM stations s ORDER BY s.name";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    return $result;
  }
  
  static function stations_filter()
  {
    $query = "SELECT s.*, COUNT(ch.id) channel_count
    FROM stations s
    LEFT JOIN channels ch ON ch.station_id = s.id
    WHERE ch.active = true
    GROUP BY s.id
    ORDER BY s.name";
    
    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    return $result;
  }
  
  function validate()
  {
    global $factory;

    if ($this->is_new()) {
      if ($this['station_id'] == null) $this->errors['station_id'] = ERROR_BLANK;    
    }
    
    if ($this['name'] == null) $this->errors['name'] = ERROR_BLANK;

    if ($this['frequency_id']) {
      $station_id = $this->is_new() ? $this['station_id'] : $factory->connection->execute_scalar("SELECT station_id FROM channels WHERE id = '".mysql_real_escape_string($this['id'])."'");
      if ($station_id) {
        $channel_code = $factory->connection->execute_scalar("SELECT name FROM frequencies WHERE id = '".mysql_real_escape_string($this['frequency_id'])."'");
        if (get_field_value('type') == 'analog') {
          if ($factory->connection->execute_scalar("SELECT c.id 
            FROM channels c
            JOIN frequencies f ON c.frequency_id = f.id
            WHERE c.active = true AND f.name = '".mysql_real_escape_string($channel_code)."'
              AND c.station_id = '".mysql_real_escape_string($station_id)."'".
              ($this->is_new() ? '' : ' AND c.id <> '.mysql_real_escape_string($this['id']))))
          $this->errors['analog_frequency_id'] = ERROR_EXIST;
        } else if (get_field_value('type') == 'digital') {
          if ($factory->connection->execute_scalar("SELECT c.id 
            FROM channels c
            JOIN frequencies f ON c.frequency_id = f.id
            WHERE c.active = true AND f.name = '".mysql_real_escape_string($channel_code)."' 
              AND c.station_id = '".mysql_real_escape_string($station_id)."'
              AND f.type = 'analog'".
              ($this->is_new() ? '' : ' AND c.id <> '.mysql_real_escape_string($this['id']))))
          $this->errors['digital_frequency_id'] = ERROR_EXIST;
        }
      }
    }
    
    if (!$this['tuner_channel']) $this['tuner_channel'] = null;    
    else if (!is_numeric($this['tuner_channel'])) $this->errors['tuner_channel'] = ERROR_NUMBER;
  }

  function load_attributes($attributes, $id = null)
  {
    $this->attributes = array();
    
    $attributes = $this->allow_attributes($attributes,
      array(
        'name',
        'frequency_id',
        'satellite',
        'transponder',
        'tuner',
        'tuner_channel',
        'access_key',
        'description',
        'enabled',
        'station_id',
      )
    );
    
    $this->map_attributes($attributes);
  
    $this['id'] = $id;
    if (!$this['frequency_id']) $this['frequency_id'] = null;
    if (!$this->is_new()) unset($this['station_id']);
    
    $this->add_userstamp();
    $this->add_timestamp();
  }

  function curr_connection_lost()
  {
    if (!$this['notified_at']) return true;
    $datetime = strtotime(date(MYSQL_TIME, time())."-".(int)Channel::$lost_connection." min");
    return parse_db_datetime($this['notified_at']) < $datetime;
  }

  function curr_broken()
  {
    return $this['broken_from'];
  }
  
  static function save_notification($event, $station_id, $channel_id = null, $frequency = null)
  {
    global $factory;
    $factory->create('station_logs', array(
      'event' => $event, 
      'station_id' => $station_id, 
      'channel_id' => $channel_id, 
      'frequency' => prepare_float($frequency),
      'created_at' => date(MYSQL_TIME), 
      'ip_address' => $_SERVER['REMOTE_ADDR']
    ));
  }
  
  static function nofity_station($station_id)
  {
    $query = "UPDATE stations SET notified_at = NOW() WHERE id = ".(int)$station_id;
    global $factory;
    $factory->connection->execute_void($query);
  }
  
  static function find_by_station_and_frequency($station_id, $frequency)
  {
    $query = "SELECT ch.id
    FROM channels ch
    JOIN frequencies f ON ch.frequency_id = f.id
    WHERE ch.station_id = '".mysql_real_escape_string($station_id)."' AND f.frequency = '".mysql_real_escape_string(prepare_float($frequency))."'";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
  
  static function is_broken($channel_id)
  {
    $query = "SELECT broken_from FROM channels WHERE id = '".mysql_real_escape_string($channel_id)."'";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
 
  static function mark_broken($channel_id)
  {
    $query = "UPDATE channels SET broken_from = IFNULL(broken_from, NOW()), broken_to = NOW()
    WHERE id = '".mysql_real_escape_string($channel_id)."'";
    global $factory;
    $factory->connection->execute_void($query);
 }
 
  static function mark_repaired($channel_id)
  {
    $query = "UPDATE channels SET broken_from = NULL, broken_to = NULL
    WHERE id = '".mysql_real_escape_string($channel_id)."'";
    global $factory;
    $factory->connection->execute_void($query);
  }
 
  static function lost_connections()
  {
    $query = "SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), notified_at)) seconds_ago FROM stations
              WHERE notified_at IS NOT NULL AND TIME_TO_SEC(TIMEDIFF(NOW(), notified_at)) > ".(int)Channel::$lost_connection * 60;

    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    return $result;
  }
 
  static function broken_channels()
  {
    $query = "SELECT ch.*, TIME_TO_SEC(TIMEDIFF(NOW(), ch.broken_from)) seconds_ago, f.name channel_code, f.frequency frequency, s.name station, s.notified_at, ch.station_id
    FROM channels ch
    LEFT JOIN frequencies f ON f.id = ch.frequency_id
    JOIN stations s ON ch.station_id = s.id
    WHERE ch.active = true AND broken_from IS NOT NULL
    ORDER BY s.name, f.frequency";

    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    return $result;
  }
 
  static function invalid_channels()
  {
    $query = "SELECT DISTINCT sl.frequency, sl.station_id, s.name station
    FROM station_logs sl
    JOIN stations s ON sl.station_id = s.id
    WHERE frequency IS NOT NULL and channel_id IS NULL
    ORDER BY s.name, frequency";

    $result = array();
    global $factory;
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new Channel($row);
    unset($rows);
    
    return $result;
  }
  
  static function forget_known($station_id)
  {
    $query = "UPDATE channels SET broken_from = NULL, broken_to = NULL
    WHERE station_id = '".mysql_real_escape_string($station_id)."'";
    global $factory;
    $factory->connection->execute_void($query);
  }
  
  static function forget_unknown($station_id)
  {
    $query = "DELETE FROM station_logs
      WHERE channel_id IS NULL AND frequency IS NOT NULL and station_id = '".mysql_real_escape_string($station_id)."'";
    global $factory;
    $factory->connection->execute_void($query);
  }
  
  static function frequency_id($frequency)
  {
    $query = "SELECT id FROM frequences WHERE frequency = '".mysql_real_escape_string($station_id)."'";
    global $factory;
    return $factory->connection->execute_scalar($query);
  }
}

?>