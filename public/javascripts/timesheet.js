jQuery(document).ready(function() {
  jQuery("#worker").change(applyFilter);
  jQuery("#include").change(applyFilter);
  jQuery("#from_date").blur(applyFilter);
  jQuery("#to_date").blur(applyFilter);
  
  jQuery("#select_all_time_entries").change(selectAll);
  jQuery("input.time_entry").change(allowSave);
})

function applyFilter() {
  var url = window.location.pathname;
  url += '?from='+jQuery('#from_date').val();
  url += '&to='+jQuery('#to_date').val();
  if (jQuery('#mode').length > 0) url += '&mode='+jQuery('#mode').val();
  if (jQuery('#worker').length > 0) url += '&worker='+jQuery('#worker').val();
  if (jQuery('#include').length > 0) url += '&include='+(jQuery('#include').is(':checked') ? jQuery('#include').val() : '');
  window.location = url;
}

function selectAll() {
  jQuery("input[type=checkbox].time_entry").attr('checked', jQuery("#select_all_time_entries").is(':checked'));
  allowSave();
}

function allowSave() {
  jQuery("input[type=checkbox].time_entry:checked").length > 0 ? 
    jQuery("input[type=submit]").show() :
    jQuery("input[type=submit]").hide();
}
