<div class="attachments-button attachments-<?php echo count($attachments) > 0 ? 'full' : 'empty'; ?>"></div>

<?php if (isset($house)) echo breadcrumb(array(
  'Дома' => url_for('houses'), 
  format_address($house) => null
)); ?>

<div class="btn-right-block btn-toolbar align-right hidden-print hidden-phone">
<?php
  if (has_access('house/download_memo') && $layout != 'print')
    echo '<a class="btn" href="/houses/'.$id.'/memos.rtf"><i class="icon-download-alt"></i> Скачать памятки</a>';
  echo print_version_button('&debtors', 'Должники');
  echo print_version_button();
?>
</div>

<?php echo page_header($title, $subtitle); ?>

<div class="form-vertical">
<?php
  echo render_house_legend();
  echo render_house($house);
?><div>

<table class="house-legend" style="margin-bottom:0px;">
  <tr>
    <td class="legend-label"><b>Отмечать:</b></td>
<?php
  $subscriber_note_types = SubscriberNote::get_types('inspection', $house['id']);
  foreach ($subscriber_note_types as $subscriber_note_type)
    echo '<td class="align-right"><strong>'.$subscriber_note_type['code'].'</strong></td><td class="legend-label">- '.$subscriber_note_type['name'].'</td>';
?>
  </tr>
</table>
</div>
<?php

include '_requests.php';
include '_debtors.php';
include '_amplifiers.php';

if ($layout != 'print') { ?>
<div class="form-actions">
  <?php echo link_to_back(url_for('houses')) ?>
</div>
<?php } ?>
</div>
