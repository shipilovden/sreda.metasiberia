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
		ossn_extend_view('js/opensource.socialnetwork', 'js/goblue');
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
		$icon = ossn_add_cache_to_url(ossn_theme_url() . 'images/favicon.svg');
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
				'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>',
				'globe' => '<circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
				'map-pin' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 1 1 16 0"></path><circle cx="12" cy="10" r="3"></circle>',
				'image' => '<rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>',
				'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle>',
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

		$head[] = ossn_html_css(array(
				'href' => ossn_theme_url() . 'vendors/fontawesome/6.7.2/css/all.min.css',
		));
		$head[] = ossn_html_js(array(
				'src' => ossn_theme_url() . 'vendors/bootstrap/js/bootstrap.min.js?v5.3.8',
		));
		return implode('', $head);
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
