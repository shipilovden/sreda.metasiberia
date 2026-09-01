<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network (OSSN)
 * @author    OSSN Core Team <info@openteknik.com>
 * @copyright (C) OpenTeknik LLC
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
define('__THEMEDIR__', ossn_route()->themes . 'goblue/');

ossn_register_callback('ossn', 'init', 'ossn_goblue_theme_init');

function ossn_goblue_theme_init() {
		//add bootstrap
		ossn_new_css('bootstrap.min', 'css/bootstrap/bootstrap.min.css');

		ossn_new_css('ossn.default', 'css/core/default');
		ossn_new_css('ossn.admin.default', 'css/core/administrator');

		//load bootstrap
		ossn_load_css('bootstrap.min', 'admin');
		ossn_load_css('bootstrap.min');

		ossn_load_css('ossn.default');
		ossn_load_css('ossn.admin.default', 'admin');

		ossn_extend_view('ossn/admin/head', 'ossn_goblue_admin_head');
		ossn_extend_view('ossn/site/head', 'ossn_goblue_head');
		ossn_extend_view('ossn/site/head', 'ossn_goblue_angular_styles');
		ossn_extend_view('js/opensource.socialnetwork', 'js/goblue');
		ossn_extend_view('js/opensource.socialnetwork', 'js/sibcore_snow_boids');
		ossn_extend_view('profile/newsfeed/info', 'goblue_search_bar_sidebar');
		
		if(ossn_isAdminLoggedin()) {
				ossn_register_menu_item('admin/sidemenu', array(
						'name'   => 'admin:theme:goblue',
						'text'   => ossn_print('admin:theme:goblue'),
						'href'   => ossn_site_url('administrator/settings/goblue'),
						'parent' => 'admin:sidemenu:themes',
				));
				ossn_register_site_settings_page('goblue', 'settings/admin/goblue');
				ossn_register_action('goblue/settings', __THEMEDIR__ . 'actions/settings.php');
				//[E] Allow custom logos to be saved with different file name #2334
				ossn_register_action('goblue/settings/logos_bgs_reset', __THEMEDIR__ . 'actions/logos_bgs_reset.php');
		}
		ossn_extend_view('ossn/site/head', 'theme_meta_favicon');
		ossn_extend_view('ossn/admin/head', 'theme_meta_favicon');
}

function goblue_search_bar_sidebar(){
		return ossn_view_form('search', array(
								'component' => 'OssnSearch',
								'class' => 'ossn-search',
								'autocomplete' => 'off',
								'method' => 'get',
								'security_tokens' => false,
								'action' => ossn_site_url("search"),
		), false);	
}
function theme_meta_favicon() {
		$icon = ossn_add_cache_to_url(ossn_theme_url() . 'images/favicon.svg?v=waypoints');
		return "\r\n<link rel='icon' href='{$icon}' type='image/svg+xml' />";
}
/**
 * Render a local Lucide icon.
 *
 * The first icons are kept here while the rest of the Font Awesome usage is
 * migrated in separate, verifiable batches. The returned SVG inherits the
 * current text color and keeps the surrounding OSSN markup unchanged.
 *
 * @param string $name Icon name.
 * @param string $class Additional CSS classes.
 *
 * @return string
 */
function ossn_goblue_lucide_icon($name, $class = '') {
		$icons = array(
				'list' => '<path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path>',
				'menu' => '<line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line>',
				'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>',
				'chevron-down' => '<path d="m6 9 6 6 6-6"></path>',
				'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
				'users-round' => '<path d="M18 21a8 8 0 0 0-16 0"></path><circle cx="10" cy="8" r="5"></circle><path d="M22 20c0-3.37-2-6.5-6-7.5"></path>',
				'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>',
				'globe' => '<circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
				'map-pin' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 1 1 16 0"></path><circle cx="12" cy="10" r="3"></circle>',
				'image' => '<rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>',
				'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle>',
				'repeat-2' => '<path d="m2 9 3-3 3 3"></path><path d="M13 18H7a2 2 0 0 1-2-2V6"></path><path d="m22 15-3 3-3-3"></path><path d="M11 6h6a2 2 0 0 1 2 2v10"></path>',
				'quote' => '<path d="M3 21c3 0 7-1 7-8V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h2c0 2-1 3-3 4v4z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h2c0 2-1 3-3 4v4z"></path>',
				'share-2' => '<circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line>',
		);
		if(!isset($icons[$name])) {
				return '';
		}
		$classes = trim('ossn-lucide-icon ' . $class);
		return '<svg class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '" xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
}
function ossn_goblue_set_custom_logos_bgs_setting($key, $val) {
		$settings = ossn_goblue_get_custom_logos_bgs_setting();
		if(!empty($key) && !empty($val)) {
				if(!$settings) {
						$settings = array();
				}
				$settings[$key] = $val;
				$json           = json_encode($settings);
				$config         = ossn_route()->themes . 'goblue/logos_backgrounds/config.json';
				return file_put_contents($config, $json);
		}
		return false;
}
function ossn_goblue_get_custom_logos_bgs_setting() {
		$config = ossn_route()->themes . 'goblue/logos_backgrounds/config.json';
		if(file_exists($config)) {
				$json = file_get_contents($config);
				if(!empty($json)) {
						$json = json_decode($json, true);
						if(json_last_error() === JSON_ERROR_NONE) {
								return $json;
						}
				}
		}
		return false;
}
function ossn_goblue_head() {
		$head = array();
		$seoTitle       = 'SREDA';
		$seoDescription = 'Connect. Share. Belong.';
		$seoSiteUrl     = rtrim(ossn_site_url(), '/') . '/';
		$seoImageUrl    = ossn_site_url('sreda_opengraf.png');
		$seoEscape      = static function ($value) {
			return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
		};

		$head[] = ossn_html_css(array(
				'href' => ossn_theme_url() . 'vendors/fontawesome/6.7.2/css/all.min.css',
		));
		$head[] = ossn_html_js(array(
				'src' => ossn_theme_url() . 'vendors/bootstrap/js/bootstrap.min.js?v5.3.8',
		));
		$head[] = '<meta name="description" content="' . $seoEscape($seoDescription) . '" />';
		$head[] = '<link rel="canonical" href="' . $seoEscape($seoSiteUrl) . '" />';
		$head[] = '<meta property="og:type" content="website" />';
		$head[] = '<meta property="og:site_name" content="SREDA" />';
		$head[] = '<meta property="og:locale" content="ru_RU" />';
		$head[] = '<meta property="og:title" content="' . $seoEscape($seoTitle) . '" />';
		$head[] = '<meta property="og:description" content="' . $seoEscape($seoDescription) . '" />';
		$head[] = '<meta property="og:url" content="' . $seoEscape($seoSiteUrl) . '" />';
		$head[] = '<meta property="og:image" content="' . $seoEscape($seoImageUrl) . '" />';
		$head[] = '<meta property="og:image:secure_url" content="' . $seoEscape($seoImageUrl) . '" />';
		$head[] = '<meta property="og:image:type" content="image/png" />';
		$head[] = '<meta property="og:image:alt" content="SREDA" />';
		$head[] = '<meta name="twitter:card" content="summary_large_image" />';
		$head[] = '<meta name="twitter:title" content="' . $seoEscape($seoTitle) . '" />';
		$head[] = '<meta name="twitter:description" content="' . $seoEscape($seoDescription) . '" />';
		$head[] = '<meta name="twitter:image" content="' . $seoEscape($seoImageUrl) . '" />';
		return implode('', $head);
}
/**
 * Keep user-facing SibCore surfaces angular and consistent with the sidebar.
 * Avatar and presence images intentionally keep their circular shape.
 *
 * @return string
 */
function ossn_goblue_angular_styles() {
		return <<<'HTML'
<style id="ossn-sibcore-angular-styles">
/* SibCore angular UI: surfaces, controls and cards use straight corners. */
.ossn-page-container *:not(img):not(svg),
body .ossn-message-box *:not(img):not(svg) {
	border-radius: 0 !important;
}

.ossn-page-container .btn,
.ossn-page-container .btn-action,
.ossn-page-container .btn-standalone-grey,
.ossn-page-container .form-control,
.ossn-page-container .form-select,
.ossn-page-container input:not([type="checkbox"]):not([type="radio"]),
.ossn-page-container select,
.ossn-page-container textarea,
.ossn-page-container .dropdown-menu,
.ossn-page-container .alert,
.ossn-page-container .card,
.ossn-page-container .panel,
.ossn-page-container .modal-content,
.ossn-page-container .modal-header,
.ossn-page-container .modal-footer {
	border-radius: 0 !important;
}

.ossn-page-container .ossn-wall-item,
.ossn-page-container .ossn-wall-container,
.ossn-page-container .ossn-widget,
.ossn-page-container .ossn-messages,
.ossn-page-container .ossn-profile .top-container,
.ossn-page-container .profile-menu-hr-container,
.ossn-page-container .ossn-profile-edit-layout,
.ossn-page-container .ossn-users-list-item,
.ossn-page-container .ossn-output-users-list .user-item-card,
.ossn-page-container .ossn-photos-wall,
.ossn-page-container .ossn-group-profile .profile-header {
	border-radius: 0 !important;
	box-shadow: 0 3px 12px rgba(15, 23, 42, 0.12) !important;
}

/* Feed composer, privacy selector and publication controls. */
.ossn-page-container .ossn-wall-container .wall-tabs,
.ossn-page-container .ossn-wall-container .wall-tabs .item,
.ossn-page-container .ossn-wall-container .controls,
.ossn-page-container .ossn-wall-container .controls li,
.ossn-page-container .ossn-wall-container-data,
.ossn-page-container .ossn-wall-privacy,
.ossn-page-container .ossn-wall-privacy-dummy,
.ossn-page-container .ossn-wall-post,
.ossn-page-container .ossn-wall-token,
.ossn-page-container .menu-likes-comments-share > li a,
.ossn-page-container .comments-list,
.ossn-page-container .comments-list .comment-contents,
.ossn-page-container .comment-box,
.ossn-page-container .comment-post-btn,
.ossn-page-container .ossn-comments-view-all {
	border-radius: 0 !important;
}

/* Profile actions: edit, messages, friendship and cover controls. */
.ossn-page-container .ossn-profile .btn,
.ossn-page-container .ossn-profile .btn-action,
.ossn-page-container .ossn-profile-extra-menu .btn,
.ossn-page-container .ossn-profile .upload-photo,
.ossn-page-container .ossn-covers-uploading-annimation,
.ossn-page-container .profile-hr-menu,
.ossn-page-container .profile-edit-layout-title {
	border-radius: 0 !important;
}

/* Messages and notification surfaces. */
.ossn-page-container .ossn-messages .messages-recent,
.ossn-page-container .ossn-messages .messages-from,
.ossn-page-container .ossn-messages .messages-from .user-item,
.ossn-page-container .ossn-messages .message-with,
.ossn-page-container .ossn-messages .message-form-form,
.ossn-page-container .ossn-messages .message-box-recieved,
.ossn-page-container .ossn-messages .message-box-sent,
.ossn-page-container .ossn-message-attach-photo,
.ossn-page-container .ossn-message-icon-attachment,
.ossn-page-container .ossn-recent-messages-toggle,
.ossn-page-container .ossn-notifications-all,
.ossn-page-container .ossn-notification-page {
	border-radius: 0 !important;
}

/* The request control and the online-friends rail have separate actions. */
#sibcore-friends-toggle > a {
	box-sizing: border-box;
	display: block;
	color: #fff !important;
}

#sibcore-friends-toggle .ossn-lucide-icon,
#ossn-notif-friends .ossn-lucide-icon {
	color: #fff !important;
	stroke: #fff !important;
	fill: none !important;
}

/* Other public sections use the same square card language. */
.ossn-page-container .ossn-photos li,
.ossn-page-container .group-header-more,
.ossn-page-container .ossn-list-users .ossn-users-list-item,
.ossn-page-container .ossn-profile-module,
.ossn-page-container .ossn-notification-messages,
.ossn-page-container .ossn-notification-messages .user-item {
	border-radius: 0 !important;
}

/* Global message dialog lives outside .ossn-page-container. */
body .ossn-message-box,
body .ossn-message-box .title,
body .ossn-message-box .contents,
body .ossn-message-box .control,
body .ossn-message-box .control .controls .btn {
	border-radius: 0 !important;
}

/* Preserve the intentional circular identity/presence markers. */
.ossn-page-container .profile-photo,
.ossn-page-container .user-img,
.ossn-page-container .user-icon-small,
.ossn-page-container .user-icon,
.ossn-page-container .user-icon-smaller,
.ossn-page-container .comment-user-img,
.ossn-page-container .profile-photo img,
.ossn-page-container .ossn-wall-userimage-form img,
.ossn-page-container .ossn-inmessage-status-circle,
.ossn-page-container .ossn-recent-message-status-online .ossn-inmessage-status-circle,
.ossn-page-container .ossn-recent-message-status-offline .ossn-inmessage-status-circle {
	border-radius: 50% !important;
}

/* Mobile shell: the navigation overlays the feed instead of moving or hiding it. */
@media (max-width: 991px) {
	.ossn-page-container.sidebar-open-page-container,
	.ossn-page-container.sidebar-open-page-container-no-annimation {
		margin-left: 0 !important;
		width: 100% !important;
	}

	.ossn-page-container.sidebar-open-page-container .topbar,
	.ossn-page-container.sidebar-open-page-container-no-annimation .topbar {
		width: 100% !important;
	}

	/* Keep the content visible if an older cached script left the legacy class. */
	.ossn-page-container .sidebar-hide-contents-xs,
	.ossn-page-container .sidebar-hide-contents-xs .ossn-inner-page,
	.topbar .right-side.sidebar-hide-contents-xs {
		display: block !important;
	}

	.sidebar {
		left: 0;
		top: 0;
		bottom: 0;
		z-index: 1100;
	}

	/* Keep the hamburger available above the open drawer. */
	.topbar {
		z-index: 1150;
	}

	.topbar .left-side,
	.topbar .topbar-menu-left,
	.topbar .topbar-menu-left li,
	.topbar .topbar-menu-left li a {
		position: relative;
		z-index: 1201;
		color: #fff !important;
	}

	.topbar .topbar-menu-left li a .ossn-lucide-icon,
	.topbar #sidebar-toggle .ossn-lucide-icon {
		color: #fff !important;
		stroke: #fff !important;
		fill: none !important;
	}

	.topbar .topbar-menu-left li:hover,
	.topbar .topbar-menu-left li:focus,
	.topbar .topbar-menu-left li:active,
	.topbar #sidebar-toggle:hover,
	.topbar #sidebar-toggle:focus,
	.topbar #sidebar-toggle:active {
		background-color: transparent !important;
	}

	#sidebar-toggle {
		position: relative;
		z-index: 1201;
	}

	/* The drawer starts below the fixed topbar, so its profile block is not covered. */
	.sidebar {
		top: 48px;
		height: calc(100vh - 48px);
	}

	/* The legacy chat layout adds a right margin while the drawer is open. */
	.ossn-inner-page {
		margin-right: 0 !important;
	}

	/* Keep the friends control reachable at the viewport edge while the menu is open. */
	.topbar .right-side {
		padding-right: 88px;
	}

	#ossn-notif-friends {
		position: fixed;
		top: 0;
		right: 44px;
		z-index: 1200;
		background: #1e293b;
	}

	#ossn-notif-friends > a {
		box-sizing: border-box;
		min-width: 44px;
		height: 48px;
		display: flex !important;
		align-items: center;
		justify-content: center;
		padding: 8px 10px !important;
	}

	#ossn-notif-friends .ossn-lucide-icon {
		width: 20px;
		height: 20px;
	}

	#sibcore-friends-toggle {
		position: fixed;
		top: 0;
		right: 0;
		z-index: 1200;
		background: #1e293b;
	}

	#sibcore-friends-toggle > a {
		min-width: 44px;
		height: 48px;
		display: flex !important;
		align-items: center;
		justify-content: center;
		padding: 8px 10px !important;
	}

	#sibcore-friends-toggle .ossn-lucide-icon {
		width: 20px;
		height: 20px;
	}

	/* Compact online-friends rail for phones. */
	.ossn-chat-windows-long {
		display: block !important;
		position: fixed;
		top: 48px;
		right: 0;
		bottom: 30px;
		width: 44px;
		min-height: 0;
		background: #1e293b;
		border-left: 1px solid #334155;
		z-index: 1050;
		transform: translateX(100%);
		visibility: hidden;
		pointer-events: none;
		transition: transform 0.2s ease, visibility 0.2s ease;
	}

	body.sibcore-friends-rail-open .ossn-chat-windows-long {
		transform: translateX(0);
		visibility: visible;
		pointer-events: auto;
	}

	.ossn-chat-windows-long .inner {
		box-sizing: border-box;
		width: 100%;
		height: 100% !important;
		margin-top: 0 !important;
		border-top: 1px solid rgba(255, 255, 255, 0.18);
		overflow-x: hidden;
		overflow-y: auto;
		scrollbar-width: thin;
		scrollbar-color: #64748b #1e293b;
	}

	.ossn-chat-windows-long .friends-list-item {
		box-sizing: border-box;
		width: 44px;
		padding: 3px 0;
		text-align: center;
		border-top: 1px solid rgba(255, 255, 255, 0.08);
		border-bottom: 1px solid rgba(0, 0, 0, 0.12);
	}

	.ossn-chat-windows-long .friends-list-item .friends-item-inner {
		box-sizing: border-box;
		width: 44px;
		height: 38px;
		margin: 0;
		padding: 2px 4px;
	}

	.ossn-chat-windows-long .friends-list-item .icon {
		display: inline-block;
		width: 34px;
		height: 34px;
	}

	.ossn-chat-windows-long .friends-list-item .user-icon-small {
		width: 34px;
		height: 34px;
		border-radius: 50% !important;
	}

	.ossn-chat-windows-long .friends-list-item .name {
		display: none;
	}

	.ossn-chat-windows-long .ossn-chat-none,
	.ossn-chat-windows-long .ossn-chat-pling {
		box-sizing: border-box;
		width: 44px;
		padding: 6px 2px;
		color: #fff;
		text-align: center;
		font-size: 11px;
	}

	/* Keep the chat bar and any open chat window directly above the footer. */
	.ossn-chat-base.d-none.d-lg-block {
		display: block !important;
	}

	.ossn-chat-base {
		left: 0;
		/* Override the OSSN Chat component's mobile bottom: 0 rule. */
		bottom: calc(var(--sibcore-mobile-footer-height) + var(--sibcore-mobile-chat-gap)) !important;
		width: 100%;
		height: 28px;
		margin: 0;
		padding: 0 44px 0 6px;
		box-sizing: border-box;
		z-index: 1060;
		pointer-events: none;
		display: flex !important;
		align-items: flex-end;
		justify-content: flex-end;
		gap: 4px;
	}

	.ossn-chat-base .ossn-chat-bar,
	.ossn-chat-base .ossn-chat-containers,
	.ossn-chat-base .friend-tab-item {
		pointer-events: auto;
	}

	.ossn-chat-base .ossn-chat-bar {
		width: 118px;
		min-width: 118px;
		flex: 0 0 118px;
		float: none;
		margin: 0 !important;
	}

	.ossn-chat-base .ossn-chat-bar .inner,
	.ossn-chat-base .friend-tab-item .friend-tab {
		box-sizing: border-box;
		height: 28px;
		min-height: 28px;
		margin: 0 !important;
		padding: 5px 7px;
		border-radius: 0 !important;
		overflow: hidden;
	}

	.ossn-chat-base .ossn-chat-bar .ossn-chat-inner-text,
	.ossn-chat-base .friend-tab-item .ossn-chat-inner-text {
		box-sizing: border-box;
		width: 100%;
		margin: 0;
		line-height: 16px;
		text-overflow: ellipsis;
		overflow: hidden;
		white-space: nowrap;
	}

	.ossn-chat-base .ossn-chat-containers {
		box-sizing: border-box;
		min-width: 0;
		max-width: 50%;
		display: flex;
		align-items: flex-end;
		gap: 4px;
	}

	.ossn-chat-base .friend-tab-item {
		box-sizing: border-box;
		width: 118px;
		min-width: 118px;
		flex: 0 0 118px;
		float: none;
		margin: 0 !important;
	}

	.ossn-chat-base .friend-tab-item:first-child {
		margin-right: 0 !important;
	}

	/* Keep an opened chat inside the viewport and above the bottom tabs. */
	.ossn-chat-base .friend-tab-item .tab-container {
		box-sizing: border-box;
		position: fixed;
		left: 12px;
		right: auto;
		bottom: calc(var(--sibcore-mobile-footer-height) + var(--sibcore-mobile-chat-gap) + 28px);
		width: min(330px, calc(100vw - 62px)) !important;
		height: min(400px, calc(100vh - 120px)) !important;
		max-height: calc(100vh - 120px);
		margin: 0 !important;
		border-radius: 0 !important;
		overflow: hidden;
		z-index: 1065;
	}

	.ossn-chat-base .friend-tab-item .tab-container .ossn-chat-tab-titles,
	.ossn-chat-base .friend-tab-item .tab-container .data {
		box-sizing: border-box;
		width: 100% !important;
	}

	.ossn-chat-base .friend-tab-item .tab-container .data {
		height: calc(100% - 48px) !important;
		max-height: none;
	}

	/* The compact closed-chat tab must never let the legacy absolute input overflow. */
	.ossn-chat-base .friend-tab-item .friend-tab {
		position: relative;
	}

	.ossn-chat-base .friend-tab-item .friend-tab form {
		box-sizing: border-box;
		position: fixed;
		left: 12px;
		bottom: calc(var(--sibcore-mobile-footer-height) + var(--sibcore-mobile-chat-gap) + 28px);
		width: min(330px, calc(100vw - 62px));
		height: 34px;
		margin: 0 !important;
		z-index: 1070;
	}

	.ossn-chat-base .friend-tab-item .friend-tab input[type='text'] {
		box-sizing: border-box;
		left: 0;
		top: 0;
		width: 100% !important;
		max-width: none;
		height: 34px;
		margin: 0 !important;
		padding-right: 72px;
		border-radius: 0 !important;
	}

	.ossn-chat-base .friend-tab-item .friend-tab .ossn-chat-icon-smile-set {
		top: 3px;
		right: 4px;
		width: 68px;
		height: 28px;
		margin: 0;
		padding: 0;
		z-index: 1;
		margin: 0 !important;
	}
}

@media (min-width: 992px) {
	/* The separate friends-rail toggle is a mobile-only control. */
	#sibcore-friends-toggle {
		display: none !important;
	}

	/* Keep the desktop chat-sound control visible and fully clickable. */
	.ossn-chat-windows-long .ossn-chat-pling {
		box-sizing: border-box;
		display: block;
		width: 100%;
		min-height: 32px;
		padding: 6px 0;
		text-align: center;
		color: #fff;
		cursor: pointer;
		pointer-events: auto;
		position: relative;
		z-index: 1;
	}

	.ossn-chat-windows-long .ossn-chat-pling i {
		pointer-events: none;
	}

	/* The active chat window must sit above the fixed footer on desktop too. */
	.ossn-chat-base {
		bottom: 32px;
		z-index: 1060;
	}
}

/* Decorative flocking snow stays behind the feed and outside the fixed chrome. */
#sibcore-boids-canvas {
	position: fixed;
	inset: 0;
	display: block;
	width: 100vw;
	height: 100vh;
	z-index: 0;
	pointer-events: none;
	opacity: 0.62;
}

/* Keep the complete application layer above the decorative canvas. */
.opensource-socalnetwork {
	position: relative;
	z-index: 1;
}

/* Public registration and login pages have no authenticated side rail: use the full
 * viewport and keep their transparent background so the shared snow flock remains
 * visible behind the form. Authenticated feed geometry is left unchanged. */
.opensource-socalnetwork:has(.sreda-registration-only) .ossn-page-container,
.opensource-socalnetwork:has(.sreda-registration-only) .ossn-inner-page,
.opensource-socalnetwork:has(.sreda-registration-only) .ossn-startup-wrapper,
.opensource-socalnetwork:has(.sreda-login-only) .ossn-page-container,
.opensource-socalnetwork:has(.sreda-login-only) .ossn-inner-page,
.opensource-socalnetwork:has(.sreda-login-only) .ossn-startup-wrapper {
	width: 100% !important;
	max-width: none !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}

.opensource-socalnetwork:has(.sreda-registration-only) .ossn-startup-wrapper,
.opensource-socalnetwork:has(.sreda-login-only) .ossn-startup-wrapper {
	background: transparent !important;
}

@media (max-width: 991px) {
	#sibcore-boids-canvas {
		width: 100vw;
		height: 100vh;
		opacity: 0.36;
	}
}

@media (prefers-reduced-motion: reduce) {
	#sibcore-boids-canvas {
		display: none !important;
	}
}

/* The authenticated footer remains fixed on phones and leaves room for the last post. */
@media (max-width: 1359px) {
	:root {
		--sibcore-mobile-footer-height: 30px;
		--sibcore-mobile-chat-gap: 0px;
	}

	.opensource-socalnetwork:has(.sidebar) .ossn-inner-page {
		padding-bottom: 50px;
	}

	.opensource-socalnetwork:has(.sidebar) .sibcore-site-footer {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 1040;
		box-sizing: border-box;
		height: var(--sibcore-mobile-footer-height);
		min-height: var(--sibcore-mobile-footer-height);
		margin: 0;
		padding: 4px 8px;
		background: #eaeaea;
		border-top: 1px solid #d2d2d2;
		overflow: hidden;
	}

	.sibcore-site-footer .footer-contents,
	.sibcore-site-footer .ossn-footer-menu {
		box-sizing: border-box;
		padding-bottom: 0;
		line-height: 20px;
		text-align: right;
		white-space: nowrap;
		overflow: hidden;
	}

	.sibcore-site-footer .ossn-footer-menu a {
		font-size: 11px;
	}
}

/* Final mobile chat dock: keep every compact chat tab in one row above the fixed footer. */
@media (max-width: 991px) {
	body .ossn-chat-base.d-none.d-lg-block {
		position: fixed !important;
		top: auto !important;
		right: 0 !important;
		bottom: calc(var(--sibcore-mobile-footer-height, 30px) + var(--sibcore-mobile-chat-gap, 0px)) !important;
		left: 0 !important;
		width: 100vw !important;
		height: 28px !important;
		min-height: 28px !important;
		margin: 0 !important;
		padding: 0 !important;
		display: flex !important;
		align-items: stretch !important;
		justify-content: flex-start !important;
		gap: 4px !important;
		transform: none !important;
		overflow: visible !important;
		z-index: 1060 !important;
		opacity: 1 !important;
		transition: opacity 0.5s ease !important;
	}

	/* The chat dock is moved into the application shell by goblue.js. Keep it below the
	 * drawer while it slides, without removing the rendered chat tabs. */
	body:has(.sidebar.sidebar-open) .ossn-chat-base.d-none.d-lg-block,
	body:has(.sidebar.sidebar-open-no-annimation) .ossn-chat-base.d-none.d-lg-block {
		z-index: 1060 !important;
		pointer-events: none !important;
	}

	/* Keep the dock under the closing drawer until the drawer animation has finished,
	 * then let it fade back above the feed instead of appearing abruptly. */
	body.sibcore-mobile-drawer-closing .ossn-chat-base.d-none.d-lg-block {
		z-index: 1060 !important;
		opacity: 0 !important;
		pointer-events: none !important;
	}

	body .ossn-chat-base.d-none.d-lg-block > .ossn-chat-bar,
	body .ossn-chat-base.d-none.d-lg-block > .ossn-chat-containers {
		position: static !important;
		top: auto !important;
		right: auto !important;
		bottom: auto !important;
		left: auto !important;
		transform: none !important;
		float: none !important;
		margin: 0 !important;
		height: 28px !important;
		min-height: 28px !important;
		align-self: stretch !important;
	}

	body .ossn-chat-base.d-none.d-lg-block > .ossn-chat-bar {
		flex: 0 0 118px !important;
		width: 118px !important;
		min-width: 118px !important;
	}

	body .ossn-chat-base.d-none.d-lg-block > .ossn-chat-containers {
		display: flex !important;
		flex: 0 1 auto !important;
		width: auto !important;
		max-width: calc(100vw - 122px) !important;
		min-width: 0 !important;
		align-items: stretch !important;
		gap: 4px !important;
	}

	body .ossn-chat-base.d-none.d-lg-block .friend-tab-item {
		position: static !important;
		top: auto !important;
		right: auto !important;
		bottom: auto !important;
		left: auto !important;
		transform: none !important;
		float: none !important;
		flex: 0 0 118px !important;
		width: 118px !important;
		min-width: 118px !important;
		height: 28px !important;
		min-height: 28px !important;
		margin: 0 !important;
	}

	body .ossn-chat-base.d-none.d-lg-block .friend-tab-item .friend-tab,
	body .ossn-chat-base.d-none.d-lg-block .ossn-chat-bar > .inner {
		position: relative !important;
		top: auto !important;
		right: auto !important;
		bottom: auto !important;
		left: auto !important;
		transform: none !important;
		height: 28px !important;
		min-height: 28px !important;
		margin: 0 !important;
	}

	/* The message window and its input remain above this dock. */
	body .ossn-chat-base.d-none.d-lg-block .friend-tab-item .tab-container,
	body .ossn-chat-base.d-none.d-lg-block .friend-tab-item .friend-tab form {
		bottom: calc(var(--sibcore-mobile-footer-height, 30px) + var(--sibcore-mobile-chat-gap, 0px) + 28px) !important;
	}
}

/* Final narrow-screen overflow guard. All topbar popups share one viewport-safe
 * surface so messages, notifications, friend requests and account links cannot
 * be clipped by their desktop trigger position. */
@media (max-width: 991px) {
	html,
	body {
		max-width: 100%;
		overflow-x: hidden;
	}

	.topbar .dropdown-menu,
	.topbar #notificationBox .ossn-notifications-box,
	.topbar .ossn-notifications-box {
		box-sizing: border-box;
		position: fixed !important;
		top: 48px !important;
		left: max(8px, env(safe-area-inset-left)) !important;
		right: max(8px, env(safe-area-inset-right)) !important;
		bottom: auto !important;
		width: auto !important;
		min-width: 0 !important;
		max-width: none !important;
		max-height: calc(100dvh - 56px) !important;
		margin: 0 !important;
		transform: none !important;
		z-index: 1205 !important;
		overflow-x: hidden !important;
		overflow-y: auto !important;
		-webkit-overflow-scrolling: touch;
	}

	/* Undo the legacy global mobile dropdown offset for the topbar only. */
	.topbar .dropdown-menu {
		padding: 4px 0;
	}

	/* Notification rows must shrink instead of preserving desktop column widths. */
	.topbar .ossn-notifications-box .notfi-meta,
	.topbar .ossn-notifications-all .notfi-meta {
		box-sizing: border-box;
		width: auto !important;
		min-width: 0;
		max-width: none;
		margin-left: 0;
		float: none !important;
	}

	.topbar .ossn-notification-messages .user-item-inner {
		display: flex;
		align-items: flex-start;
		gap: 8px;
		min-width: 0;
	}

	.topbar .ossn-notification-messages .user-item .image {
		flex: 0 0 50px;
		width: 50px;
		float: none;
	}

	.topbar .ossn-notification-messages .user-item .data {
		flex: 1 1 auto;
		width: auto !important;
		min-width: 0;
		float: none !important;
		overflow: hidden;
	}

	.topbar .ossn-notification-messages .user-item .data .name,
	.topbar .ossn-notification-messages .reply-text,
	.topbar .ossn-notification-messages .reply-text-from {
		box-sizing: border-box;
		width: auto !important;
		max-width: 100%;
		margin-left: 0;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.topbar .ossn-notification-messages .user-item-inner .time {
		float: none;
		display: block;
		margin: 0;
		white-space: nowrap;
	}

	.topbar .notification-friends li {
		width: 100%;
		min-width: 0;
	}

	.topbar .notification-friends .ossn-notifications-friends-inner {
		box-sizing: border-box;
		display: flex;
		align-items: center;
		gap: 8px;
		min-width: 0;
	}

	.topbar .notification-friends .image {
		flex: 0 0 50px;
		width: 50px;
		float: none;
	}

	.topbar .notification-friends .notfi-meta {
		display: flex;
		align-items: center;
		gap: 8px;
		flex: 1 1 auto;
	}

	.topbar .notification-friends .notfi-meta > .user {
		flex: 1 1 auto;
		min-width: 0;
		width: auto !important;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.topbar .notification-friends .controls {
		display: flex;
		align-items: center;
		flex: 0 0 auto;
		gap: 4px;
		margin: 0;
		float: none;
	}

	.topbar .notification-friends .controls form {
		display: block;
	}

	.topbar .notification-friends .controls .btn {
		max-width: 100%;
		padding: 3px 6px;
		white-space: nowrap;
	}

	/* Other fixed dialogs and media viewers also stay within the visual viewport. */
	body .ossn-message-box {
		box-sizing: border-box;
		width: min(470px, calc(100vw - 16px)) !important;
		min-width: 0 !important;
		max-width: calc(100vw - 16px);
		max-height: calc(100dvh - 16px);
	}

	.ossn-page-container img,
	.ossn-page-container video,
	.ossn-page-container iframe,
	.ossn-page-container embed,
	.ossn-page-container object {
		max-width: 100%;
	}

	.ossn-page-container input,
	.ossn-page-container select,
	.ossn-page-container textarea {
		box-sizing: border-box;
		max-width: 100%;
	}

	.ossn-page-container .ossn-wall-item,
	.ossn-page-container .ossn-wall-container,
	.ossn-page-container .ossn-widget,
	.ossn-page-container .ossn-layout-module,
	.ossn-page-container .ossn-page-contents {
		min-width: 0;
		max-width: 100%;
	}

	.ossn-page-container .ossn-wall-item,
	.ossn-page-container .ossn-widget,
	.ossn-page-container .ossn-layout-module {
		overflow-wrap: anywhere;
	}
}

@media (max-width: 480px) {
	.topbar .ossn-notifications-box .metadata,
	.topbar .ossn-notifications-box .messages-inner,
	.topbar .ossn-notifications-box .notification-friends {
		max-width: 100%;
		min-width: 0;
	}

	/* The popup keeps a readable two-column share grid even at 320 CSS pixels. */
	.ossn-wall-item .ossn-wall-share-menu {
		grid-template-columns: repeat(2, minmax(0, 1fr));
		max-height: min(70dvh, 420px);
	}
}
</style>
HTML;
}
function ossn_goblue_admin_head() {
		$head   = array();
		$head[] = ossn_html_css(array(
				'href' => ossn_theme_url() . 'vendors/fontawesome/6.7.2/css/all.min.css',
		));
		$head[] = ossn_html_js(array(
				'src' => ossn_theme_url() . 'vendors/bootstrap/js/bootstrap.min.js?v5.3.8',
		));
		return implode('', $head);
}
