<?php
require_once SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php';

/**
 * Return an OSSN signup-compatible error before the unchanged core action is
 * included. This also handles direct POST requests which bypass the browser.
 *
 * @param string $code
 * @return void
 */
function sreda_invite_registration_error($code) {
		$messages = array(
				'required'  => ossn_print('sreda:invite:registration:required'),
				'invalid'   => ossn_print('sreda:invite:registration:invalid'),
				'used'      => ossn_print('sreda:invite:registration:used'),
				'reserved'  => ossn_print('sreda:invite:registration:reserved'),
		);
		$message = isset($messages[$code]) ? $messages[$code] : $messages['invalid'];
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(array(
				'dataerr'             => $message,
				'invite_error'        => true,
				'invite_error_code'   => $code,
				'invite_error_message' => $message,
				'invite_error_title'  => ossn_print('sreda:invite:registration:title'),
		), JSON_UNESCAPED_UNICODE);
		exit;
}

$reservation = false;
if(SredaInvite::isInviteOnlyEnabled()) {
		$token = input('invite_token', true);
		if(empty($token)) {
				sreda_invite_registration_error('required');
		}
		$reservation = SredaInvite::reserve($token);
		if(empty($reservation['ok'])) {
				sreda_invite_registration_error($reservation['error']);
		}

		$GLOBALS['sreda_invite_registration_reservation'] = $reservation;
		$GLOBALS['sreda_invite_registration_created']      = false;
		$GLOBALS['sreda_invite_registration_create_started'] = false;
		ob_start();
		$GLOBALS['sreda_invite_registration_buffer_level'] = ob_get_level();
		register_shutdown_function('sreda_invite_registration_finalize');
}

// Keep OSSN's validation, password hashing, activation and response format.
// The route is replaced only so this guard cannot be skipped.
require ossn_route()->actions . 'user/register.php';
