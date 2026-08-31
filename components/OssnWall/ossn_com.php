<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network
 * @author    Open Source Social Network Core Team <info@openteknik.com>
 * @copyright (C) OpenTeknik LLC
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
define('__OSSN_WALL__', ossn_route()->com . 'OssnWall/');
require_once __OSSN_WALL__ . 'classes/OssnWall.php';
/**
 * Initialize Ossn Wall Component
 *
 * @return void
 * @access private
 */
function ossn_wall() {
		ossn_register_com_panel('OssnWall', 'settings');

		//actions
		if(ossn_isLoggedin()) {
				ossn_register_action('wall/post/a', __OSSN_WALL__ . 'actions/wall/post/home.php');
				ossn_register_action('wall/post/u', __OSSN_WALL__ . 'actions/wall/post/user.php');
				ossn_register_action('wall/post/g', __OSSN_WALL__ . 'actions/wall/post/group.php');
				ossn_register_action('wall/post/delete', __OSSN_WALL__ . 'actions/wall/post/delete.php');
				ossn_register_action('wall/post/edit', __OSSN_WALL__ . 'actions/wall/post/edit.php');
				ossn_register_action('wall/post/embed', __OSSN_WALL__ . 'actions/wall/post/embed.php');
				ossn_register_action('wall/repost', __OSSN_WALL__ . 'actions/wall/repost.php');
				ossn_register_action('wall/quote', __OSSN_WALL__ . 'actions/wall/quote.php');

				ossn_extend_view('forms/OssnWall/home/container', 'ossn_wall_container_assets');
				ossn_extend_view('forms/OssnWall/user/container', 'ossn_wall_container_assets');
				ossn_extend_view('forms/OssnWall/group/container', 'ossn_wall_container_assets');
		}
		if(ossn_isAdminLoggedin()) {
				ossn_register_action('wall/admin/settings', __OSSN_WALL__ . 'actions/wall/admin/settings.php');
		}
		//css and js
		ossn_extend_view('css/ossn.default', 'css/wall');
		ossn_extend_view('js/ossn.site', 'js/ossn_wall');
		ossn_extend_view('ossn/site/head', 'ossn_wall_post_meta');
		ossn_new_js('ossn.repost', 'js/ossn_repost');
		ossn_load_js('ossn.repost');
		ossn_new_js('ossn.share', 'js/ossn_share');
		ossn_load_js('ossn.share');

		ossn_new_external_js('jquery.tokeninput', 'vendors/jquery/jquery.tokeninput.js');

		//pages
		ossn_register_page('post', 'ossn_post_page');
		ossn_register_page('friendpicker', 'ossn_friend_picker');

		//hooks
		ossn_add_hook('notification:view', 'like:post', 'ossn_likes_post_notifiation');
		ossn_add_hook('notification:view', 'comments:post', 'ossn_likes_post_notifiation');
		ossn_add_hook('notification:view', 'wall:friends:tag', 'ossn_likes_post_notifiation');
		ossn_add_hook('notification:view', 'comments:post:group:wall', 'ossn_likes_post_notifiation');
		ossn_add_hook('notification:view', 'like:post:group:wall', 'ossn_likes_post_notifiation');

		//hooks for notification redirect URI
		ossn_add_hook('notification:redirect:uri', 'like:post', 'ossn_likes_redirect_uri');
		ossn_add_hook('notification:redirect:uri', 'comments:post', 'ossn_likes_redirect_uri');
		ossn_add_hook('notification:redirect:uri', 'wall:friends:tag', 'ossn_likes_redirect_uri');
		ossn_add_hook('notification:redirect:uri', 'comments:post:group:wall', 'ossn_likes_redirect_uri');
		ossn_add_hook('notification:redirect:uri', 'like:post:group:wall', 'ossn_likes_redirect_uri');

		ossn_add_hook('wall', 'post:menu', 'ossn_wall_post_menu');

		//templates
		ossn_add_hook('wall:template', 'user', 'ossn_wall_templates');
		ossn_add_hook('wall:template', 'group', 'ossn_wall_templates');

		//callbacks
		ossn_register_callback('group', 'delete', 'ossn_group_wall_delete');
		ossn_register_callback('user', 'delete', 'ossn_user_posts_delete');

		$menupost = array(
				'name' => 'post',
				'text' => ossn_goblue_lucide_icon('megaphone') . '<span>' . ossn_print('post') . '</span>',
				'href' => ossn_site_url(),
		);
		$container_controls = array(
				array(
						'name'  => 'tag_friend',
						'class' => 'ossn-wall-friend',
						'text'  => ossn_goblue_lucide_icon('users'),
				),
				array(
						'name'  => 'location',
						'class' => 'ossn-wall-location',
						'text'  => ossn_goblue_lucide_icon('map-pin'),
				),
				array(
						'name'  => 'photo',
						'class' => 'ossn-wall-photo',
						'text'  => ossn_goblue_lucide_icon('image'),
				),
		);
		ossn_register_menu_item('wall/container/controls/group', array(
				'name'  => 'tag_friend',
				'class' => 'ossn-wall-friend',
				'text'  => ossn_goblue_lucide_icon('users'),
		));
		ossn_register_menu_item('wall/container/home', $menupost);
		ossn_register_menu_item('wall/container/group', $menupost);
		ossn_register_menu_item('wall/container/user', $menupost);

		foreach ($container_controls as $key => $container_control) {
				ossn_register_menu_item('wall/container/controls/home', $container_control);
				ossn_register_menu_item('wall/container/controls/user', $container_control);
				if($container_control['name'] != 'tag_friend') {
						ossn_register_menu_item('wall/container/controls/group', $container_control);
				}
		}
		ossn_add_hook('required', 'components', 'ossn_location_asure_requirements');
}
/**
 * ossn get wall by guid
 *
 * @param integer $guid Wall post guid
 * @return object|boolean
 */
function ossn_wall_by_guid($guid) {
		if(!isset($guid) || (isset($guid) && empty($guid))) {
				return false;
		}
		$wall = new OssnWall();
		return $wall->GetPost($guid);
}
/**
 * Redirect URI for wall like or comment like
 * Since its same for groups and wall so no need for seperate function
 *
 * @reutrn string
 */
function ossn_likes_redirect_uri($hook, $type, $return, $params) {
		$notification = $params['notification'];
		$uri          = "post/view/{$notification->subject_guid}";
		if(preg_match('/comments/i', $notification->type)) {
				$uri = "post/view/{$notification->subject_guid}#comments-item-{$notification->item_guid}";
		}
		return $uri;
}
/**
 * Location, Profile, Notifications Make sure it is not disabled if Wall is active
 *
 * @return array
 */
function ossn_location_asure_requirements($hook, $type, $return, $params) {
		$return[] = 'OssnLocation';
		$return[] = 'OssnNotifications';
		$return[] = 'OssnProfile';
		return $return;
}
/**
 * Friends Picker
 *
 * @return false|null|mixed data
 * @access public
 */
function ossn_friend_picker() {
		header('Content-Type: application/json');
		if(!ossn_isLoggedin()) {
				exit();
		}
		$search_for = input('q');
		$usera      = array();
		$user       = new OssnUser();

		$options = array();
		if(!empty($search_for)) {
				$search_term = "%{$search_for}%"; // Prepend/append wildcards for the LIKE search

				$wheres = array(
						array(
								// CRITICAL: The entire SQL function goes into the 'name' key.
								'name'       => "CONCAT(u.first_name, ' ', u.last_name)",

								// Use the LIKE comparator
								'comparator' => 'LIKE',

								// Pass the parameterized value for security (e.g., '%John Smith%')
								'value'      => $search_term,
						),
				);
				$options = array(
						'wheres' => $wheres,
				);
		}
		$picker_type = input('picker_type');
		$group_guid  = input('guid');

		//[E] Enhance friends picker because now getFriends searched via OssnUser instance #2202
		if(empty($picker_type) || (isset($picker_type) && $picker_type != 'group')) {
				$friends = $user->getFriends(ossn_loggedin_user()->guid, $options);
		} elseif(isset($picker_type) && $picker_type == 'group' && com_is_active('OssnGroups')) {
				$group  = ossn_get_group_by_guid($group_guid);
				$member = false;
				if($group) {
						$member = $group->isMember($group->guid, ossn_loggedin_user()->guid);
				}
				if($group->owner_guid == ossn_loggedin_user()->guid) {
						$member = true;
				}
				if(empty($search_for) || !$group || ($group && !$member)) {
						echo json_encode(array());
						return false;
				}
				$loggedin_guid = ossn_loggedin_user()->guid;

				$user    = new OssnUser();
				$friends = $user->searchUsers(array(
						'joins'    => array(
								'JOIN ossn_relationships AS r ON r.relation_to = u.guid AND r.type = "group:join:approve"',
						),
						'wheres'   => array(
								// 1. First Condition: r.relation_from = ?
								// Linked to the next item by the default 'AND'
								array(
										'name'       => 'r.relation_from',
										'comparator' => '=',
										'value'      => $group_guid,
								),

								// 2. Second Condition: CONCAT(...) LIKE ?
								// Linked to the next item by the default 'AND'
								array(
										'name'       => "CONCAT(u.first_name, ' ', u.last_name)",
										'comparator' => 'LIKE',
										'value'      => $search_term,
								),

								// 3. Third Condition: u.guid != ?
								array(
										'name'       => 'u.guid',
										'comparator' => '!=',
										'value'      => $loggedin_guid,
								),
						),
						'distinct' => true,
				));
		}
		if(!$friends) {
				echo json_encode(array());
				return false;
		}
		foreach ($friends as $users) {
				$p['first_name'] = $users->first_name;
				$p['last_name']  = $users->last_name;
				$p['imageurl']   = ossn_site_url("avatar/{$users->username}/smaller");
				$p['id']         = $users->guid;
				$p['username']   = $users->username;
				$usera[]         = $p;
		}
		echo json_encode($usera);
}
/**
 * Setting up a template for notification view for like posts
 *
 * @param string $hook Name of hook
 * @param string $type Hook type
 * @param string $return mixed data
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_likes_post_notifiation($hook, $type, $return, $params) {
		$notif = $params;
		$user  = ossn_user_by_guid($notif->poster_guid);
		if(preg_match('/like/i', $notif->type)) {
				$type = 'like';
		}
		if(preg_match('/tag/i', $notif->type)) {
				$type = 'tag';
		}
		if(preg_match('/comments/i', $notif->type)) {
				$type = 'comment';
		}

		$iconURL = $user->iconURL()->small;
		return ossn_plugin_view('notifications/template/view', array(
				'iconURL'   => $iconURL,
				'guid'      => $notif->guid,
				'type'      => $notif->type,
				'viewed'    => $notif->viewed,
				'icon_type' => $type,
				'instance'  => $notif,
				'fullname'  => $user->fullname,
		));
}
/**
 * OssnWall post page handlers
 *
 * @param array $pages List of pages
 *
 * @return false|mixed data
 * @access private
 */
function ossn_post_page($pages) {
		$page = $pages[0];
		if(empty($page)) {
				return false;
		}
		switch ($page) {
		case 'view':
				$title = ossn_print('post:view');
				$wall  = new OssnWall();
				$post  = $pages[1];
				$post  = $wall->GetPost($post);
				if(empty($post->guid) || empty($pages[1])) {
						ossn_error_page();
				}
				$loggedin = ossn_loggedin_user();
				//Posts having friends privacy are visible to public using direct URL #1484
				//re-opened on 27-06-2021 thanks to Haydar Alkaduhimi for reporting it.
				//fixing again on 18-09-2021 user can not view own post.
				//fixing admins can not view friends only post if they are not friends October 20th 2024  #2403
				if(
						(isset($post->access) && $post->access == OSSN_FRIENDS && !ossn_isLoggedin()) ||
						(!ossn_isAdminLoggedin() &&
								ossn_isLoggedin() &&
								$loggedin->guid != $post->poster_guid &&
								$post->access == OSSN_FRIENDS &&
								ossn_isLoggedin() &&
								!ossn_user_is_friend($loggedin->guid, $post->poster_guid))
				) {
						ossn_error_page();
				}
				//[B] Close group post is accessible when not loggedin #1997
				if($post->type == 'group' && com_is_active('OssnGroups')) {
						$group = ossn_get_group_by_guid($post->owner_guid);
						if($group && $group->membership == OSSN_PRIVATE) {
								//[B] admins can not view access=1 posts if not member of group #2510
								if((ossn_isLoggedin() && !ossn_isAdminLoggedin() && !$group->isMember($group->guid, $loggedin->guid)) || !ossn_isLoggedin()) {
										ossn_error_page();
								}
						}
				}
				global $ossn_wall_share_post;
				$ossn_wall_share_post = $post;
				$params['post'] = $post;

				$contents = array(
						'content' => ossn_plugin_view('wall/pages/view', $params),
				);
				$content = ossn_set_page_layout('newsfeed', $contents);
				echo ossn_view_page($title, $content);
				break;
		case 'photo':
				$wall = new OssnWall();
				$post = $wall->GetPost($pages[1]);
				if(!empty($pages[1]) && !empty($pages[2]) && $post) {
						$file = $post->getPhotoFile();
						if(!$file) {
								ossn_error_page();
						}
						$file->output();
				} else {
						ossn_error_page();
				}
				break;
		case 'privacy':
				if(ossn_is_xhr()) {
						$params = array(
								'title'    => ossn_print('privacy'),
								'contents' => ossn_plugin_view('wall/privacy'),
								'callback' => '#ossn-wall-privacy',
						);
						echo ossn_plugin_view('output/ossnbox', $params);
				}
				break;
		case 'edit':
				$post = ossn_get_object($pages[1]);
				if(!ossn_is_xhr()) {
						ossn_error_page();
				}
				if(!$post) {
						header('HTTP/1.0 404 Not Found');
				}
				$user = ossn_loggedin_user();
				if($post->poster_guid == $user->guid || $user->canModerate()) {
						$params = array(
								'title'    => ossn_print('edit'),
								'contents' => ossn_view_form(
										'post/edit',
										array(
												'action'    => ossn_site_url('action/wall/post/edit'),
												'id'        => 'ossn-post-edit-form',
												'component' => 'OssnWall',
												'params'    => array(
														'post' => $post,
												),
										),
										false
								),
								'callback' => '#ossn-post-edit-save',
						);
					echo ossn_plugin_view('output/ossnbox', $params);
				}
				break;
		case 'quote':
				if(!ossn_is_xhr() || !ossn_isLoggedin()) {
						ossn_error_page();
						break;
				}
				$wall = new OssnWall();
				$post = $wall->GetPost($pages[1]);
				if(!$post || !ossn_wall_repost_source_visible($post)) {
						header('HTTP/1.0 404 Not Found');
						break;
				}
				$params = array(
						'title'    => ossn_print('repost:quote:title'),
						'contents' => ossn_view_form(
								'post/quote',
								array(
										'action'    => ossn_site_url('action/wall/quote'),
										'id'        => 'ossn-wall-quote-form',
										'component' => 'OssnWall',
										'params'    => array(
												'post' => $post,
										),
								),
								false
						),
						'callback' => '#ossn-wall-quote-save',
						'button'   => ossn_print('repost:quote'),
				);
				echo ossn_plugin_view('output/ossnbox', $params);
				break;
		default:
				ossn_error_page();
				break;
		}
}
/**
 * View post menu
 *
 * @param string $hook Name of hook
 * @param string $type Hook type
 * @param string $return mixed data
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_wall_post_menu($hook, $type, $return, $params) {
		$user = ossn_loggedin_user();
		if($params['post']->type == 'group') {
				$group = ossn_get_group_by_guid($params['post']->owner_guid);
		}
		if(
				$params['post']->poster_guid == ossn_loggedin_user()->guid ||
				$params['post']->owner_guid == $user->guid ||
				(isset($group) && ($group->owner_guid == $user->guid || $group->isModerator($user->guid))) ||
				$user->canModerate()
		) {
				$deleteurl = ossn_site_url("action/wall/post/delete?post={$params['post']->guid}", true);

				ossn_unregister_menu('delete', 'wallpost');
				ossn_register_menu_item('wallpost', array(
						'name'      => 'delete',
						'class'     => 'ossn-wall-post-delete',
						'text'      => ossn_print('delete'),
						'href'      => $deleteurl,
						'data-guid' => $params['post']->guid,
				));
		} else {
				ossn_unregister_menu('delete', 'wallpost');
		}
		if(($params['post']->poster_guid == ossn_loggedin_user()->guid || ossn_isAdminLoggedin()) && empty($params['post']->item_guid)) {
				ossn_unregister_menu('edit', 'wallpost');
				ossn_register_menu_item('wallpost', array(
						'name'      => 'edit',
						'class'     => 'ossn-wall-post-edit',
						'text'      => ossn_print('edit'),
						'href'      => 'javascript:void(0);',
						'priority'  => 1,
						'data-guid' => $params['post']->guid,
				));
		} else {
				ossn_unregister_menu('edit', 'wallpost');
		}
		return ossn_view_menu('wallpost', 'wall/menus/post-controls');
}
/**
 * Check whether a wall publication is visible to a user and can be used as
 * the source of a repost.
 *
 * @param object $post Wall publication.
 * @param object|false $user User to check, or the current user.
 *
 * @return bool
 */
function ossn_wall_repost_source_visible($post, $user = false) {
		if(!$post || !in_array($post->type, array('user', 'group'), true) || !ossn_wall_repost_source_supported($post)) {
				return false;
		}

		if(!$user) {
				$user = ossn_loggedin_user();
		}
		$user_guid = $user && !empty($user->guid) ? (int) $user->guid : 0;
		$is_post_owner = $user_guid && ((int) $post->poster_guid === $user_guid || (int) $post->owner_guid === $user_guid);

		if($post->type === 'user') {
				if((int) $post->access === OSSN_PUBLIC) {
						return true;
				}
				if(!$user_guid) {
						return false;
				}
				if($is_post_owner || ossn_isAdminLoggedin()) {
						return true;
				}
				return (int) $post->access === OSSN_FRIENDS && ossn_user_is_friend($post->owner_guid, $user_guid);
		}

		if(!$user_guid || !function_exists('ossn_get_group_by_guid')) {
				return false;
		}
		$group = ossn_get_group_by_guid($post->owner_guid);
		return $group && ($group->owner_guid == $user_guid || $post->poster_guid == $user_guid || $group->isMember($group->guid, $user_guid) || ossn_isAdminLoggedin());
}

/**
 * Check whether a wall publication has a representation that can be reposted.
 *
 * OSSN stores profile/cover/photo-album updates as wall objects with an
 * item_guid instead of regular post text. These are still publications in the
 * feed and are supported by the repost preview below.
 *
 * @param object $post Wall publication.
 *
 * @return bool
 */
function ossn_wall_repost_source_supported($post) {
		if(!$post || !in_array($post->type, array('user', 'group'), true)) {
				return false;
		}
		if(empty($post->item_guid)) {
				return true;
		}
		return in_array($post->item_type, array('profile:photo', 'cover:photo', 'album:photos:wall'), true);
}

/**
 * Get an image used by a supported special wall publication.
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_repost_source_image($post) {
		if(!$post) {
				return '';
		}
		if(isset($post->{'file:wallphoto'})) {
				return $post->getPhotoURL();
		}

		if($post->item_type === 'profile:photo' && function_exists('ossn_profile_photo_wall_url')) {
				$image = ossn_get_file($post->item_guid);
				return $image ? ossn_profile_photo_wall_url($image) : '';
		}
		if($post->item_type === 'cover:photo' && function_exists('ossn_profile_coverphoto_wall_url')) {
				$image = ossn_get_file($post->item_guid);
				return $image ? ossn_profile_coverphoto_wall_url($image) : '';
		}
		if($post->item_type === 'album:photos:wall' && !empty($post->photos_guids) && class_exists('OssnPhotos')) {
				$photo_guids = array_filter(array_map('intval', explode(',', $post->photos_guids)));
				if(!empty($photo_guids)) {
						$photos = (new OssnPhotos())->searchPhotos(array(
								'wheres' => 'e.guid IN(' . implode(',', $photo_guids) . ')',
								'page_limit' => 1,
						));
						if(!empty($photos[0])) {
								return $photos[0]->getURL('view');
						}
				}
		}
		return '';
}

/**
 * Resolve and validate the original publication used by a repost.
 *
 * @param integer $guid Publication guid.
 * @param object|false $user User that requests the repost.
 *
 * @return object|false
 */
function ossn_wall_repost_source($guid, $user = false) {
		$wall = new OssnWall();
		$source = !empty($guid) ? $wall->GetPost((int) $guid) : false;
		if(!$source) {
				return false;
		}

		// Reposting a repost always points to its original publication.
		if(!empty($source->repost_guid)) {
				$original = $wall->GetPost((int) $source->repost_guid);
				if($original) {
						$source = $original;
				}
		}

		return ossn_wall_repost_source_visible($source, $user) ? $source : false;
}

/**
 * Render the repost toggle used in a wall action row.
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_repost_toggle($post) {
		if(!ossn_isLoggedin() || !$post || empty($post->guid) || !ossn_wall_repost_source_visible($post)) {
				return '';
		}
		$guid = (int) $post->guid;
		$label = ossn_print('repost:post');
		return '<a href="javascript:void(0);" class="post-control-repost ossn-wall-post-repost ossn-wall-repost-toggle" data-guid="' . $guid . '" aria-haspopup="true" aria-expanded="false" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">' . ossn_goblue_lucide_icon('repeat-2') . '<span>' . $label . '</span></a>';
}

/**
 * Render the two repost choices shown after clicking the repost toggle.
 *
 * @param integer $guid Publication guid.
 *
 * @return string
 */
function ossn_wall_repost_menu_markup($guid) {
		$guid = (int) $guid;
		return '<div class="ossn-wall-repost-menu" role="menu" hidden>'
				. '<a href="javascript:void(0);" class="ossn-wall-repost-action" data-mode="repost" data-guid="' . $guid . '" role="menuitem">'
				. ossn_goblue_lucide_icon('repeat-2') . '<span>' . ossn_print('repost:post') . '</span></a>'
				. '<a href="javascript:void(0);" class="ossn-wall-repost-action" data-mode="quote" data-guid="' . $guid . '" role="menuitem">'
				. ossn_goblue_lucide_icon('quote') . '<span>' . ossn_print('repost:quote') . '</span></a>'
				. '</div>';
}

/**
 * Render a repost action with its menu for wall templates that do not use
 * postextra (for example photo wall updates).
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_repost_button($post) {
		$toggle = ossn_wall_repost_toggle($post);
		if(empty($toggle)) {
				return '';
		}
		return '<div class="ossn-wall-repost-dropdown">' . $toggle . ossn_wall_repost_menu_markup($post->guid) . '</div>';
}

/**
 * Resolve the publication represented by a wall item for sharing metadata.
 * A repost keeps its own URL, but its visible text and image come from the
 * original publication.
 *
 * @param object $post Wall publication.
 *
 * @return object|false
 */
function ossn_wall_share_source_post($post) {
		if(!$post) {
				return false;
		}
		if(!empty($post->repost_guid)) {
				$source = (new OssnWall())->GetPost((int) $post->repost_guid);
				if($source && ossn_wall_repost_source_visible($source)) {
						return $source;
				}
		}
		return $post;
}

/**
 * Convert post content to safe plain text for share services and metadata.
 *
 * @param string $text Post content.
 *
 * @return string
 */
function ossn_wall_share_plain_text($text) {
		if(!is_string($text) || $text === '') {
				return '';
		}
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		$text = preg_replace('/\\s+/u', ' ', $text);
		return trim($text);
}

/**
 * Build the canonical data used by the Share menu and post metadata.
 *
 * @param object $post Wall publication.
 *
 * @return array
 */
function ossn_wall_share_data($post) {
		$source = ossn_wall_share_source_post($post);
		$guid   = (int) $post->guid;
		$site   = (string) ossn_site_settings('site_name');
		$author = $post->poster_guid ? ossn_user_by_guid($post->poster_guid) : false;
		$title  = $site;
		$texts  = array();

		if($source !== $post && !empty($post->description)) {
				$texts[] = ossn_wall_share_plain_text($post->description);
		}
		if($source && !empty($source->description)) {
				$texts[] = ossn_wall_share_plain_text($source->description);
		}
		$texts = array_values(array_unique(array_filter($texts)));

		if($author && !empty($author->fullname)) {
				$title = $author->fullname . ' — ' . $site;
		}
		return array(
				'url'   => ossn_site_url("post/view/{$guid}"),
				'title' => $title,
				'text'  => implode("\n\n", $texts),
				'image' => $source ? ossn_wall_repost_source_image($source) : '',
		);
}

/**
 * Add Open Graph and Twitter metadata to a direct post page.
 * Social networks use these tags to build a preview with the post text and
 * image while the canonical URL still points to the exact publication.
 *
 * @return string
 */
function ossn_wall_post_meta() {
		global $ossn_wall_share_post;
		if(empty($ossn_wall_share_post) || empty($ossn_wall_share_post->guid)) {
				return '';
		}

		$data        = ossn_wall_share_data($ossn_wall_share_post);
		$description = $data['text'];
		if($description === '') {
				$description = $data['title'];
		}
		if(function_exists('mb_substr')) {
				$description = mb_substr($description, 0, 300, 'UTF-8');
		} else {
				$description = substr($description, 0, 300);
		}
		$escape = function($value) {
				return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
		};

		$tags = array(
				'<meta name="description" content="' . $escape($description) . '" />',
				'<link rel="canonical" href="' . $escape($data['url']) . '" />',
				'<meta property="og:title" content="' . $escape($data['title']) . '" />',
				'<meta property="og:description" content="' . $escape($description) . '" />',
				'<meta property="og:type" content="article" />',
				'<meta property="og:url" content="' . $escape($data['url']) . '" />',
				'<meta property="og:site_name" content="' . $escape(ossn_site_settings('site_name')) . '" />',
				'<meta name="twitter:card" content="' . ($data['image'] ? 'summary_large_image' : 'summary') . '" />',
				'<meta name="twitter:title" content="' . $escape($data['title']) . '" />',
				'<meta name="twitter:description" content="' . $escape($description) . '" />',
		);
		if(!empty($data['image'])) {
				$tags[] = '<meta property="og:image" content="' . $escape($data['image']) . '" />';
				$tags[] = '<meta property="og:image:alt" content="' . $escape($data['title']) . '" />';
				$tags[] = '<meta name="twitter:image" content="' . $escape($data['image']) . '" />';
		}
		return "\n" . implode("\n", $tags) . "\n";
}

/**
 * Render the Share toggle used in a wall action row.
 *
 * The menu itself is created by the delegated client-side handler so that
 * dynamically loaded wall items receive the same share options.
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_share_toggle($post) {
		if(!ossn_isLoggedin() || !$post || empty($post->guid)) {
				return '';
		}
		$share_data = ossn_wall_share_data($post);
		$label      = ossn_print('share:post');
		return '<a href="javascript:void(0);" class="post-control-share ossn-wall-post-share ossn-wall-share-toggle ossn-wall-share-icon-only" data-share-url="' . htmlspecialchars($share_data['url'], ENT_QUOTES, 'UTF-8') . '" data-share-title="' . htmlspecialchars($share_data['title'], ENT_QUOTES, 'UTF-8') . '" data-share-text="' . htmlspecialchars($share_data['text'], ENT_QUOTES, 'UTF-8') . '" data-share-image="' . htmlspecialchars($share_data['image'], ENT_QUOTES, 'UTF-8') . '" aria-haspopup="true" aria-expanded="false" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">' . ossn_goblue_lucide_icon('share-2') . '</a>';
}

/**
 * Render a Share action with its client-side menu container.
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_share_button($post) {
		$toggle = ossn_wall_share_toggle($post);
		if(empty($toggle)) {
				return '';
		}
		return '<div class="ossn-wall-share-dropdown">' . $toggle . '</div>';
}

/**
 * Render one existing postextra menu item without relying on its registration
 * priority. This keeps the action row order stable for every wall item.
 *
 * @param array $link Existing OSSN menu item.
 *
 * @return string
 */
function ossn_wall_render_post_menu_link($link) {
		if(empty($link['name'])) {
				return '';
		}
		$name = $link['name'];
		$class = 'post-control-' . $name;
		if(isset($link['class'])) {
				$class .= ' ' . $link['class'];
		}
		$link['class'] = $class;
		$labels = array(
				'like'    => ossn_print('ossn:like'),
				'comment' => ossn_print('comment:comment'),
		);
		if(isset($labels[$name])) {
				$link['title']      = $labels[$name];
				$link['aria-label'] = $labels[$name];
		}
		unset($link['name'], $link['priority']);
		return '<li>' . ossn_plugin_view('output/url', $link) . '</li>';
}

/**
 * Render the common wall action row in the order Like, Comment, Repost, Share.
 *
 * @param object $post Wall publication.
 *
 * @return string
 */
function ossn_wall_render_post_actions($post) {
		global $Ossn;
		if(!ossn_isLoggedin() || empty($Ossn->menu['postextra'])) {
				return '';
		}

		$items = array();
		foreach($Ossn->menu['postextra'] as $menu) {
				foreach($menu as $link) {
						if(!empty($link['name']) && in_array($link['name'], array('like', 'comment'), true)) {
							$items[$link['name']] = $link;
						}
				}
		}

		$html = '';
		foreach(array('like', 'comment') as $name) {
				if(isset($items[$name])) {
						$html .= ossn_wall_render_post_menu_link($items[$name]);
				}
		}

		$repost = ossn_wall_repost_button($post);
		if($repost) {
				$html .= '<li class="ossn-wall-repost-action-item">' . $repost . '</li>';
		}

		$share = ossn_wall_share_button($post);
		if($share) {
				$html .= '<li class="ossn-wall-share-action-item">' . $share . '</li>';
		}
		return $html;
}

/**
 * Render the entity action row in the same order as a regular wall post.
 *
 * Profile, cover and album updates use entityextra instead of postextra.
 * Keep the existing entity handlers, but render their common actions in one
 * predictable order and attach the same repost/share controls.
 *
 * @return string
 */
function ossn_wall_render_entity_actions() {
		global $Ossn;
		if(!ossn_isLoggedin() || empty($Ossn->menu['entityextra'])) {
				return '';
		}

		$items = array();
		foreach($Ossn->menu['entityextra'] as $menu) {
				foreach($menu as $link) {
						if(!empty($link['name']) && in_array($link['name'], array('like', 'comment', 'repost', 'share'), true)) {
							$items[$link['name']] = $link;
						}
				}
		}

		$html = '';
		foreach(array('like', 'comment', 'repost', 'share') as $name) {
				if(!isset($items[$name])) {
						continue;
				}
				$link = $items[$name];
				$class = 'entity-menu-extra-' . $name;
				if(isset($link['class'])) {
						$class .= ' ' . $link['class'];
				}
				$link['class'] = $class;
				$labels = array(
						'like'    => ossn_print('ossn:like'),
						'comment' => ossn_print('comment:comment'),
						'repost'  => ossn_print('repost:post'),
						'share'   => ossn_print('share:post'),
				);
				if(isset($labels[$name])) {
						$link['title']      = $labels[$name];
						$link['aria-label'] = $labels[$name];
				}
				unset($link['name'], $link['priority']);

				$action = ossn_plugin_view('output/url', $link);
				if($name === 'repost' && !empty($link['data-guid'])) {
						$action = '<div class="ossn-wall-repost-dropdown">' . $action . ossn_wall_repost_menu_markup($link['data-guid']) . '</div>';
				}
				if($name === 'share') {
						$action = '<div class="ossn-wall-share-dropdown">' . $action . '</div>';
				}
				$html .= '<li>' . $action . '</li>';
		}
		return $html;
}

/**
 * Register a repost action in the entity menu used by special wall templates.
 *
 * @param object $post Wall publication.
 *
 * @return void
 */
function ossn_wall_register_repost_entity_menu($post) {
		ossn_wall_register_share_entity_menu($post);
		ossn_unregister_menu('repost', 'entityextra');
		if(!ossn_isLoggedin() || !$post || empty($post->guid) || !ossn_wall_repost_source_visible($post)) {
				return;
		}
		$guid = (int) $post->guid;
		ossn_register_menu_item('entityextra', array(
				'name'      => 'repost',
				'class'     => 'ossn-wall-post-repost ossn-wall-repost-toggle',
				'href'      => 'javascript:void(0);',
				'data-guid' => $guid,
				'priority'  => 210,
				'text'      => ossn_goblue_lucide_icon('repeat-2') . '<span>' . ossn_print('repost:post') . '</span>',
		));
}

/**
 * Register a Share action in the entity menu used by special wall templates.
 *
 * @param object $post Wall publication.
 *
 * @return void
 */
function ossn_wall_register_share_entity_menu($post) {
		ossn_unregister_menu('share', 'entityextra');
		if(!ossn_isLoggedin() || !$post || empty($post->guid)) {
				return;
		}
		$share_data = ossn_wall_share_data($post);
		ossn_register_menu_item('entityextra', array(
				'name'           => 'share',
				'class'          => 'ossn-wall-post-share ossn-wall-share-toggle ossn-wall-share-icon-only',
				'href'           => 'javascript:void(0);',
				'data-share-url' => $share_data['url'],
				'data-share-title' => $share_data['title'],
				'data-share-text' => $share_data['text'],
				'data-share-image' => $share_data['image'],
				'title'          => ossn_print('share:post'),
				'aria-label'     => ossn_print('share:post'),
				'priority'       => 220,
				'text'           => ossn_goblue_lucide_icon('share-2'),
		));
}

/**
 * Delete group wall posts
 *
 * @param string $callback Name of callback
 * @param string $type Callback type
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_group_wall_delete($callback, $type, $params) {
		$wall  = new OssnWall();
		$posts = $wall->GetPostByOwner($params['entity']->guid, 'group');
		if($posts) {
				foreach ($posts as $post) {
						$wall->deletePost($post->guid);
				}
		}
}
/**
 * Delete user wall posts
 *
 * @param string $callback Name of callback
 * @param string $type Callback type
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_user_posts_delete($callback, $type, $params) {
		$wall  = new OssnWall();
		$posts = $wall->getUserGroupPostsGuids($params['entity']->guid);
		if($posts) {
				foreach ($posts as $post) {
						//$post is here int
						$wall->deletePost($post);
				}
		}
		//Broken wall posts upon deleting user #1129
		$wall      = new OssnWall();
		$userposts = $wall->getPosterPosts($params['entity']->guid);
		if($userposts) {
				foreach ($userposts as $item) {
						$wall->deletePost($item->guid);
				}
		}
		//Deleting user didn't delete users wall posts if wall poster_guid is not same user as deleted #1505
		if(!empty($params['entity']->guid)) {
				$posts_by_owner_guid = $wall->searchObject(array(
						'type'       => 'user',
						'subtype'    => 'wall',
						'owner_guid' => $params['entity']->guid,
						'page_limit' => false,
				));
				if($posts_by_owner_guid) {
						foreach ($posts_by_owner_guid as $posti) {
								$posti->deletePost($posti->guid);
						}
				}
		}
}
/**
 * Encode unecaped unicode characters
 *
 * @return mixed data
 * @access private
 */
function ossnwall_json_unencaped_unicode($matches) {
		return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
}
/**
 * View wall post view template
 *
 * @param array $params Options
 *
 * @return mixed data
 * @access private
 */
function ossn_wall_view_template(array $params = array()) {
		if(!is_array($params)) {
				return false;
		}
		$type = $params['post']->type;
		if(isset($params['post']->item_type) && !empty($params['post']->item_type)) {
				$type = $params['post']->item_type;
		}
		if(ossn_is_hook('wall:template', $type)) {
				return ossn_call_hook('wall:template', $type, $params);
		}
		return false;
}
/**
 * Wall template view
 * Depends on wall post type
 *
 * @param string $callback Name of callback
 * @param string $type Callback type
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_wall_templates($hook, $type, $return, $params) {
		ossn_trigger_callback('wall', 'load:item', $params);
		$params = ossn_call_hook('wall', 'templates:item', $params, $params);
		return ossn_plugin_view("wall/templates/wall/{$type}/item", $params);
}
/**
 * Set homepage wall items type friends/public
 *
 * @param strig $default friends/public
 *
 * @return mixed data
 * @access private
 */
function ossn_set_homepage_wall_access($default = 'friends') {
		$data = ossn_get_entities(array(
				'type'       => 'component',
				'subtype'    => 'ossnwall_defaultwall',
				'owner_guid' => 2,
		));
		if(!$data) {
				return ossn_add_entity(array(
						'type'       => 'component',
						'subtype'    => 'ossnwall_defaultwall',
						'owner_guid' => 2,
						'value'      => $default,
				));
		} else {
				$settings = $data[0];
				return ossn_update_entity($settings->guid, $default);
		}
}
/**
 * Wall template view
 * Depends on wall post type
 *
 * @param string $callback Name of callback
 * @param string $type Callback type
 * @param array $params Arrays or Objects
 *
 * @return mixed data
 * @access private
 */
function ossn_get_homepage_wall_access() {
		$data = ossn_get_entities(array(
				'type'       => 'component',
				'subtype'    => 'ossnwall_defaultwall',
				'owner_guid' => 2,
		));
		if($data) {
				return $data[0]->value;
		} else {
				return 'public';
		}
}
/**
 * Convert wallobject to wall post array
 *
 * @param object $post A wall object
 *
 * @return array|false
 */
function ossn_wallpost_to_item($post) {
		if($post && $post instanceof OssnWall) {
				$content_post = $post;
				$reposted_post = false;
				if(!empty($post->repost_guid)) {
						$reposted_post = (new OssnWall())->GetPost((int) $post->repost_guid);
						if($reposted_post && ossn_wall_repost_source_visible($reposted_post)) {
								$content_post = $reposted_post;
						} else {
								$reposted_post = false;
						}
				}

				//post text
				$text = '';
				if(!empty($content_post->description)) {
						$text = ossn_restore_new_lines($content_post->description, true);
				}
				// A quoted repost keeps the new author's text on the repost object,
				// while the rest of the rendered content comes from the source post.
				$repost_text = '';
				if($reposted_post && !empty($post->description)) {
						$repost_text = ossn_restore_new_lines($post->description, true);
				}

				//location
				$location = '';
				if(isset($content_post->location)) {
						$location = '- ' . $content_post->location;
				}

				//image
				$image = ossn_wall_repost_source_image($content_post);

				$user = ossn_user_by_guid($post->poster_guid);

				//friends
				$friends = '';
				if(isset($content_post->tag_friend_guids)) {
						$friends = $content_post->tag_friend_guids;
				}

				return array(
						'post'     => $post,
						'friends'  => explode(',', $friends),
						'text'     => $text,
						'location' => $location,
						'user'     => $user,
						'image'    => $image,
						'repost_text' => $repost_text,
						'reposted_post' => $reposted_post,
				);
		}
		return false;
}
/**
 * Wall container assets
 *
 **/
function ossn_wall_container_assets() {
		ossn_location_load_jscss();
		ossn_load_external_js('jquery.tokeninput');
}
//initilize ossn wall
ossn_register_callback('ossn', 'init', 'ossn_wall');
