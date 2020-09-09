<?php

$constraints = array(
  'id' => '\d+',        // Only digits
  'action' => '[^\/]+'  // Doesn't contain '/' symbol
);

# Custom pages
$routing->root('home#index');

$routing->get('403', 'errors#show_403');
$routing->get('404', 'errors#show_404');
$routing->get('500', 'errors#show_500');

$routing->post('files/upload.ajax', 'files#upload');
$routing->post('files/destroy.ajax/:id/:filename', 'files#destroy');
$routing->get('files/download/:id/:filename', 'files#download', $constraints);

$routing->get('address/:id/:action(.:format)', 'address', $constraints);

$routing->get('reports/:id/:action(.:format)', 'reports', $constraints);
$routing->get('reports/:action(.:format)', 'reports');

$routing->post('requests/:id/close', 'requests#close', $constraints);
$routing->post('requests/:id/restore', 'requests#restore', $constraints);

$routing->post('subscribers/:id/requests/new', 'subscribers#new_request', $constraints);
$routing->post('subscribers/:id/messages/new', 'subscribers#new_message', $constraints);
$routing->post('subscribers/:id/billing/new', 'subscribers#new_billing_detail', $constraints);
$routing->post('subscribers/:id/billing/:billing_detail_id/destroy', 'subscribers#rollback_billing_detail', array('id' => '\d+', 'billing_detail_id' => '\d+'));
$routing->get('subscribers/:id/memo.:format', 'subscribers#download_memo', $constraints);
$routing->get('subscribers/:id/cancel.:format', 'subscribers#download_cancel', $constraints);
$routing->get('subscribers/:id/complaint.:format', 'subscribers#download_complaint', $constraints);
$routing->get('subscribers/:id/envelope.:format', 'subscribers#download_envelope', $constraints);
$routing->post('subscribers/:id/restore', 'subscribers#restore', $constraints);

$routing->get('houses/:id/passport(.:format)', 'houses#passport', $constraints);
$routing->get('houses/:id/memos.:format', 'houses#download_memos', $constraints);
$routing->get('houses/:id/amplifiers(.:format)', 'amplifiers#index', $constraints);
$routing->get('houses/:id/subscribers/edit', 'houses#edit_subscribers', $constraints);
$routing->post('houses/:id/subscribers/edit', 'houses#update_subscribers', $constraints);
$routing->get('houses/:id/subscribers(.:format)', 'houses#subscribers', $constraints);
$routing->post('houses/:id/billing/:billing_detail_id/destroy', 'houses#rollback_billing_detail', array('id' => '\d+', 'billing_detail_id' => '\d+'));
$routing->get('inspections/:id/new', 'inspections#add', $constraints);
$routing->post('inspections/:id/new', 'inspections#create', $constraints);

$routing->get('amplifiers/:id/new', 'amplifiers#add', $constraints);
$routing->post('amplifiers/:id/new', 'amplifiers#create', $constraints);
$routing->post('amplifiers/:id/upload_scans', 'amplifiers#upload_scans', $constraints);

$routing->get('billing/:id/period_details', 'billing#period_details', $constraints);
$routing->get('billing/:id/file_details', 'billing#file_details', $constraints);
$routing->get('billing/:id/original_file.txt', 'billing#download_file', $constraints);
$routing->match('billing/:id/new_account_detail', 'billing#new_account_detail', $constraints);
$routing->get('billing/:id/account_details(.:format)', 'billing#account_details', $constraints);
$routing->match('billing/:id/fix_file_detail', 'billing#fix_file_detail', $constraints);
$routing->post('billing/:id/dismiss_request', 'billing#dismiss_request', $constraints);
$routing->post('billing/:id/rollback_detail', 'billing#rollback_detail', $constraints);
$routing->post('billing/:id/rollback_file', 'billing#rollback_file', $constraints);
$routing->post('billing/:id/send_sms', 'billing#send_sms', $constraints);
$routing->get('billing/:id/memos.:format', 'billing#download_memos', $constraints);

$routing->post('arrears/:id/cell', 'arrears#cell', $constraints);
$routing->post('arrears/:id/home', 'arrears#home', $constraints);
$routing->get('arrears/:id/next', 'arrears#next', $constraints);
$routing->get('arrears/:id/prev', 'arrears#prev', $constraints);

$routing->get('channels/:id/forget_known', 'channels#forget_known', $constraints);
$routing->get('channels/:id/forget_unknown', 'channels#forget_unknown', $constraints);

$routing->get('roles/:id/rights', 'roles#rights', $constraints);
$routing->post('roles/:id/rights', 'roles#update_rights', $constraints);
$routing->get('roles/:id/switch', 'roles#switch_role', $constraints);
$routing->get('roles/restore', 'roles#restore_role', $constraints);


# RESTful routing
$routing->get(':controller/new(.:format)', '#add');
$routing->get(':controller/:id/edit(.:format)', '#edit', $constraints);
$routing->get(':controller/:id(.:format)', '#show', $constraints);
$routing->get(':controller(.:format)', '#index');
$routing->post(':controller(.:format)', '#create');
$routing->post(':controller/:id/destroy(.:format)', '#destroy', $constraints);
$routing->post(':controller/:id(.:format)', '#update', $constraints);
$routing->delete(':controller/:id(.:format)', '#destroy', $constraints);

# Common routing
$routing->match(':controller(/:action(/:id))(.:format)', $constraints);

# 404 error
$routing->get(':url', 'errors#show_404');
?>