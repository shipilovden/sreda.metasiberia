<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';
header('Content-Type: application/json; charset=UTF-8');

if(!ossn_isAdminLoggedin()) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:admin:only')), JSON_UNESCAPED_UNICODE);
	exit;
}

$admin = ossn_loggedin_user();
$inviteId = (int) input('invite_id', true);
$invite = SredaInvite::rotatePersonalForAdmin($admin->guid, $inviteId);
if(empty($invite['ok'])) {
	$errors = array(
			'email_exists' => 'sreda:invite:email:exists',
			'database' => 'sreda:invite:error',
			'invalid' => 'sreda:invite:invalid',
	);
	$key = isset($errors[$invite['error']]) ? $errors[$invite['error']] : 'sreda:invite:error';
	echo json_encode(array('success' => false, 'error' => ossn_print($key)), JSON_UNESCAPED_UNICODE);
	exit;
}

$sent = SredaInvite::sendPersonalInvite($admin->guid, $invite['token']);
if(empty($sent['ok'])) {
	$errors = array(
			'from_not_configured' => 'sreda:invite:from:error',
			'smtp_not_configured' => 'sreda:invite:smtp:error',
			'send_failed' => 'sreda:invite:send:error',
			'database' => 'sreda:invite:error',
			'rate_limited' => 'sreda:invite:send:cooldown',
	);
	$key = isset($errors[$sent['error']]) ? $errors[$sent['error']] : 'sreda:invite:send:error';
	echo json_encode(array(
			'success' => false,
			'error' => ossn_print($key),
			'email' => $invite['invited_email'],
			'invite_id' => $invite['id'],
			'token' => $invite['token'],
			'invite_url' => $invite['invite_url'],
			'rotation' => true,
			'send_failed' => true,
	), JSON_UNESCAPED_UNICODE);
	exit;
}

echo json_encode(array(
		'success' => true,
		'invite_id' => $sent['id'],
		'email' => $sent['invited_email'],
		'invite_url' => $sent['invite_url'],
		'send_count' => $sent['send_count'],
		'message' => ossn_print('sreda:invite:sent', array($sent['invited_email'])),
), JSON_UNESCAPED_UNICODE);
