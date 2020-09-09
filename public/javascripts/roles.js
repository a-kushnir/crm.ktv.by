$(document).ready(function() {
  var list = $('#sortable-list');
  list.sortable({
    handle: 'fieldset', 
    update: function() {
      $('li', list).each(function(index, elem) {
        $($(elem).children('fieldset').children('input[type=hidden]')[0]).val(index);
      });
    }
  });
});