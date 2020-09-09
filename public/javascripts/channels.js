jQuery(document).ready(function() {
  jQuery('#station').change(applyFilter);
  jQuery('#channel_type').change(channelTypeChanged).change();
})

function applyFilter() {
  var url = window.location.pathname;
  url += '?station='+jQuery('#station').val();
  window.location = url;
}

function channelTypeChanged(){
  var selected_type = jQuery('#channel_type').val();
  jQuery('#analog_frequency').toggle(selected_type == 'analog');
  jQuery('#analog_frequency select').prop('disabled', selected_type != 'analog');
  jQuery('#digital_frequency').toggle(selected_type == 'digital');
  jQuery('#digital_frequency select').prop('disabled', selected_type != 'digital');
}