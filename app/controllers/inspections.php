<?php
class InspectionsController extends ApplicationController
{
  function index()
  {
    check_access('inspections');
    
    $this->title = "Обходные отчеты";
    
    $filter = trim(get_field_value('filter'));
    $this->inspections = Inspection::all($filter, null, null, get_field_value('page'), Inspection::$page_size);
    $this->records = Inspection::records($filter);
  }

  function show()
  {
    check_access('inspections');

    $this->inspection = Inspection::first($this->id);
    if (!$this->inspection) show_404();
    
    $this->subscriber_notes = $this->inspection->subscriber_notes();
    $this->amplifier_scans = $this->inspection->amplifier_scans();
    $this->amplifiers = Amplifier::all($this->inspection['house_id']);
    
    $this->title = "Обходной отчет";
    $this->subtitle = format_address($this->inspection);
  }

  function add()
  {
    check_access('house/new_inspection');

    $this->house = House::load($this->id);
    if (!$this->house) show_404();
    
    $this->inspection = array('actual_date' => date(MYSQL_DATE, time()));
    $this->amplifiers = Amplifier::all($this->house['id']);
    
    $this->title = "Обходной отчет";
    $this->subtitle = format_address($this->house);
  }

  function create()
  {
    check_access('house/new_inspection');

    $this->house = House::load($this->id);
    if (!$this->house) show_404();
    
    $now = date(MYSQL_TIME, time());
    $user = $_SESSION['user_id'];
    
    global $factory;
    
    $inspection = get_field_value('inspection');
    $inspection_id = $factory->create('inspections', array(
      'house_id' => $this->house['id'],
      'actual_date' => prepare_date($inspection['actual_date']),
      'handled_by' => $inspection['handled_by'],
      'comment' => $inspection['comment'],
      'created_at' => $now,
      'updated_at' => $now,
      'created_by' => $user,
      'updated_by' => $user,
    ));
    
    $notes = get_field_value('notes');
    if ($notes) {
      foreach($notes as $apartment => $note_types) {
        $subscriber = Subscriber::load_by_address($this->house['id'], $apartment);
        foreach ($note_types as $note_type) {
          $factory->create('subscriber_notes', array(
            'subscriber_note_type_id' => $note_type,
            'subscriber_id' => $subscriber ? $subscriber['id'] : null,
            'house_id' => $this->house['id'],
            'apartment' => $apartment,
            'inspection_id' => $inspection_id,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $user,
            'updated_by' => $user,
          ));
        }
      }
    }

    $this->house->recalc_inspected_on();

    $result = array('success' => 0, 'errors' => array());
    if (isset($_FILES['scan_file'])) {
      $result = Amplifier::import_scan_files($_FILES['scan_file'], $this->house['id'], $inspection_id);
    }
    
    if (count($result['errors']) > 0) flash_alert('При загрузке возникли следующие ошибки: <ul><li>'.implode('<li>', $result['errors']).'</ul>');
    flash_notice('Обходной отчет был создан, '.$result['success'].' '.rus_word($result['success'], 'скан загружен', 'скана загружено', 'сканов загружено'));
    redirect_to(get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('houses', 'index'));
  }
}
?>