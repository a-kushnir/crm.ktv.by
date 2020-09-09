<?php echo breadcrumb(array(
  'Роли' => url_for('roles'), 
  $role['name'] => url_for('roles', 'show', $id),
  'Права' => null,
)); ?>

<?php echo page_header($title, $subtitle); ?>

<form method="post" class="form-inline">

<?php 
$javascripts[] = '/javascripts/jquery-ui-sortable.min.js';
$javascripts[] = '/javascripts/roles.js';
?>
<style type="text/css">
  #sortable-list { margin: 0; list-style-type:none; }
  #sortable-list fieldset { cursor: move; }
</style>
<ul id="sortable-list">
<?php
  $group_id = null;
  $index = 0;
  foreach($capabilities as $capability) {
    if ($group_id != $capability['capability_group_id']) {
      if ($group_id) echo '</fieldset><br>';
      $menu_item = isset($menu_items[$capability['capability_group_id']]) ? $menu_items[$capability['capability_group_id']] : null;
      echo '<li class="sortable-item"><fieldset><legend>'.check_box_tag('', '', false, array('style' => 'margin-bottom: 6px;', 'onclick' => '$(".group-'.$capability['capability_group_id'].'").attr("checked",$(this).is(":checked") ? "checked" : null);')).' '.$capability['group'].' <small class="float-right">'.check_box_tag('menu_items['.$capability['capability_group_id'].'][hide_in_other]', '1', $menu_item ? $menu_item['hide_in_other'] == '1' : false, array('style' => 'margin-bottom: 6px;')).' спрятать в Прочее</small></legend>'.
        hidden_field_tag('menu_items['.$capability['capability_group_id'].'][position]', $menu_item ? $menu_item['position'] : $index);
      $group_id = $capability['capability_group_id'];
      $index ++;
    }
    echo '<label for="capabilities_'.$capability['id'].'" class="checkbox">'.
      check_box_tag('capabilities['.$capability['id'].']', '1', isset($rights[$capability['id']]), array('class' => ($capability['danger'] ? 'danger-capability ' : '').'group-'.$group_id)).
      '<span>'.$capability['name'].'</span></label>';
  }
  if ($group_id) echo '</fieldset><br>';
?>
</ul>
<div class="form-actions">
  <?php
  echo link_to_back(url_for('roles', 'show', $id));
  echo link_to(url_for('roles', 'switch', $id), 'Проверка', array('class' => 'btn btn-large btn btn-info'));
  echo submit_button('Сохранить');
  ?>
</div>
</form>
