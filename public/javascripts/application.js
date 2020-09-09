$(document).ready(function() {
  initPrimaryMenuButtons();
  if ($('').datepicker != null) {
    $('.date-picker').datepicker({weekStart: 1, format: 'dd.mm.yyyy'});
  }
  if ($('').mask != null) {
    $(".phone-number").mask("8 (099) 999-99-99");
    $.mask.definitions['я']='[а-яА-Яa-zA-Z]';
    $(".passport-id").mask("яя 9999999");
    $(".contract_number").mask("9999999");
  }
  if ($('').htmlarea != null) {
    $("textarea.html-area:visible").htmlarea();
  }
  if ($('').popover != null) {
    $('.tooltip-balloon').popover({html:true,trigger:'hover'});
  }
  if ($('').tab != null) {
    $('.nav.nav-tabs a').click(function (e) {
      e.preventDefault();
      $(this).tab('show');
    })
  }
  if ($('').dropdown != null) {
    $('.dropdown-toggle').dropdown();
  }
  
  setupAjax();
  initAddressHandlers();
  initChooseRegionHandlers();
});

function initPrimaryMenuButtons(){
  $('#primary_menu_search_button').click(function(){
    $('#primary_menu_buttons, #primary_menu_search').toggle();
    var filter = $('#primary_menu_search input[type=text]')
    if (filter.is(':visible')) filter.focus();
    return false;
  });
  $('#primary_menu_search input[type=text]').keyup(function(e){
    //alert(e.which);
    if (e.which == 27) { // Escape
      $('#primary_menu_buttons, #primary_menu_search').toggle();
    } else if (e.which == 13) { // Enter
      var url = $('#primary_menu_search_button').attr('href');
      var val = $('#primary_menu_search input[type=text]').val()
      window.location = url + (url.indexOf('?') ? '&' : '?') + 'filter='+encodeURIComponent(val);
      return false;
    }
  });
}

function setupAjax() {
$.xhrPool = [];
$.xhrPool.abortAll = function() {
    $(this).each(function(idx, jqXHR) {
      jqXHR.abort();
    });
  this.length = 0;
};

$.ajaxSetup({
  beforeSend: function(jqXHR, settings) {
    $.xhrPool.push(jqXHR);
  },
  complete: function(jqXHR, textStatus) {
      var index = $.inArray(jqXHR, $.xhrPool);
      if (index > -1) $.xhrPool.splice(index, 1);
  },
  error: function(jqXHR, textStatus, errorThrown) {
    if (textStatus != 'abort' && textStatus != 'error') {
      alert(textStatus);
    }
    var index = $.inArray(jqXHR, $.xhrPool);
        if (index > -1) $.xhrPool.splice(index, 1);
  }
});
}

function redirect_to(url) {
  window.location.replace(url);
}

function initAddressHandlers()
{
  $('.address_city').change(handleCityChange);
  $('.address_street').change(handleStreetChange);
  $('.address_house').change(handleHouseChange);
  $('.billing_tariff').change(billingTariffChange);
}

function handleCityChange()
{
  var object_name = this.name.substr(0, this.name.indexOf('['));
  var parent = '#'+object_name+'_address ';
  
  $(parent + '.address_city_selected').hide();
  $(parent + '.address_street_selected').hide();
  $(parent + '.address_house_selected').hide();
  $(parent + '.address_house_selected').hide();
  $(parent + '.address_street_container').html('');
  
  $('.billing_tariff_container').hide(); // Billing tariff

  var city = $('#' + object_name + '_city_id');
  if (city.val() != '') {
    $(parent).removeClass('error');
    $(parent + '.error').html('');
    if ($(parent + '.address_street_container').length > 0) {
      $(parent + '.address_loading').show();
      $.ajax('/address/' + city.val() + '/streets.ajax?for=' + object_name + 
     ($('#' + object_name + '_apartment').length > 0 ? '' : '&all'), {
        success: function(data){
          $(parent + '.address_loading').hide();
          $(parent + '.address_city_selected').show();
          $(parent + '.address_street_container').html(data);
          $('#' + object_name + '_street_id').change(handleStreetChange);
          $('.billing_tariff_container').show(); // Billing tariff
        }
      });
    } else {
      $(parent + '.address_city_selected').show();
    }
    
    if ($('.billing_tariff_container').length > 0) {
      var ba_object_name = $('.billing_tariff_container label.control-label').attr('for');
      ba_object_name = ba_object_name.substr(0, ba_object_name.indexOf('billing_tariff_id') - 1);
      $('.tariff_justification_container').hide();
      $('.tariff_ends_on_container').hide();
      $.ajax('/address/' + city.val() + '/tariffs.ajax?for=' + ba_object_name, {
        success: function(data){
          $('.billing_tariff_container .controls').html(data);
          $('#' + ba_object_name + '_billing_tariff_id').change(billingTariffChange).change();
        }
      });
    }
  }
}

function billingTariffChange()
{
  var selected = $(this).children('option:selected');
  $('.tariff_justification_container').toggle(selected.attr('data-justification') == '1');
  $('.tariff_ends_on_container').toggle(selected.attr('data-ends-on') == '1');
}

function handleStreetChange()
{
  var object_name = this.name.substr(0, this.name.indexOf('['));
  var parent = '#'+object_name+'_address ';

  var street = $('#' + object_name + '_street_id');

  $(parent + '.address_street_selected').hide();
  $(parent + '.address_house_selected').hide();
  $(parent + '.address_house_container').html('');
  
  if (street.val() != '') {
    if ($(parent + '.address_house_container').length > 0) {
      $(parent + '.address_loading').show();
      $.ajax('/address/' + street.val() + '/houses.ajax?for=' + object_name, {
        success: function(data){
          $(parent + '.address_loading').hide();
          $(parent + '.address_street_selected').show();
          $(parent + '.address_house_container').html(data);
          $('#' + object_name + '_house_id').change(handleHouseChange);
        }
      });
    } else {
      $(parent + '.address_street_selected').show();
    } 
  }
}

function handleHouseChange()
{
  var object_name = this.name.substr(0, this.name.indexOf('['));
  var parent = '#'+object_name+'_address ';

  var house = $(this);
  if (house.val() != '') {
    $(parent + '.address_house_selected').show();
  } else {
    $(parent + '.address_house_selected').hide();
  }
}

function initChooseRegionHandlers(){
  $('#selected_region_city_id').change(handleSelectedCityChange);
  $('#selected_region_city_district_id').change(handleSelectedCityDistrictChange);
}

function handleSelectedCityChange(){
  $('.choose_city_district_div').hide();
  
  var city = $('#selected_region_city_id');
  if (city.val() != '') {
    $('#selected_region_city_district_id').val('');
  $('#selected_region_city_microdistrict_id').val('');
    if ($('.choose_city_district_div').length > 0) {
      $.ajax('/address/' + city.val() + '/districts.ajax', {
        success: function(data){
          $('.choose_city_district_div').html(data);
      $('.choose_city_district_div').show();
          $('#selected_region_city_district_id').change(handleSelectedCityDistrictChange);
        }
      });
    } else {
      $('.choose_city_district_div').show();
    }
  }
}

function handleSelectedCityDistrictChange()
{
  $('.choose_city_microdistrict_div').hide();
  
  var city = $('#selected_region_city_district_id');
  if (city.val() != '') {
    $('#selected_region_city_microdistrict_id').val('');
    if ($('.choose_city_microdistrict_div').length > 0) {
      $.ajax('/address/' + city.val() + '/microdistricts.ajax', {
        success: function(data){
          $('.choose_city_microdistrict_div').html(data);
      $('.choose_city_microdistrict_div').show();
        }
      });
    } else {
      $('.choose_city_microdistrict_div').show();
    }
  }
}

function submitChoosenRegion()
{
  $('#submit_choosen_region').addClass('disabled');
  $('#submit_choosen_region').val('Применяется...');
  $.ajax({
    type: 'POST',
    url: '/address/set_region.ajax',
    data: $('#choose_location_form').serialize(),
    success: function(){
      $('#submit_choosen_region').val('Применить');
      $('#submit_choosen_region').removeClass('disabled');
      $('#choose_location_div').modal('hide');
      location.reload();
    }
  });
  
  var regionName = 'Мой регион';
  var city_id = $('#selected_region_city_id').val();
  if (city_id != null && city_id != '') {
    regionName = $('#selected_region_city_id option:selected').text();
  var district_id = $('#selected_region_city_district_id').val();
  if (district_id != null && district_id != '') {
    regionName += ", " + $('#selected_region_city_district_id option:selected').text();
  var microdistrict_id = $('#selected_region_city_microdistrict_id').val();
  if (microdistrict_id != null && microdistrict_id != '') {
    regionName += ", " + $('#selected_region_city_microdistrict_id option:selected').text();
  }}}
    $('#region_name_menu_item').html(regionName);
  
  return false;
}

function chooseRegion()
{
    $('#choose_location_div').modal();
}

function showSection(identity){
  $('#show_'+identity).hide();
  $('#hide_'+identity).show();
  $('#' + identity).show('fast');
  return false;
}

function hideSection(identity){
  $('#hide_'+identity).hide();
  $('#show_'+identity).show();
  $('#' + identity).hide('fast');
  return false;
}

$(document).ready(function() {
  if ($('#ipcam_url').size() > 0) {
    var refreshId = setInterval(function() {
      var randval = Math.random();
      var img = new Image();
      $(img).addClass('img-polaroid2');
      $(img).load(function () {
          if ($('#ipcam_url option:selected').val() != "") {
            $(this).hide();
            $('#ipcam_wnd').removeClass('no-signal').html("").append(this);
            $(this).attr('style', 'display:inline;');//$(this).show();
            $('#ipcam_time').html('<small>Обновлено ' + dateFormat(new Date(), 'HH:MM:ss' + '</small>'));
          }
      }).error(function () {
      }).attr('src', $('#ipcam_url option:selected').val()  != "" ? $('#ipcam_url option:selected').val() + randval : null);
    }, 1000);
    $.ajaxSetup({ cache: false });
    
    $('#ipcam_url').change(function(){
      $('#ipcam_wnd').html('<img class="img-polaroid2" src="/images/no-signal.gif" />').addClass('no-signal');
      $('#ipcam_time').html('');
    })
  }
  
  // Javascript to enable link to tab
  var url = document.location.toString();
  if (url.match('#')) {
    $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show');
  } else {
    $('.nav-tabs a.default-tab').tab('show');
  }
  $('div.tab-pane:visible').addClass('fade in');
  
  // Change hash for page-reload
  $('.nav-tabs a').on('shown', function (e) {
    window.location.hash = e.target.hash;
  })

  // Check Boxes in Drop Down Menus
  $('.dropdown-menu').on('click', function(e){
    if($(this).hasClass('dropdown-menu-form')){
      e.stopPropagation();
    }
  });
  
  $('.house-schema .dropdown-menu-form button').on('click', function(e){
    var parent = $(this).parents('.dropdown');
    parent.children('.dropdown-toggle').dropdown('toggle');
    /*if (parent.find('input:checked').size() > 0)
      parent.parent().addClass('report-filled');
    else
      parent.parent().removeClass('report-filled');*/
    return false;
  });
  
  $('.house-schema .dropdown-menu-form').hover(function () {
    // Do nothing
  }, function () {
    var parent = $(this).parents('.dropdown');
    //parent.children('.dropdown-toggle').dropdown('toggle');
    if (parent.find('input:checked').size() > 0)
      parent.parent().addClass('report-filled');
    else
      parent.parent().removeClass('report-filled');
  });
});