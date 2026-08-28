<?php
/**
 * Publish a quoted wall publication to the current user's feed.
 */

$loggedin = ossn_loggedin_user();
$quote = trim((string) input('quote'));
$source = ossn_wall_repost_source(input('post'), $loggedin);

if(!$loggedin || !$source || $quote === '') {
		if(ossn_is_xhr()) {
				header('Content-Type: application/json');
				echo json_encode(array('error' => ossn_print('repost:quote:error')));
				exit;
		}
		ossn_trigger_message(ossn_print('repost:quote:error'), 'error');
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

if($repost->Post($quote, '', '', $access)) {
		if(ossn_is_xhr()) {
				header('Content-Type: application/json');
				echo json_encode(array(
						'done'    => 1,
						'message' => ossn_print('repost:quote:success'),
				));
				exit;
		}
		ossn_trigger_message(ossn_print('repost:quote:success'), 'success');
		redirect(REF);
}

if(ossn_is_xhr()) {
		header('Content-Type: application/json');
		echo json_encode(array('error' => ossn_print('repost:quote:error')));
		exit;
}
ossn_trigger_message(ossn_print('repost:quote:error'), 'error');
redirect(REF);
