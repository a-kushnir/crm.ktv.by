<?php echo breadcrumb(array(
  'Заявки' => url_for('requests'), 
  format_address($request) => null
)); ?>

<?php echo page_header($title, $subtitle); ?>

<form method="post" action="<?php echo url_for('requests', 'close', $id) ?>" class="form-horizontal">
  <?php include '_form.php' ?>

  <div class="form-actions">
    <?php echo link_to_back(url_for('requests')) ?>
    <?php echo submit_button("Закрыть"); ?>
    <?php if (has_role(ROLE_ROOT)) echo link_to_edit('requests', $id) ?>
    <?php if (has_role(ROLE_ROOT)) echo link_to_destroy('requests', $id) ?>
  </div>
</form>
