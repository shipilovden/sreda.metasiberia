<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';
header('Content-Type: application/json; charset=UTF-8');

if(!ossn_isAdminLoggedin()) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'error' => ossn_print('sreda:invite:admin:only')), JSON_UNESCAPED_UNICODE);
	exit;
}

$admin  = ossn_loggedin_user();
$email = SredaInvite::normalizeEmail(input('email', true));
$invite = SredaInvite::createPersonalForAdmin($admin->guid, $email);
if(empty($invite['ok'])) {
		$errors = array(
				'invalid_email' => 'sreda:invite:email:invalid',
				'email_exists' => 'sreda:invite:email:exists',
				'existing_token_unavailable' => 'sreda:invite:existing:unavailable',
				'database' => 'sreda:invite:error',
		);
		$key = isset($errors[$invite['error']]) ? $errors[$invite['error']] : 'sreda:invite:error';
		echo json_encode(array('success' => false, 'error' => ossn_print($key)), JSON_UNESCAPED_UNICODE);
	exit;
}

$sent = SredaInvite::sendPersonalInvite($admin->guid, $invite['token']);
if(empty($sent['ok'])) {
		$errors = array(
				'rate_limited' => 'sreda:invite:send:cooldown',
				'from_not_configured' => 'sreda:invite:from:error',
				'smtp_not_configured' => 'sreda:invite:smtp:error',
				'send_failed' => 'sreda:invite:send:error',
				'database' => 'sreda:invite:error',
		);
		$key = isset($errors[$sent['error']]) ? $errors[$sent['error']] : 'sreda:invite:send:error';
		echo json_encode(array(
				'success' => false,
				'error' => ossn_print($key),
				'email' => $invite['invited_email'],
				'invite_id' => $invite['id'],
				'token' => $invite['token'],
				'invite_url' => $invite['invite_url'],
				'send_failed' => true,
		), JSON_UNESCAPED_UNICODE);
		exit;
}

echo json_encode(array(
		'success'    => true,
		'invite_id'  => $sent['id'],
		'email'      => $sent['invited_email'],
		'invite_url' => $sent['invite_url'],
		'send_count' => $sent['send_count'],
		'message'    => ossn_print('sreda:invite:sent', array($sent['invited_email'])),
), JSON_UNESCAPED_UNICODE);
