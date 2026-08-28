<?php
/**
 * Repost a wall publication to the current user's feed.
 */

$loggedin = ossn_loggedin_user();
$source = ossn_wall_repost_source(input('post'), $loggedin);

if(!$loggedin || !$source) {
		if(ossn_is_xhr()) {
				header('Content-Type: application/json');
				echo json_encode(array('error' => ossn_print('repost:error')));
				exit;
		}
		ossn_trigger_message(ossn_print('repost:error'), 'error');
		redirect(REF);
}

$access = (int) $source->access;
if(!in_array($access, array(OSSN_PRIVATE, OSSN_PUBLIC, OSSN_FRIENDS), true)) {
		$access = OSSN_PUBLIC;
}

$repost = new OssnWall();
$repost->owner_guid = $loggedin->guid;
$repost->poster_guid = $loggedin->guid;
$repost->data->repost_guid = (int) $source->guid;

// null creates a regular wall object without visible text; the original
// publication is rendered from repost_guid by ossn_wallpost_to_item().
if($repost->Post(null, '', '', $access)) {
		if(ossn_is_xhr()) {
				header('Content-Type: application/json');
				echo json_encode(array(
						'done'    => 1,
						'message' => ossn_print('repost:success'),
				));
				exit;
		}
		ossn_trigger_message(ossn_print('repost:success'), 'success');
		redirect(REF);
}

if(ossn_is_xhr()) {
		header('Content-Type: application/json');
		echo json_encode(array('error' => ossn_print('repost:error')));
		exit;
}
ossn_trigger_message(ossn_print('repost:error'), 'error');
redirect(REF);
