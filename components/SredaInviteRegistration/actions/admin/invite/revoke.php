<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';
header('Content-Type: application/json; charset=UTF-8');

if(!ossn_isAdminLoggedin()) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:admin:only')), JSON_UNESCAPED_UNICODE);
	exit;
}

$admin = ossn_loggedin_user();
$token = input('invite_token', true);
$inviteId = (int) input('invite_id', true);
$revoked = $inviteId > 0
		? SredaInvite::revokeForAdminById($admin->guid, $inviteId)
		: SredaInvite::revokeForAdmin($admin->guid, $token);
if(!$revoked) {
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:revoke:error')), JSON_UNESCAPED_UNICODE);
	exit;
}

echo json_encode(array(
		'success' => true,
		'invite_id' => $inviteId,
		'message' => ossn_print('sreda:invite:revoked'),
), JSON_UNESCAPED_UNICODE);
