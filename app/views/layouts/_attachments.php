<div id="attachments_div" class="modal" style="display:none;" tabindex="-1" role="dialog">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3>Прикрепленные файлы</h3>
</div>
<div class="modal-body">
  <a href="#" id="attachments_upload_button" onclick="$('#attachments_upload_button').hide(); $('#attachments_form').show(); return false;"><i class="icon icon-download-alt"> </i> Добавить файлы</a>
  <?php $query_params = (isset($attachments_for) ? '?for='.$attachments_for.(isset($attachments_id) ? '&id='.$attachments_id : '') : '') ?>
  <form id="attachments_form" method="post" action="/files/upload.ajax<?php echo $query_params; ?>" enctype="multipart/form-data" style="display:none;">
    <div id="attachments_dropzone">
      Кидай сюда<br>
      <a class="btn btn-primary">Обзор</a>
      <input type="file" name="attachments_file" multiple ></input>
    </div>
  </form>

  <table id="attachments_table" class="table table-hover"><?php
    foreach ($attachments as $attachment){
      $extension = pathinfo($attachment['name'], PATHINFO_EXTENSION);
      echo '<tr>'.
        '<td><div class="mime ext-'.$extension.'"></div><a href="/files/download/'.$attachment['id'].'/'.$attachment['name'].'">'.$attachment['name'].'</a></td>'.
        '<td class="align-right"><small>'.format_file_size($attachment['size']).'</small></td>'.
        '<td class="align-right"><small>'.human_date($attachment['created_at']).'</small></td>'.
        '<td style="width: 10px;"><a href="/files/destroy.ajax/'.$attachment['id'].'/'.$attachment['name'].'" class="attachments-destroy">×</a></td>'.
      '</tr>';
    }
  ?></table>
  <table style="display:none;">
    <tr id="attachments_template">
      <td><div class="mime ext-{ext}"></div><span>{name}</span></td>
      <td class="align-right"><small>{size}</small></td>
      <td class="align-right"><div style="margin-bottom:0;min-width:50px;" class="progress progress-striped active"><div class="bar" style="width: 0%;"></div></div></td>
      <td style="width: 10px;"><a href="#" class="attachments-destroy">×</a></td>
    </tr>
  </table>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>
</div>
</div>

<?php
  $javascripts[] = '/javascripts/jquery.ui.widget.min.js';
  $javascripts[] = '/javascripts/jquery.iframe-transport.min.js';
  $javascripts[] = '/javascripts/jquery.fileupload.min.js';
  $javascripts[] = '/javascripts/file-uploader.js';
?>
<link rel="stylesheet" type="text/css" href="/stylesheets/file-uploader.css" />
