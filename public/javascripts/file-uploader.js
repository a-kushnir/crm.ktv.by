$(function(){
  var attachments_table = $('#attachments_table');

  $('div.attachments-button').click(function(){
    $('#attachments_upload_button').show();
    $('#attachments_form').hide();
    $('#attachments_div').modal('show');
    return false;
  });

  $('#attachments_dropzone a').click(function(){
    $(this).parent().find('input').show().focus().click().hide();
  });

  $('#attachments_form').fileupload({
    dropZone: $('#attachments_dropzone'),
    add: function (e, data) {
      var fname = data.files[0].name;
      var fext = fname.substr( (fname.lastIndexOf('.') +1) ).toLowerCase();
  
      var tpl = $('#attachments_template').html();
      tpl = '<tr class="working">' + tpl + '</tr>';
      tpl = tpl.replace('{ext}', htmlEscape(fext));
      tpl = tpl.replace('{name}', htmlEscape(fname));
      tpl = tpl.replace('{size}', formatFileSize(data.files[0].size));
      tpl = $(tpl);

      data.context = tpl.prependTo(attachments_table);
      attachments_table.show();

      // Listen for clicks on the cancel icon
      tpl.find('a.attachments-destroy').click(function(){
        if(tpl.hasClass('working')){
          jqXHR.abort();
        }
        tpl.fadeOut(function(){
          tpl.remove();
          if (attachments_table.find('td').size() == 0)
            attachments_table.hide();
        });
      });

      var jqXHR = data.submit();
    },
    progress: function(e, data){
      var progress = parseInt(data.loaded / data.total * 100, 10);
      data.context.find('div.bar').width(progress+'%');
      if(progress == 100){ data.context.removeClass('working'); }
    },
    done: function(e, data) {
      var result = JSON.parse(data.result);
      data.context.find('div.progress').replaceWith('<small>только что</small>'); 
      var name = data.context.find('span');
      name.replaceWith('<a href="/files/download/' + result.id + '/' + result.name + '">' + result.name + '</>'); 
      var destroy_link = data.context.find('a.attachments-destroy');
      destroy_link.attr('href', '/files/destroy.ajax/' + result.id + '/' + result.name); 
      destroy_link.click(destroyFile);
    },
    fail:function(e, data){
      data.context.find('div.progress').replaceWith('<small>ошибка</small>'); 
      data.context.addClass('error');
    }
  });

  $(document).on('drop dragover', function (e) {
    e.preventDefault();
  });

  function formatFileSize(bytes) {
    if (typeof bytes !== 'number') return '';
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    return (bytes / 1024).toFixed(2).replace('.', ',') + ' KB';
  }
  
  function htmlEscape(value) {
    return $('<div/>').text(value).html();
  }
  
  function destroyFile() {
    $.ajax($(this).attr('href'), { type: 'POST' });
    var tr = $(this).parents('tr');
    tr.fadeOut(function(){ 
      tr.remove();
      if (attachments_table.find('td').size() == 0)
        attachments_table.hide();
    });
    return false;
  }
  
  $('a.attachments-destroy').click(destroyFile);
  
});