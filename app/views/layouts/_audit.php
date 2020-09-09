<?php if(!$audit_record->is_new()) { ?>
<fieldset>
<legend>Аудит</legend>
<?php echo read_only_field('audit_record', 'creator', 'Создано', null, array('value' => '<small>'.human_datetime(get_object_value($audit_record, 'created_at'))."</small> ".get_object_value($audit_record, 'creator'))); ?>
<?php if (get_object_value($audit_record, 'created_at') != get_object_value($audit_record, 'updated_at') || get_object_value($audit_record, 'creator') != get_object_value($audit_record, 'updator'))
      echo read_only_field('audit_record', 'editor', 'Изменено', null, array('value' =>'<small>'.human_datetime(get_object_value($audit_record, 'updated_at'))."</small> ".get_object_value($audit_record, 'updator'))); ?>
</fieldset>
<?php } ?>