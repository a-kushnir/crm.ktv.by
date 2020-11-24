<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Абоненты' => url_for('subscribers'), 
  $subscriber->name() => null
)); ?>

<h1><?php echo $title; ?> <small><?php echo $subtitle; ?></small></h1>

<?php $relatives = has_access('subscriber/relatives') ? $subscriber->relatives() : array(); ?>
<ul class="nav nav-tabs stats">
<li><a href="#contract" class="default-tab"><strong><?php echo count($relatives) + 1; ?></strong><?php echo rus_word(count($relatives) + 1, 'Договор', 'Договора', 'Договоров') ?></a></li>
<?php if (has_access('subscriber/billing')) { ?>
<li><a href="#billing"><strong><?php echo format_money($subscriber['actual_balance'], array('suffix' => '')); ?></strong> На счете</a></li>
<?php } if (has_access('requests')) { ?>
<li><a href="#requests"><strong><?php echo count($requests); ?></strong><?php echo rus_word(count($requests), 'Заявка', 'Заявки', 'Заявок') ?></a></li>
<?php } if (has_access('subscriber/messages')) { ?>
<li><a href="#messages"><strong><?php echo count($messages); ?></strong><?php echo rus_word(count($messages), 'Сообщение', 'Сообщения', 'Сообщений') ?></a></li>
<?php } if (has_access('subscriber/calls')) { ?>
<li><a href="#calls"><strong><?php echo count($calls); ?></strong><?php echo rus_word(count($calls), 'Звонок', 'Звонка', 'Звонков') ?></a></li>
<?php } ?>
</ul>
<div class="tab-content">
<div class="tab-pane fade" id="contract">
  <div class="form-horizontal">
  <?php if (!$mobile_version && $layout != 'print' && $subscriber->self_billing()) {
    if (has_access('subscriber/download_memo')) echo "<a class='btn pull-right' style='margin-left:10px;' href='/subscribers/".$id."/memo.rtf'><i class='icon-download-alt'></i> Памятка</a>";
    if (has_access('subscriber/download_cancel')) echo "<a class='btn pull-right' style='margin-left:10px;' href='/subscribers/".$id."/cancel.rtf'><i class='icon-download-alt'></i> Отключение</a>";
    if (has_access('subscriber/download_cancel')) echo "<a class='btn pull-right' style='margin-left:10px;' href='/subscribers/".$id."/envelope.rtf'><i class='icon-download-alt'></i> Конверт</a>";
    if (has_access('subscriber/download_cancel')) echo "<a class='btn pull-right' style='margin-left:10px;' href='/subscribers/".$id."/complaint.rtf'><i class='icon-download-alt'></i> Претензия</a>";
  } ?>
  <?php 
    $form = new FormBuilder('subscriber', true);
    include '_form.php'
  ?>
  </div>
  <div class="form-actions visible-desktop">
    <?php echo link_to_back(url_for('subscribers')) ?>
    <?php if ($subscriber['active'] && has_access('subscriber/edit')) echo link_to_edit('subscribers', $id) ?>
    <?php if ($subscriber['active'] && has_access('subscriber/destroy')) echo '<a href="#terminate_subscriber_div" role="button" class="btn btn-danger btn-large" data-toggle="modal">Отключить</a>' ?>
    <?php if (!$subscriber['active'] && has_access('subscriber/restore') && !Subscriber::occupied_address($id)) echo "<a href='".url_for('subscribers', 'restore', $id)."' rel='nofollow' data-method='post' class='btn btn-large btn-danger' data-confirm='Вы уверены?'>Восстановить</a>"; ?>
  </div>
</div>
<div class="tab-pane fade" id="requests">
  <?php if (has_access('requests')) include '_requests.php'; ?>
</div>
<div class="tab-pane fade" id="messages">
  <?php if (has_access('subscriber/messages')) include '_messages.php' ?>
</div>
<div class="tab-pane fade" id="calls">
  <?php if (has_access('subscriber/calls')) include '_calls.php' ?>
</div>
<div class="tab-pane fade" id="billing">
  <?php if (has_access('subscriber/billing')) include '_billing.php' ?>
</div>
</div>

<form id="terminate_subscriber_form" action="/subscribers/<?php echo $id ?>/destroy" method="post">
<div id="terminate_subscriber_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3>Вы уверены, что хотите отключить абонента?</h3>
</div>
<div class="modal-body form-horizontal">
<?php
  $form = new FormBuilder('subscriber', false);
  echo $form->select('termination_reason_id', 'Причина', get_field_collection(Subscriber::termination_reasons(), 'id', 'name', ''), array('required' => true));
  echo $form->select('competitor_id', 'Перешел на', get_field_collection(House::competitors($subscriber['house_id']), 'id', 'name', ''));
  echo $form->text_area('termination_comment', 'Комментарий', null, array('rows' => '4'));
?>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Нет, оставить все как есть</a>
<input type="submit" class="btn btn-danger" value="Да, отключить"></input>
</div>
</div>
</form>
<?php if ($action == 'destroy') $javascripts[] = "<script>$('#terminate_subscriber_div').modal()</script>" ?>
