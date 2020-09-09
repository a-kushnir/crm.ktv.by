jQuery(document).ready(function() {
  jQuery("#amp").change(applyFilter);
})

function applyFilter() {
  var url = window.location.pathname;
  url += '?amp='+jQuery('#amp').val();
  window.location = url;
}
