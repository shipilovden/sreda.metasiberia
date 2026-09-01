<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';
header('Content-Type: application/json; charset=UTF-8');

if(!ossn_isAdminLoggedin()) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:admin:only')), JSON_UNESCAPED_UNICODE);
	exit;
}

$enabled = input('invite_only') === 'on';
if(!SredaInvite::setInviteOnly($enabled)) {
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:settings:error')), JSON_UNESCAPED_UNICODE);
	exit;
}

echo json_encode(array(
		'success' => true,
		'message' => ossn_print('sreda:invite:settings:saved'),
), JSON_UNESCAPED_UNICODE);
