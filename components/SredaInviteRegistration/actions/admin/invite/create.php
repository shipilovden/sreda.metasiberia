<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';
header('Content-Type: application/json; charset=UTF-8');

if(!ossn_isAdminLoggedin()) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:admin:only')), JSON_UNESCAPED_UNICODE);
	exit;
}

$admin  = ossn_loggedin_user();
$invite = SredaInvite::createForAdmin($admin->guid);
if(!$invite) {
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:error')), JSON_UNESCAPED_UNICODE);
	exit;
}

echo json_encode(array(
		'success'    => true,
		'invite_url' => $invite['invite_url'],
		'message'    => ossn_print('sreda:invite:created'),
), JSON_UNESCAPED_UNICODE);
