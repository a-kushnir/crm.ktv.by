$(document).ready(function() {
  apartmentChanged();
  $('#request_apartment').keyup(apartmentChanged);
  $('#request_city_id').change(apartmentChanged);
})

function apartmentChanged(){
  $('#request_house_id, #request_street_id').change(apartmentChanged);

  var apartment = $('#request_apartment').val();
  var house_id = $('#request_house_id').val();
  var street_id = $('#request_street_id').val();
  var city_id = $('#request_city_id').val();
  
  if ($.trim(apartment) != '' && house_id != '' && street_id != '' && city_id != '') {
    $.ajax('/requests/subscriber.ajax?house_id=' + house_id + '&apartment=' + apartment + '&billing_file_log_id=' + billing_file_log_id + '&readonly', {
      success: function(data){
        $('#subscriber_container').show();
        $('#subscriber_container').html(data);
        if ($('').popover != null) {
            $('.tooltip-balloon').popover();
        }
      },
      error: function(data){
        alert(data.statusText);
      }
    })
  } else {
    $('#subscriber_container').hide();
  }
}