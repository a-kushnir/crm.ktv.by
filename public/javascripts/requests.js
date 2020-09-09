$(document).ready(function() {
  // New request
  $("#request_city_id,#request_street_id,#request_house_id").one('change', apartmentChanged);
  $("#request_apartment").keyup(apartmentChanged);
  $("#request_city_id").change(apartmentChanged);

$.ajaxSetup({
  complete: function() {
    $("#request_city_id,#request_street_id,#request_house_id").one('change', apartmentChanged);
  }
});
  
  // Requests
  $("#house,#type,#include").change(applyFilter);
  
  $(".select_house").click(houseClicked);
})

function houseClicked() {
  if ($(".select_house:checked").length > 0) {
    $("#house option[value='selection']").html('Выбранные дома [' + $(".select_house:checked").length + ']');
    $("#house option[value='selection']").show();
  } else {
    $("#house option[value='selection']").hide();
  }
}

function applyFilter() {
  var url = window.location.pathname;
  url += '?house='+$('#house').val();
  url += '&type='+$('#type').val();
  url += '&include='+($('#include').is(':checked') ? $('#include').val() : '');
  url += '&selection='+($('#selection').length == 1 ? $('#selection').val() : $('.select_house:checked').map(function(){
    return this.getAttribute("value");
  }).get());
  window.location = url;
}

function apartmentChanged(){
  var apartment = $("#request_apartment").val();
  var house_id = $("#request_house_id").val();
  if ($.trim(apartment) != '' && house_id != null && house_id != '')
  {
    $.xhrPool.abortAll(); // Cancel all previous requests
    $('.address_loading').show();
    $('#subscriber_container').hide();
    $.ajax('/requests/subscriber.ajax?house_id=' + house_id + '&apartment=' + apartment, {
      success: function(data){
    $('.address_loading').hide();
        $('#subscriber_container').show();
        $('#subscriber_container').html(data);
        if ($('').mask != null) {
          $(".phone-number").mask("8 (099) 999-99-99");
        }
        if ($('').popover != null) {
          $('.tooltip-balloon').popover();
        }
      }
    })
  } else {
    $('#subscriber_container').hide();
  }
  
}