<?php
class SubscriberNote extends ModelBase {
  var $table_name = 'subscriber_notes';

  // kind - inspection or subscriber
  // TODO $house_id
  static function get_types($kind, $house_id = null)
  {
    if ($kind != 'inspection' && $kind != 'subscriber') return;
    
    $query = "SELECT snt.* FROM subscriber_note_types snt ";
    if ($house_id) $query.= "LEFT JOIN houses_competitors hc ON hc.house_id = '".mysql_real_escape_string($house_id)."' AND hc.competitor_id = snt.competitor_id ";
    $query.= "WHERE snt.visible_".($kind)." = true ";
    if ($house_id) $query.= "AND snt.competitor_id IS NULL OR hc.id IS NOT NULL ";
    $query.= "ORDER BY snt.position";
    
    global $factory;
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = $row;
    unset($rows);
    
    return $result;
  }
}

?>