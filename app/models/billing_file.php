<?php
class BillingFile extends ModelBase {
  var $table_name = 'billing_files';
  public static $page_size = 10;

  static function generate_query($count_only, $id = null, $filter = null, $page = 1, $limit = null)
  {
    $query = 'SELECT ';
    $query.= $count_only ? "count(bf.id)" : "bf.*, bs.name billing_source";
     $query.= " FROM billing_files bf 
    LEFT JOIN billing_sources bs ON bf.billing_source_id = bs.id
    WHERE 1=1";
    if ($id != null) $query.= " and bf.id = '".mysql_real_escape_string($id)."'";
    
    if ($filter) {
      $words = explode(' ', $filter);
      foreach($words as $word) {
        $query.= " and (";
        $query.= "bf.file_name LIKE '%".mysql_real_escape_string($word)."%' OR ";
        $query.= "bf.order_code = '".mysql_real_escape_string($word)."' OR ";
        $query.= "bs.name = '".mysql_real_escape_string($word)."'";
        $query.= ")";
      }
    }
    
    if (!$count_only) $query.= " ORDER BY bf.unhandled DESC, bf.id DESC";
    if ($limit != null) $query .= " LIMIT ".$limit." OFFSET ".(($page ? $page : 1) - 1) * $limit;
    
    return $query;
  }
  
  static function records($filter = null)
  {
    global $factory;
    $query = BillingFile::generate_query(true, null, $filter);
    
    return $factory->connection->execute_scalar($query);
  }
  
  static function load($id = null, $filter = null, $page = 1, $limit = null)
  {
    global $factory;
    $query = BillingFile::generate_query(false, $id, $filter, $page, $limit);
    
    if ($id != null) {
      $row = $factory->connection->execute_row($query);
      $result = $row ? new BillingFile($row) : null;
    } else {
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingFile($row);
      unset($rows);
    }
    
    return $result;
  }
  
  static function logs($billing_file_id)
  {
    global $factory;
    $query = "SELECT bfl.*, bd.billing_account_id, r.id has_request, r.subscriber_id FROM billing_file_logs bfl
    LEFT JOIN billing_details bd ON bd.id = bfl.billing_detail_id
    LEFT JOIN requests r ON r.id = bfl.request_id AND r.active = true
    WHERE bfl.billing_file_id = '".mysql_real_escape_string($billing_file_id)."'";
    
    $result = array();
    $rows = $factory->connection->execute($query);
    while($row = mysql_fetch_array($rows))
      $result[] = new BillingFile($row);
    unset($rows);
    
    return $result;
  }

  static function log($billing_file_log_id)
  {
    global $factory;
    $query = "SELECT bfl.*,
      bd.billing_account_id,
      bf.billing_source_id,
      bf.file_name,
      bs.payment_comment
    FROM billing_file_logs bfl
    LEFT JOIN billing_details bd ON bd.id = bfl.billing_detail_id
    JOIN billing_files bf ON bf.id = bfl.billing_file_id
    JOIN billing_sources bs ON bs.id = bf.billing_source_id
    WHERE bfl.id = '".mysql_real_escape_string($billing_file_log_id)."'";
    
    $row = $factory->connection->execute_row($query);
    return $row ? new BillingFile($row) : null;
  }    
  
  static function report($from_date, $to_date) {
  global $factory;
  
  $query = "select order_date, sum(order_fee) order_fee, sum(success_count+failed_count) total_count
  from billing_files
  where order_date >= '".mysql_real_escape_string($from_date)."' 
    and order_date <= '".mysql_real_escape_string($to_date)."'
  group by order_date DESC";
  
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingFile($row);
      unset($rows);
  return $result;
  }
  
  static function sources($from_date, $to_date) {
  global $factory;
  
  $query = "select bf.billing_source_id, bs.name billing_source, sum(order_fee) order_fee, sum(success_count+failed_count) total_count
  from billing_files bf
  join billing_sources bs on bf.billing_source_id = bs.id
  where order_date >= '".mysql_real_escape_string($from_date)."' 
    and order_date <= '".mysql_real_escape_string($to_date)."'
  group by bf.billing_source_id
  order by sum(order_fee) DESC";
  
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingFile($row);
      unset($rows);
  return $result;
  }
  
  static function report_details($from_date, $to_date) {
  global $factory;
  
  $query = "select bs.name billing_source, bf.order_date, bf.order_code, bf.order_fee, bf.success_count+bf.failed_count total_count
  from billing_files bf
  join billing_sources bs on bf.billing_source_id = bs.id
  where bf.order_date >= '".mysql_real_escape_string($from_date)."' 
    and bf.order_date <= '".mysql_real_escape_string($to_date)."'
  order by bf.order_date";
  
      $result = array();
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows))
        $result[] = new BillingFile($row);
      unset($rows);
  return $result;
  }
  
  function rollback()
  {
    global $factory;
  
    $datetime = date(MYSQL_DATE, strtotime('-30 days')).' 00:00:00';
    $query = "SELECT id FROM billing_files WHERE id = '".mysql_real_escape_string($this->attributes['id'])."' AND created_at > '".mysql_real_escape_string($datetime)."'";
    if ($factory->connection->execute_scalar($query) != null) { // if this file is in this month
      $bp = new BillingFile();
      $bp['id'] = $this->attributes['id'];
      $bp['updated_at'] = null;
      $bp['unhandled'] = 1;
      $bp->update(); // Corrupt this file

      $query = "SELECT id FROM billing_details WHERE billing_file_id='".mysql_real_escape_string($this->attributes['id'])."'";
      $rows = $factory->connection->execute($query);
      while($row = mysql_fetch_array($rows)) {
        BillingAccount::rollback($row['id']);
      }
      
      $query = "DELETE FROM billing_file_logs WHERE billing_file_id='".mysql_real_escape_string($this->attributes['id'])."'";
      $factory->connection->execute_void($query);
      
      $this->destroy(); // Destroy this file
      return true;
    }
    return false;
  }
  
  static function stats(){
    $row = array();
    global $factory;
    $query = "SELECT datediff(NOW(), created_at) max FROM billing_files ORDER BY id DESC LIMIT 1";
    $row['max'] = $factory->connection->execute_scalar($query);
    $query = "SELECT id errors FROM billing_files WHERE updated_at IS NULL LIMIT 1";
    $row['err'] = $factory->connection->execute_scalar($query);
    return $row;
  }
}

?>