<?php
header('Content-Type: application/json; charset=utf-8');

if(!ossn_isLoggedin()) {
		echo json_encode(array(
				'success' => false,
				'error'   => ossn_print('sreda:link:error'),
		), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit();
}

$preview = sreda_link_preview_fetch(input('url'));
if(!$preview) {
		echo json_encode(array(
				'success' => false,
				'error'   => ossn_print('sreda:link:error'),
		), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit();
}

echo json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit();
