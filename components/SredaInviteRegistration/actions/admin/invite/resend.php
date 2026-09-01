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
$sent = SredaInvite::sendPersonalInvite($admin->guid, $token);
if(empty($sent['ok'])) {
	$errors = array(
			'rate_limited' => 'sreda:invite:send:cooldown',
			'from_not_configured' => 'sreda:invite:from:error',
			'smtp_not_configured' => 'sreda:invite:smtp:error',
			'send_failed' => 'sreda:invite:send:error',
			'not_active' => 'sreda:invite:not:active',
			'invalid' => 'sreda:invite:invalid',
			'database' => 'sreda:invite:error',
	);
	$key = isset($errors[$sent['error']]) ? $errors[$sent['error']] : 'sreda:invite:send:error';
	echo json_encode(array(
		'success' => false,
		'error' => ossn_print($key),
		'retry_after' => isset($sent['retry_after']) ? (int) $sent['retry_after'] : 0,
	), JSON_UNESCAPED_UNICODE);
	exit;
}

echo json_encode(array(
		'success' => true,
		'invite_id' => $sent['id'],
		'email' => $sent['invited_email'],
		'invite_url' => $sent['invite_url'],
		'send_count' => $sent['send_count'],
		'message' => ossn_print('sreda:invite:resent', array($sent['invited_email'])),
), JSON_UNESCAPED_UNICODE);
