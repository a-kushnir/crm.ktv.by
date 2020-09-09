<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Должники' => url_for('arrears'), 
  $subscriber->name() => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<a class="btn" href="/files/dialog_s_dolzhnikom.pdf"><i class="icon-download-alt"></i> Скачать диалог</a>
</div>

<?php echo page_header($title, $subtitle); ?>

<div class="form-vertical">
<h3><small>Должен компании</small> <?php echo format_money(-$subscriber["actual_balance"]); ?> <small> последняя оплата была </small> <?php $last_paid = human_date(BillingAccount::last_paid($id)); echo $last_paid ? $last_paid : 'никогда' ?></h3>
<div class="row">
<?php
  $has_answer = Call::has_answer($id);
  if (!isset($call)) $call = null;

  $phones = array();
  if (get_object_value($subscriber, 'cell_phone')) $phones['cell'] = get_object_value($subscriber, 'cell_phone');
  if (get_object_value($subscriber, 'home_phone')) $phones['home'] = get_object_value($subscriber, 'home_phone');

  foreach($phones as $phone_type => $phone_number) {
    $phone_number = Call::corrected_phone_number($subscriber['id'], $phone_type, $phone_number);
    
    echo '<div class="span6"><div class="well">';
    
    $last_call = Call::last_call($subscriber['id'], $phone_type, $phone_number);
    $lc_datetime = $last_call['created_at'];
    $lc_timestamp = parse_db_datetime($lc_datetime);
    
    if ($last_call) echo "<span class='float-right'><span class='bold'>".human_datetime($lc_datetime)."</span> <span title='Прошлый звонок'>".$last_call['name']."</span></span>";
    echo "<h4>".($last_call['lookup_code'] == 'wrong_number' ? '<s>' : '').phone_with_icon($phone_number).($last_call['lookup_code'] == 'wrong_number' ? '</s>' : '').'</h4>';
    
    if (!$has_answer && (time() - $lc_timestamp) > CALL_DELAY) {
      $crs = Call::results($phone_type);
      foreach($crs as $cr) {
        if ($cr['lookup_code'] == 'promision' || $cr['lookup_code'] == 'already_paid') {
          echo '<a href="#promision_div" role="button" class="btn btn-block '.$cr['button_class'].'" data-toggle="modal" onclick="$(\'#promision_form\').attr(\'action\',\'/arrears/'.$subscriber["id"].'/'.$phone_type.'?answer='.$cr['id'].'\');$(\'#promision_div h3\').text(\''.$cr['name'].'\')">'.$cr['name'].'</a>';
        } else if ($cr['lookup_code'] == 'termination') {
          echo '<a href="#termination_div" role="button" class="btn btn-block '.$cr['button_class'].'" data-toggle="modal" onclick="$(\'#termination_form\').attr(\'action\',\'/arrears/'.$subscriber["id"].'/'.$phone_type.'?answer='.$cr['id'].'\');$(\'#termination_form h3\').text(\''.$cr['name'].'\')">'.$cr['name'].'</a>';
        } else if ($cr['lookup_code'] == 'wrong_number') {
          echo '<a href="#wrong_number_div" role="button" class="btn btn-block '.$cr['button_class'].'" data-toggle="modal" onclick="$(\'#wrong_number_form\').attr(\'action\',\'/arrears/'.$subscriber["id"].'/'.$phone_type.'?answer='.$cr['id'].'\');$(\'#wrong_number_form h3\').text(\''.$cr['name'].'\')">'.$cr['name'].'</a>';
        } else {
          echo '<a href="/arrears/'.$subscriber["id"].'/'.$phone_type.'?answer='.$cr['id'].'" data-method="post" class="btn btn-block '.$cr['button_class'].'">'.$cr['name'].'</a>';
        }
      }
    } else if ($has_answer) {
    echo '<i>Необходимая информация получена</i>';
    } else {
      echo '<i>Звонок был осуществлен менее суток назад</i>';
    }
    
    echo '</div></div>';
  }
?>
</div>

<?php $relatives = $subscriber->relatives(); ?>
<ul class="nav nav-tabs stats">
<li><a href="#contract" class="default-tab"><strong><?php echo count($relatives) + 1; ?></strong><?php echo rus_word(count($relatives) + 1, 'Договор', 'Договора', 'Договоров') ?></a></li>
<li><a href="#billing"><strong><?php echo format_money($subscriber['actual_balance'], array('suffix' => '')); ?></strong> На счете</a></li>
<li><a href="#requests"><strong><?php echo count($requests); ?></strong><?php echo rus_word(count($requests), 'Заявка', 'Заявки', 'Заявок') ?></a></li>
<li><a href="#messages"><strong><?php echo count($messages); ?></strong><?php echo rus_word(count($messages), 'Сообщение', 'Сообщения', 'Сообщений') ?></a></li>
<li><a href="#calls"><strong><?php echo count($calls); ?></strong><?php echo rus_word(count($calls), 'Звонок', 'Звонка', 'Звонков') ?></a></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade" id="contract">
  <div class="form-horizontal">
  <?php if (!$mobile_version && $layout != 'print' && has_access('subscriber/download_memo')) { ?>
    <a class='btn pull-right' href='/subscribers/<?php echo $id; ?>/memo.rtf'><i class="icon-download-alt"></i> Памятка абонента</a>
  <?php } ?>
  <?php 
    $form = new FormBuilder('subscriber', true);
    echo link_to(url_for('subscribers', 'destroy', $id), '<i class="icon icon-trash"> </i>', array('class' => 'btn pull-right', 'style' => 'margin-right:10px;')).' ';
    echo link_to(url_for('subscribers', 'edit', $id), '<i class="icon icon-edit"> </i>', array('class' => 'btn pull-right', 'style' => 'margin-right:10px;')).' ';
    include APP_ROOT.'/app/views/subscribers/_form.php'
  ?>
  </div>
</div>
<div class="tab-pane fade" id="requests">
<?php $suppress_new_request = true; ?>
<?php include APP_ROOT.'/app/views/subscribers/_requests.php'; ?>
</div>
<div class="tab-pane fade" id="messages">
  <?php include APP_ROOT.'/app/views/subscribers/_messages.php' ?>
</div>
<div class="tab-pane fade" id="calls">
  <?php include APP_ROOT.'/app/views/subscribers/_calls.php' ?>
</div>
<div class="tab-pane fade" id="billing">
  <?php include APP_ROOT.'/app/views/subscribers/_billing.php' ?>
</div>
</div>

<?php
  $form = new FormBuilder('call');
  echo $form->begin_div(array('class' => 'form-horizontal'));
?>

<form id="promision_form" action="#" method="post">
<div id="promision_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3><?php if ($call && $call->call_result) echo $call->call_result['name']; ?></h3>
</div>
<div class="modal-body">
<p>Абоненту дается отсрочка на указанный период.</p>
<?php 
  echo $form->date("promised_on", "Погасит долг до", array('required' => true, 'hint' => 'Если абонент уже оплатил, то вводится сегодня')); 
?>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Отмена</a>
<input type="submit" class="btn btn-success" value="Сохранить"></input>
</div>
</div>
</form>
<?php echo $call && isset($call->errors["promised_on"]) ? "<script>$('#promision_div').modal()</script>" : '' ?>

<form id="termination_form" action="#" method="post">
<div id="termination_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3><?php if ($call && $call->call_result) echo $call->call_result['name']; ?></h3>
</div>
<div class="modal-body">
  <p>Абонент будет отключен до момента полного погашения задолженности.</p>
<?php 
  echo $form->select("termination_reason_id", "Причина", get_field_collection(Subscriber::termination_reasons(), 'id', 'name', ''), array('required' => true));
  echo $form->select('competitor_id', 'Перешел на', get_field_collection(House::competitors($subscriber['house_id']), 'id', 'name', ''));
  echo $form->text_area("termination_comment", "Комментарий", null, array('rows' => '4')); 
?>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Отмена</a>
<input type="submit" class="btn btn-danger" value="Отключить"></input>
</div>
</div>
</form>
<?php echo $call && $call->subscriber && $call->subscriber->errors['termination_reason_id'] ? "<script>$('#termination_div').modal()</script>" : '' ?>

<form id="wrong_number_form" action="#" method="post">
<div id="wrong_number_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3><?php if ($call && $call->call_result) echo $call->call_result['name']; ?></h3>
</div>
<div class="modal-body">
  <p>Введите правильный номер телефона для связи с абонентом</p>
<?php 
  echo $form->phone("new_phone_number", "Номер телефона"); 
?>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Отмена</a>
<input type="submit" class="btn btn-inverse" value="Сохранить"></input>
</div>
</div>
</form>

  <div class="form-actions">
    <?php echo link_to_back(url_for('arrears')) ?>
    <?php echo link_to(url_for('arrears', 'prev', $id), '<i class="icon-chevron-left icon-white"> </i> Предыдущий', array('class' => 'btn btn-large btn-info')); ?>
    <?php echo link_to(url_for('arrears', 'next', $id), 'Следующий <i class="icon-chevron-right icon-white"> </i>', array('class' => 'btn btn-large btn-info')); ?>
    <?php if (has_access('subscriber/destroy')) echo link_to(url_for('arrears', 'destroy', $id), 'Отключить', array('class' => 'btn btn-large btn-danger', 'rel'=>'nofollow', 'data-method'=>'post', 'data-confirm'=>'Отключить абонента и создать заявку?')); ?>
  </div>
</div>

<?php echo $form->end_div(); ?>