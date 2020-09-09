<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php echo breadcrumb(array(
  'Заявки' => (get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('requests', 'index')), 
  format_address($request) => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<?php 
  $form = new FormBuilder('request', true); 
  echo $form->begin_form('/requests/'.$id.'/close?index='.get_field_value('index'), array('class' => 'form-horizontal'));
  include '_form.php'
?>
  <div class="form-actions">
    <?php echo link_to_back(get_field_value('index') ? base64_decode(get_field_value('index')) : url_for('requests', 'index')) ?>
    <?php if ($request['handled_on'] == null && has_access('request/close')) echo submit_button("Закрыть"); ?>
    <?php if ($request['handled_on'] == null && has_access('request/edit')) echo link_to('/requests/'.$id.'/edit?index='.get_field_value('index'), 'Редактировать', array('class' => 'btn btn-large btn-primary')) ?>
    <?php if (($request['handled_on'] == null && $request['active']) && has_access('request/destroy')) echo "<a href='".('/requests/'.$id.'/destroy?index='.get_field_value('index'))."' rel='nofollow' data-method='post' class='btn btn-large btn-danger' data-confirm='Вы уверены?'>Удалить</a>" ?>
    <?php if (($request['handled_on'] != null || !$request['active']) && has_access('request/restore')) echo "<a href='".('/requests/'.$id.'/restore?index='.get_field_value('index'))."' rel='nofollow' data-method='post' class='btn btn-large btn-danger' data-confirm='Вы уверены?'>Восстановить</a>"; ?>
  </div>
<?php 
  echo $form->end_form();
?>
