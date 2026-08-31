<?php
/**
 * SREDA Giphy Wall
 *
 * Adds a GIF picker to the OSSN wall composer without changing OSSN core,
 * OssnWall, or the installed OssnGiphy component.
 */

define('SredaGiphyWall', ossn_route()->com . 'SredaGiphyWall/');

function sreda_giphy_wall_is_ready() {
		return com_is_active('OssnGiphy')
				&& function_exists('ossn_giphy_api_key')
				&& (bool) ossn_giphy_api_key();
}

function sreda_link_preview_icon() {
		return '<svg class="ossn-lucide-icon" xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
}

function sreda_link_preview_clean_text($value, $limit = 300) {
		$value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$value = preg_replace('/\s+/u', ' ', trim($value));
		if(function_exists('mb_substr')) {
				return mb_substr($value, 0, $limit, 'UTF-8');
		}
		return substr($value, 0, $limit);
}

function sreda_link_preview_is_public_ip($ip) {
		return filter_var(
				$ip,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		) !== false;
}

function sreda_link_preview_resolve_public_ip($host) {
		if(filter_var($host, FILTER_VALIDATE_IP)) {
				return sreda_link_preview_is_public_ip($host) ? $host : false;
		}

		if(!function_exists('dns_get_record')) {
				return false;
		}
		$records = @dns_get_record($host, DNS_A | DNS_AAAA);
		if(!$records) {
				return false;
		}
		/* Prefer IPv4 so CURLOPT_RESOLVE works consistently on older cURL builds. */
		usort($records, function($left, $right) {
				$left_is_ipv4 = isset($left['ip']) ? 0 : 1;
				$right_is_ipv4 = isset($right['ip']) ? 0 : 1;
				return $left_is_ipv4 <=> $right_is_ipv4;
		});
		foreach($records as $record) {
				$ip = isset($record['ip']) ? $record['ip'] : (isset($record['ipv6']) ? $record['ipv6'] : '');
				if($ip && sreda_link_preview_is_public_ip($ip)) {
						return $ip;
				}
		}
		return false;
}

/**
 * Validate a remote URL before the server connects to it.
 *
 * DNS is resolved here and the selected public address is later pinned with
 * CURLOPT_RESOLVE, which prevents a redirect or DNS rebinding from reaching
 * localhost/private network addresses.
 */
function sreda_link_preview_validate_url($url) {
		$url = trim((string) $url);
		if($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20]/', $url)) {
				return false;
		}
		$parts = parse_url($url);
		if(!$parts || empty($parts['scheme']) || empty($parts['host'])) {
				return false;
		}
		$scheme = strtolower($parts['scheme']);
		if(!in_array($scheme, array('http', 'https'), true)) {
				return false;
		}
		if(isset($parts['user']) || isset($parts['pass'])) {
				return false;
		}
		if(isset($parts['port']) && !in_array((int) $parts['port'], array(80, 443), true)) {
				return false;
		}

		$host = strtolower(rtrim($parts['host'], '.'));
		if($host === 'localhost'
				|| preg_match('/(?:^|\.)localhost$/i', $host)
				|| preg_match('/(?:^|\.)(local|internal|intranet|lan)$/i', $host)
				|| $host === '0.0.0.0') {
				return false;
		}
		$ip = sreda_link_preview_resolve_public_ip($host);
		if(!$ip) {
				return false;
		}

		$path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
		$normalized = $scheme . '://' . $host;
		if(isset($parts['port'])) {
				$normalized .= ':' . (int) $parts['port'];
		}
		$normalized .= $path;
		if(isset($parts['query']) && $parts['query'] !== '') {
				$normalized .= '?' . $parts['query'];
		}
		return array(
				'url'   => $normalized,
				'host'  => $host,
				'port'  => isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80),
				'ip'    => $ip,
				'scheme' => $scheme,
		);
}

function sreda_link_preview_resolve_url($base, $relative) {
		$relative = trim((string) $relative);
		if($relative === '') {
				return '';
		}
		if(preg_match('#^https?://#i', $relative)) {
				return $relative;
		}
		/* Do not reinterpret javascript:, data:, mailto: and other schemes as paths. */
		if(preg_match('/^[a-z][a-z0-9+.-]*:/i', $relative)) {
				return '';
		}
		$base_parts = parse_url($base);
		if(!$base_parts || empty($base_parts['scheme']) || empty($base_parts['host'])) {
				return '';
		}
		if(strpos($relative, '//') === 0) {
				return $base_parts['scheme'] . ':' . $relative;
		}
		$origin = $base_parts['scheme'] . '://' . $base_parts['host'];
		if(isset($base_parts['port'])) {
				$origin .= ':' . (int) $base_parts['port'];
		}
		if(strpos($relative, '/') === 0) {
				return $origin . $relative;
		}
		if(strpos($relative, '?') === 0 || strpos($relative, '#') === 0) {
				$current = isset($base_parts['path']) ? $base_parts['path'] : '/';
				return $origin . $current . $relative;
		}
		$base_path = isset($base_parts['path']) ? $base_parts['path'] : '/';
		$fragment_position = strpos($relative, '#');
		if($fragment_position !== false) {
				$relative = substr($relative, 0, $fragment_position);
		}
		$directory = rtrim(str_replace('\\', '/', dirname($base_path)), '/');
		$path = $origin . ($directory ? $directory . '/' : '/') . $relative;
		return $path;
}

function sreda_link_preview_fetch_url($validated) {
		$body = '';
		$headers = array();
		$ch = curl_init();
		if(!$ch) {
				return false;
		}
		curl_setopt_array($ch, array(
				CURLOPT_URL            => $validated['url'],
				CURLOPT_RETURNTRANSFER => false,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_HEADER         => false,
				CURLOPT_CONNECTTIMEOUT => 4,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_USERAGENT      => 'SREDA Link Preview/1.0',
				CURLOPT_HTTPHEADER     => array('Accept: text/html,application/xhtml+xml;q=0.9,*/*;q=0.1'),
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_CAINFO         => ossn_route()->www . 'vendors/cacert.pem',
				CURLOPT_HEADERFUNCTION  => function($curl, $line) use (&$headers) {
						$length = strlen($line);
						$line = trim($line);
						if($line === '' || strpos($line, ':') === false) {
							return $length;
						}
						list($name, $value) = explode(':', $line, 2);
						$headers[strtolower(trim($name))] = trim($value);
						return $length;
				},
				CURLOPT_WRITEFUNCTION     => function($curl, $chunk) use (&$body) {
						if(strlen($body) + strlen($chunk) > 524288) {
							return 0;
						}
						$body .= $chunk;
						return strlen($chunk);
				},
		));
		if(filter_var($validated['host'], FILTER_VALIDATE_IP) === false) {
				curl_setopt($ch, CURLOPT_RESOLVE, array(
						$validated['host'] . ':' . $validated['port'] . ':' . $validated['ip'],
				));
		}
		$ok = curl_exec($ch);
		$status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		$content_type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);
		if($ok === false || $status < 200 || $status >= 400) {
				return false;
		}
		if($content_type && stripos($content_type, 'text/html') === false && stripos($content_type, 'application/xhtml+xml') === false) {
				return false;
		}
		return array(
				'body' => $body,
				'headers' => $headers,
				'status' => $status,
		);
}

function sreda_link_preview_read_html($url) {
		$next_url = $url;
		$response = false;
		for($redirect = 0; $redirect <= 3; $redirect++) {
				$validated = sreda_link_preview_validate_url($next_url);
				if(!$validated) {
						return false;
				}
				$response = sreda_link_preview_fetch_url($validated);
				if(!$response) {
						return false;
				}
				if($response['status'] >= 300 && $response['status'] < 400 && !empty($response['headers']['location'])) {
						$next_url = sreda_link_preview_resolve_url($validated['url'], $response['headers']['location']);
						if(!$next_url) {
							return false;
						}
						continue;
				}
				return array('url' => $validated['url'], 'body' => $response['body']);
		}
		return false;
}

function sreda_link_preview_extract($url) {
		$document = new DOMDocument();
		$previous = libxml_use_internal_errors(true);
		$loaded = $document->loadHTML($url['body'], LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);
		if(!$loaded) {
				return false;
		}
		$meta = array();
		foreach($document->getElementsByTagName('meta') as $element) {
				$key = strtolower(trim($element->getAttribute('property')));
				if($key === '') {
						$key = strtolower(trim($element->getAttribute('name')));
				}
				$content = trim($element->getAttribute('content'));
				if($key !== '' && $content !== '' && !isset($meta[$key])) {
						$meta[$key] = $content;
				}
		}
		$title = '';
		$titles = $document->getElementsByTagName('title');
		if($titles->length) {
				$title = $titles->item(0)->textContent;
		}
		$domain = parse_url($url['url'], PHP_URL_HOST);
		$domain = preg_replace('/^www\./i', '', strtolower((string) $domain));
		$site_name = sreda_link_preview_clean_text(isset($meta['og:site_name']) ? $meta['og:site_name'] : '', 120);
		$title = sreda_link_preview_clean_text(isset($meta['og:title']) ? $meta['og:title'] : $title, 180);
		$description = sreda_link_preview_clean_text(
				isset($meta['og:description']) ? $meta['og:description'] : (isset($meta['description']) ? $meta['description'] : ''),
				320
		);
		$site_name = $site_name ?: $domain;
		$title = $title ?: $site_name;

		$image = '';
		$image_candidate = isset($meta['og:image']) ? sreda_link_preview_resolve_url($url['url'], $meta['og:image']) : '';
		$image_data = $image_candidate ? sreda_link_preview_validate_url($image_candidate) : false;
		if($image_data) {
				$image = $image_data['url'];
		}
		$favicon = '';
		foreach($document->getElementsByTagName('link') as $element) {
				$rel = strtolower($element->getAttribute('rel'));
				$href = trim($element->getAttribute('href'));
				if($href && strpos($rel, 'icon') !== false) {
						$candidate = sreda_link_preview_resolve_url($url['url'], $href);
						$validated = $candidate ? sreda_link_preview_validate_url($candidate) : false;
						if($validated) {
								$favicon = $validated['url'];
								break;
						}
				}
		}
		if(!$favicon) {
				$parts = parse_url($url['url']);
				$origin = $parts['scheme'] . '://' . $parts['host'];
				$fallback = sreda_link_preview_validate_url($origin . '/favicon.ico');
				if($fallback) {
						$favicon = $fallback['url'];
				}
		}
		return array(
				'success'     => true,
				'url'         => $url['url'],
				'title'       => $title,
				'description' => $description,
				'site_name'   => $site_name,
				'domain'      => $domain,
				'image'       => $image ?: $favicon,
				'favicon'     => $favicon,
		);
}

function sreda_link_preview_fetch($url) {
		$validated = sreda_link_preview_validate_url($url);
		if(!$validated) {
				return false;
		}
		$html = sreda_link_preview_read_html($validated['url']);
		return $html ? sreda_link_preview_extract($html) : false;
}

function sreda_link_preview_from_post_input() {
		$url = trim((string) input('sreda_link_preview_url'));
		$validated = sreda_link_preview_validate_url($url);
		if(!$validated) {
				return false;
		}
		$domain = preg_replace('/^www\./i', '', strtolower($validated['host']));
		$image = trim((string) input('sreda_link_preview_image'));
		if($image) {
				$image_data = sreda_link_preview_validate_url($image);
				$image = $image_data ? $image_data['url'] : '';
		}
		$favicon = trim((string) input('sreda_link_preview_favicon'));
		if($favicon) {
				$favicon_data = sreda_link_preview_validate_url($favicon);
				$favicon = $favicon_data ? $favicon_data['url'] : '';
		}
		return array(
				'url'         => $validated['url'],
				'title'       => sreda_link_preview_clean_text(input('sreda_link_preview_title'), 180) ?: $domain,
				'description' => sreda_link_preview_clean_text(input('sreda_link_preview_description'), 320),
				'site_name'   => sreda_link_preview_clean_text(input('sreda_link_preview_site_name'), 120) ?: $domain,
				'domain'      => $domain,
				'image'       => $image ?: $favicon,
				'favicon'     => $favicon,
		);
}

function sreda_link_preview_decode($value) {
		if(is_array($value)) {
				return $value;
		}
		if(!is_string($value) || $value === '') {
				return false;
		}
		$data = json_decode($value, true);
		return is_array($data) ? $data : false;
}

function sreda_link_preview_render($preview) {
		$preview = sreda_link_preview_decode($preview);
		if(!$preview || empty($preview['url']) || empty($preview['title'])) {
				return '';
		}
		$url_parts = parse_url((string) $preview['url']);
		if(!$url_parts || empty($url_parts['scheme']) || !in_array(strtolower($url_parts['scheme']), array('http', 'https'), true)) {
				return '';
		}
		$escape = function($value) {
				return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
		};
		$image = '';
		if(!empty($preview['image'])) {
				$image_parts = parse_url((string) $preview['image']);
				if($image_parts && !empty($image_parts['scheme']) && in_array(strtolower($image_parts['scheme']), array('http', 'https'), true)) {
					$image = (string) $preview['image'];
				}
		}
		$html = '<a class="sreda-link-preview-card" href="' . $escape($preview['url']) . '" target="_blank" rel="noopener noreferrer">';
		if($image) {
				$html .= '<span class="sreda-link-preview-image"><img src="' . $escape($image) . '" alt="" loading="lazy" /></span>';
		}
		$html .= '<span class="sreda-link-preview-content">'
				. '<strong class="sreda-link-preview-title">' . $escape($preview['title']) . '</strong>';
		if(!empty($preview['description'])) {
				$html .= '<span class="sreda-link-preview-description">' . $escape($preview['description']) . '</span>';
		}
		$html .= '<span class="sreda-link-preview-domain">' . $escape($preview['domain']) . '</span>'
				. '</span></a>';
		return $html;
}

function sreda_giphy_wall_init() {
		ossn_add_hook('wall', 'templates:item', 'sreda_giphy_wall_template_item', 90);
		ossn_register_callback('wall', 'post:created', 'sreda_giphy_wall_post_created', 130);
		ossn_extend_view('css/ossn.default', 'sredagiphywall/css');
		ossn_new_js('sreda.giphy.wall', 'sredagiphywall/js');
		if(!ossn_isLoggedin()) {
				return;
		}

		ossn_load_js('sreda.giphy.wall');
		ossn_register_action('sreda/link/preview', SredaGiphyWall . 'actions/link/preview.php');

		$link_label = ossn_print('sreda:link:button');
		$link_button = array(
				'name'     => 'sreda_link_preview_selector',
				'class'    => 'sreda-link-preview-control',
				'text'     => '<span class="sreda-link-preview-button" title="' . htmlspecialchars(ossn_print('sreda:link:tooltip'), ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($link_label, ENT_QUOTES, 'UTF-8') . '">' . sreda_link_preview_icon() . '</span>',
				'href'     => 'javascript:void(0);',
				'priority' => 135,
		);
		ossn_register_menu_item('wall/container/controls/home', $link_button);
		ossn_register_menu_item('wall/container/controls/user', $link_button);
		ossn_register_menu_item('wall/container/controls/group', $link_button);

		if(!sreda_giphy_wall_is_ready()) {
				return;
		}

		$gif_button = array(
				'name'     => 'sreda_giphy_selector',
				'class'    => 'sreda-giphy-wall-control',
				'text'     => '<span class="sreda-giphy-wall-button" title="' . htmlspecialchars(ossn_print('sreda:giphy:button'), ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars(ossn_print('sreda:giphy:button'), ENT_QUOTES, 'UTF-8') . '">GIF</span>',
				'href'     => 'javascript:void(0);',
				'priority' => 130,
		);

		ossn_register_menu_item('wall/container/controls/home', $gif_button);
		ossn_register_menu_item('wall/container/controls/user', $gif_button);
		ossn_register_menu_item('wall/container/controls/group', $gif_button);
}

function sreda_giphy_wall_validate_attachment($id, $url) {
		if (!preg_match('/^[^\/?#\s]{1,128}$/', $id)) {
			return false;
		}
		$parts = parse_url($url);
		if (!$parts || strtolower(isset($parts['scheme']) ? $parts['scheme'] : '') !== 'https') {
			return false;
		}
		if (!preg_match('/^media[0-9]+\.giphy\.com$/i', isset($parts['host']) ? $parts['host'] : '')) {
			return false;
		}
		$segments = explode('/', trim(isset($parts['path']) ? $parts['path'] : '', '/'));
		if (count($segments) < 3 || strtolower($segments[0]) !== 'media') {
			return false;
		}
		return hash_equals(strtolower($id), strtolower($segments[1]));
}

function sreda_giphy_wall_post_created($event, $type, $params) {
		if (!ossn_isLoggedin() || empty($params['object_guid'])) {
			return;
		}

		$object = ossn_get_object((int) $params['object_guid']);
		if (!$object || !($object instanceof OssnObject) || $object->subtype !== 'wall') {
			return;
		}

		$changed = false;
		$id  = trim((string) input('sreda_giphy_id'));
		$url = trim((string) input('sreda_giphy_url'));
		if (sreda_giphy_wall_is_ready()
				&& $id
				&& $url
				&& sreda_giphy_wall_validate_attachment($id, $url)
				&& empty($object->{'file:wallphoto'})) {
				if (!isset($object->data) || !is_object($object->data)) {
					$object->data = new stdClass();
				}
				$object->data->sreda_giphy_id  = $id;
				$object->data->sreda_giphy_url = $url;
				$changed = true;
		}

		$link_preview = sreda_link_preview_from_post_input();
		if ($link_preview) {
				if (!isset($object->data) || !is_object($object->data)) {
					$object->data = new stdClass();
				}
				$object->data->sreda_link_preview = json_encode($link_preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				$changed = true;
		}

		if ($changed) {
				$object->save();
		}
}

function sreda_giphy_wall_template_item($hook, $type, $return, $params) {
		if (!is_array($return) || !isset($return['post']) || !($return['post'] instanceof OssnObject)) {
			return $return;
		}
		$source = $return['post'];
		if (!empty($return['reposted_post']) && $return['reposted_post'] instanceof OssnObject) {
			$source = $return['reposted_post'];
		}
		$link_preview = isset($source->sreda_link_preview) ? sreda_link_preview_decode($source->sreda_link_preview) : false;
		if ($link_preview) {
				$return['text'] = (string) (isset($return['text']) ? $return['text'] : '') . sreda_link_preview_render($link_preview);
		}

		if (!empty($return['image'])) {
			return $return;
		}
		$id  = isset($source->sreda_giphy_id) ? (string) $source->sreda_giphy_id : '';
		$url = isset($source->sreda_giphy_url) ? (string) $source->sreda_giphy_url : '';
		if ($id && $url && sreda_giphy_wall_validate_attachment($id, $url)) {
			$return['image'] = $url;
		}
		return $return;
}

ossn_register_callback('ossn', 'init', 'sreda_giphy_wall_init');
