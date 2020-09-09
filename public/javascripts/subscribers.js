$(document).ready(function() {
  $('#subscriber_cell_phone, #subscriber_home_phone').change(function() {
    $('#billing_account_lookup_code.hide-if-necessary').parents('div.control-group').toggle(
      $('#subscriber_cell_phone').val() == '' && $('#subscriber_home_phone').val() == ''
    );
  }).change();
})