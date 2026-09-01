<?php
define('SREDA_INVITE_REGISTRATION', ossn_route()->com . 'SredaInviteRegistration/');

ossn_register_class(array(
		'SredaInvite' => SREDA_INVITE_REGISTRATION . 'classes/SredaInvite.php',
));

function sreda_invite_registration_init() {
		$inviteToken = input('invite', true);
		if($inviteToken !== false && $inviteToken !== '' && !headers_sent()) {
			header('Referrer-Policy: no-referrer');
		}

		// The public form remains the standard OSSN form; only a signed-by-URL
		// hidden value is added to it when the visitor came through an invite.
		ossn_extend_view('forms/signup/before/submit', 'sredainvite/signup');
		ossn_extend_view('ossn/site/head', 'sredainvite/referrer');
		ossn_extend_view('ossn/site/head', 'sredainvite/seo');
		ossn_extend_view('css/ossn.default', 'css/sreda_invite');

		ossn_new_js('sreda.invite.registration', 'js/sreda_invite');
		ossn_load_js('sreda.invite.registration');

		// This route is deliberately replaced without changing OSSN core. The
		// wrapper gates the request and then includes the unchanged core action.
		ossn_register_action('user/register', SREDA_INVITE_REGISTRATION . 'actions/user/register.php');
		ossn_register_page('sreda', 'sreda_invite_registration_page_handler');

		// OssnUser::addUser() calls this hook immediately before inserting the
		// user. It distinguishes pre-create validation from an unknown result.
		ossn_add_hook('user', 'create', 'sreda_invite_registration_create_started', 1);
		ossn_register_callback('user', 'created', 'sreda_invite_registration_user_created', 1);

		if(ossn_isAdminLoggedin()) {
				ossn_register_sections_menu('newsfeed', array(
						'name'   => 'sreda_invite_registration',
						'text'   => ossn_print('sreda:invite:menu'),
						'url'    => 'javascript:void(0);',
						'parent' => 'links',
						'icon'   => 'fa fa-user-plus',
				));

				ossn_register_action('sreda/invite/create', SREDA_INVITE_REGISTRATION . 'actions/admin/invite/create.php');
				ossn_register_action('sreda/invite/settings', SREDA_INVITE_REGISTRATION . 'actions/admin/invite/settings.php');
		}
}

/**
 * Return the administrator invite dialog as an HTML page fragment.
 *
 * The OSSN action dispatcher forces every XHR action response to JSON. The
 * dialog is an HTML fragment, so it must use a page endpoint instead.
 *
 * @param array  $pages
 * @param string $handler
 * @return void
 */
function sreda_invite_registration_page_handler($pages, $handler) {
		if(empty($pages[0]) || $pages[0] !== 'invite' || empty($pages[1]) || $pages[1] !== 'dialog') {
				http_response_code(404);
				return;
		}

		if(!ossn_isAdminLoggedin()) {
			http_response_code(403);
			echo '<div class="title">' . htmlspecialchars(ossn_print('sreda:invite:title'), ENT_QUOTES, 'UTF-8') . '</div>';
			echo '<div class="contents"><div class="ossn-box-inner"><p>' . htmlspecialchars(ossn_print('sreda:invite:admin:only'), ENT_QUOTES, 'UTF-8') . '</p></div></div>';
				return;
		}

		$admin  = ossn_loggedin_user();
		$invite = SredaInvite::getOrCreateForAdmin($admin->guid);
		if(!$invite) {
			echo '<div class="title">' . htmlspecialchars(ossn_print('sreda:invite:title'), ENT_QUOTES, 'UTF-8') . '</div>';
			echo '<div class="contents"><div class="ossn-box-inner"><p>' . htmlspecialchars(ossn_print('sreda:invite:error'), ENT_QUOTES, 'UTF-8') . '</p></div></div>';
				return;
		}

		echo ossn_plugin_view('sredainvite/dialog', array(
				'invite'     => $invite,
				'invite_only' => SredaInvite::isInviteOnlyEnabled(),
		));
}

/**
 * Mark the point at which the core has started the user creation path.
 *
 * @param string $hook
 * @param string $type
 * @param mixed $returnValue
 * @param mixed $params
 * @return mixed
 */
function sreda_invite_registration_create_started($hook, $type, $returnValue, $params) {
		if(!empty($GLOBALS['sreda_invite_registration_reservation'])) {
				$GLOBALS['sreda_invite_registration_create_started'] = true;
		}
		return $returnValue;
}

/**
 * Release only a reservation whose response is a normal core validation error
 * from before OssnUser::addUser(). Any unknown or post-create result stays
 * reserved until explicit administrator rotation.
 *
 * @return void
 */
function sreda_invite_registration_finalize() {
		$reservation   = $GLOBALS['sreda_invite_registration_reservation'] ?? false;
		$bufferLevel   = $GLOBALS['sreda_invite_registration_buffer_level'] ?? false;
		$createStarted = !empty($GLOBALS['sreda_invite_registration_create_started']);
		$created       = !empty($GLOBALS['sreda_invite_registration_created']);
		$release       = false;

		if($bufferLevel && ob_get_level() >= $bufferLevel) {
				$contents = ob_get_contents();
				$payload   = is_string($contents) ? json_decode(trim($contents), true) : null;
				$lastError = error_get_last();
				$fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
				$hasFatal = is_array($lastError) && in_array($lastError['type'], $fatalTypes, true);
				$normalValidation = is_array($payload)
						&& empty($payload['success'])
						&& ((isset($payload['error']) && (string) $payload['error'] === '1') || !empty($payload['dataerr']))
						&& empty($payload['invite_error']);
				$release = !$created && !$createStarted && !$hasFatal
						&& (!function_exists('connection_aborted') || connection_aborted() === 0)
						&& $normalValidation;

				if($release && $reservation) {
						SredaInvite::release($reservation);
				}
				ob_end_flush();
		}
}

/**
 * Consume an invite only after OssnUser::addUser() reported success.
 *
 * @param string $callback
 * @param string $type
 * @param array $params
 * @return void
 */
function sreda_invite_registration_user_created($callback, $type, $params) {
		if(empty($GLOBALS['sreda_invite_registration_reservation']) || empty($params['guid'])) {
				return;
		}
		$consumed = SredaInvite::consume($GLOBALS['sreda_invite_registration_reservation'], $params['guid']);
		if($consumed) {
				$GLOBALS['sreda_invite_registration_created'] = true;
		} else {
				error_log('SREDA invite was not marked used after successful user creation.');
		}
}

ossn_register_callback('ossn', 'init', 'sreda_invite_registration_init');
