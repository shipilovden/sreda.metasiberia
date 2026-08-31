(function ($) {
	function escapeHtml(value) {
		return String(value || '').replace(/[&<>"']/g, function (character) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			}[character];
		});
	}

	function print(key, fallback) {
		var value = Ossn.Print(key);
		return value && value !== key ? value : fallback;
	}

	function menuMarkup(url, title, text, image) {
		var encodedUrl = encodeURIComponent(url);
		var encodedTitle = encodeURIComponent(title);
		var shareText = text || title;
		var shareMessage = shareText + '\n\n' + url;
		var encodedText = encodeURIComponent(shareText);
		var encodedImage = encodeURIComponent(image || '');
		var emailBody = encodeURIComponent(shareMessage);
		var items = [
			{key: 'telegram', label: print('share:telegram', 'Telegram'), icon: 'fa-brands fa-telegram', href: 'https://t.me/share/url?url=' + encodedUrl + '&text=' + encodedText},
			{key: 'whatsapp', label: print('share:whatsapp', 'WhatsApp'), icon: 'fa-brands fa-whatsapp', href: 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareMessage)},
			{key: 'vk', label: print('share:vk', 'ВКонтакте'), icon: 'fa-brands fa-vk', href: 'https://vk.com/share.php?url=' + encodedUrl + '&title=' + encodedTitle + '&description=' + encodedText + '&image=' + encodedImage + '&noparse=true'},
			{key: 'facebook', label: print('share:facebook', 'Facebook'), icon: 'fa-brands fa-facebook', href: 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl},
			{key: 'messenger', label: print('share:messenger', 'Facebook Messenger'), icon: 'fa-brands fa-facebook-messenger', href: 'https://www.facebook.com/dialog/send?link=' + encodedUrl},
			{key: 'x', label: print('share:x', 'X'), icon: 'fa-brands fa-x-twitter', href: 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedText},
			{key: 'linkedin', label: print('share:linkedin', 'LinkedIn'), icon: 'fa-brands fa-linkedin', href: 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl},
			{key: 'reddit', label: print('share:reddit', 'Reddit'), icon: 'fa-brands fa-reddit', href: 'https://www.reddit.com/submit?url=' + encodedUrl + '&title=' + encodedTitle},
			{key: 'pinterest', label: print('share:pinterest', 'Pinterest'), icon: 'fa-brands fa-pinterest', href: 'https://pinterest.com/pin/create/button/?url=' + encodedUrl + '&description=' + encodedText},
			{key: 'instagram', label: print('share:instagram', 'Instagram'), icon: 'fa-brands fa-instagram', href: 'https://www.instagram.com/', native: true},
			{key: 'instagram-stories', label: print('share:instagram:stories', 'Instagram Stories'), icon: 'fa-brands fa-instagram', href: 'https://www.instagram.com/create/story/', native: true, nativeImage: true},
			{key: 'instagram-messages', label: print('share:instagram:messages', 'Instagram Direct'), icon: 'fa-brands fa-instagram', href: 'https://www.instagram.com/direct/inbox/', native: true},
			{key: 'threads', label: print('share:threads', 'Threads'), icon: 'fa-brands fa-threads', href: 'https://www.threads.net/intent/post?text=' + encodeURIComponent(shareMessage)},
			{key: 'discord', label: print('share:discord', 'Discord'), icon: 'fa-brands fa-discord', href: 'https://discord.com/app', native: true},
			{key: 'signal', label: print('share:signal', 'Signal'), icon: 'fa-solid fa-comment', href: 'https://signal.org/', native: true},
			{key: 'bluesky', label: print('share:bluesky', 'Bluesky'), icon: 'fa-brands fa-bluesky', href: 'https://bsky.app/intent/compose?text=' + encodeURIComponent(shareMessage)},
			{key: 'mastodon', label: print('share:mastodon', 'Mastodon'), icon: 'fa-brands fa-mastodon', href: 'https://mastodon.social/share?text=' + encodeURIComponent(shareMessage)},
			{key: 'tumblr', label: print('share:tumblr', 'Tumblr'), icon: 'fa-brands fa-tumblr', href: 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=' + encodedUrl + '&title=' + encodedTitle + '&caption=' + encodedText},
			{key: 'snapchat', label: print('share:snapchat', 'Snapchat'), icon: 'fa-brands fa-snapchat', href: 'https://www.snapchat.com/', native: true},
			{key: 'tiktok', label: print('share:tiktok', 'TikTok'), icon: 'fa-brands fa-tiktok', href: 'https://www.tiktok.com/', native: true},
			{key: 'viber', label: print('share:viber', 'Viber'), icon: 'fa-brands fa-viber', href: 'viber://forward?text=' + encodeURIComponent(shareMessage), native: true},
			{key: 'line', label: print('share:line', 'LINE'), icon: 'fa-brands fa-line', href: 'https://social-plugins.line.me/lineit/share?url=' + encodedUrl + '&text=' + encodedText},
			{key: 'wechat', label: print('share:wechat', 'WeChat'), icon: 'fa-brands fa-weixin', href: 'https://www.wechat.com/', native: true},
			{key: 'qq', label: print('share:qq', 'QQ'), icon: 'fa-brands fa-qq', href: 'https://connect.qq.com/widget/shareqq/index.html?url=' + encodedUrl + '&title=' + encodedTitle + '&summary=' + encodedText},
			{key: 'kakaotalk', label: print('share:kakaotalk', 'KakaoTalk'), icon: 'fa-solid fa-comment', href: 'https://story.kakao.com/share?url=' + encodedUrl, native: true},
			{key: 'imo', label: print('share:imo', 'imo'), icon: 'fa-solid fa-comment', href: 'https://imo.im/', native: true},
			{key: 'bip', label: print('share:bip', 'BiP'), icon: 'fa-solid fa-comment', href: 'https://bip.com/', native: true},
			{key: 'zalo', label: print('share:zalo', 'Zalo'), icon: 'fa-solid fa-comment', href: 'https://zalo.me/share?u=' + encodedUrl},
			{key: 'slack', label: print('share:slack', 'Slack'), icon: 'fa-brands fa-slack', href: 'https://slack.com/'},
			{key: 'teams', label: print('share:teams', 'Microsoft Teams'), icon: 'fa-solid fa-users', href: 'https://teams.microsoft.com/share?href=' + encodedUrl + '&msgText=' + encodedText},
			{key: 'nostr', label: print('share:nostr', 'Nostr'), icon: 'fa-solid fa-bolt', href: 'https://nostr.com/', native: true},
			{key: 'sms', label: print('share:sms', 'SMS'), icon: 'fa-solid fa-comment-sms', href: 'sms:?&body=' + encodeURIComponent(shareMessage), sms: true},
			{key: 'email', label: print('share:email', 'Email'), icon: 'fa-solid fa-envelope', href: 'mailto:?subject=' + encodedTitle + '&body=' + emailBody, email: true},
			{key: 'copy', label: print('share:copy', 'Скопировать ссылку'), icon: 'fa-solid fa-copy', href: '#', copy: true},
			{key: 'qr', label: print('share:qr', 'QR-код'), icon: 'fa-solid fa-qrcode', href: 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' + encodedUrl, qr: true},
			{key: 'web-share', label: print('share:web', 'Поделиться…'), icon: 'fa-solid fa-share-nodes', href: '#', native: true, anyDevice: true}
		];

		var html = '<div class="ossn-wall-share-menu" role="menu" data-share-image="' + escapeHtml(image) + '" hidden>';
		$.each(items, function (index, item) {
			var mode = item.copy ? 'copy' : (item.native ? 'native' : '');
			var attributes = mode ? ' data-share-mode="' + mode + '"' : '';
			if (item.nativeImage) {
				attributes += ' data-share-native-image="1"';
			}
			if (item.anyDevice) {
				attributes += ' data-share-any-device="1"';
			}
			if (!item.email && !item.copy && !item.native && !item.sms && !item.anyDevice) {
				attributes += ' target="_blank" rel="noopener noreferrer"';
			}
			html += '<a href="' + escapeHtml(item.href) + '" class="ossn-wall-share-action ossn-wall-share-' + item.key + '" role="menuitem"' + attributes + '>'
				+ '<i class="ossn-wall-share-menu-icon ' + item.icon + '" aria-hidden="true"></i><span>' + escapeHtml(item.label) + '</span></a>';
		});
		return html + '</div>';
	}

	function closeMenus() {
		$('.ossn-wall-share-dropdown.is-open').each(function () {
			var $dropdown = $(this);
			$dropdown.removeClass('is-open');
			$dropdown.find('.ossn-wall-share-toggle').attr('aria-expanded', 'false');
			$dropdown.find('.ossn-wall-share-menu').attr('hidden', 'hidden').css({
				position: '',
				width: '',
				minWidth: '',
				maxWidth: '',
				left: '',
				right: '',
				top: '',
				bottom: '',
				transform: ''
			});
		});
		$('.ossn-wall-repost-dropdown.is-open').removeClass('is-open')
			.find('.ossn-wall-repost-toggle').attr('aria-expanded', 'false')
			.end().find('.ossn-wall-repost-menu').attr('hidden', 'hidden');
	}

	function positionShareMenu($dropdown) {
		var menu = $dropdown.find('.ossn-wall-share-menu')[0];
		var toggle = $dropdown.find('.ossn-wall-share-toggle')[0];
		if (!menu || !toggle) {
			return;
		}

		var padding = 8;
		var viewport = window.visualViewport || null;
		var viewportWidth = viewport ? viewport.width : window.innerWidth;
		var viewportHeight = viewport ? viewport.height : window.innerHeight;
		var menuWidth = Math.min(360, Math.max(0, viewportWidth - (padding * 2)));
		var toggleRect = toggle.getBoundingClientRect();
		var $menu = $(menu);

		/* Make the menu independent from the wall card's width and overflow rules. */
		$menu.css({
			position: 'fixed',
			width: menuWidth + 'px',
			minWidth: '0',
			maxWidth: 'none',
			left: '0px',
			right: 'auto',
			top: '0px',
			bottom: 'auto',
			transform: 'none'
		});

		var menuHeight = $menu.outerHeight();
		var top = toggleRect.top - menuHeight - 6;
		var maxTop = window.innerHeight - menuHeight - padding;
		if (top < padding) {
			top = toggleRect.bottom + 6;
		}
		if (maxTop < padding) {
			maxTop = padding;
		}
		top = Math.max(padding, Math.min(top, maxTop));

		var left = toggleRect.left + (toggleRect.width / 2) - (menuWidth / 2);
		left = Math.max(padding, Math.min(left, viewportWidth - menuWidth - padding));
		$menu.css({
			left: left + 'px',
			top: top + 'px'
		});
	}

	function ensureDropdown($toggle) {
		var $dropdown = $toggle.closest('.ossn-wall-share-dropdown');
		if (!$dropdown.length) {
			$toggle.wrap('<span class="ossn-wall-share-dropdown"></span>');
			$dropdown = $toggle.parent();
		}
		if (!$dropdown.find('.ossn-wall-share-menu').length) {
			$dropdown.append(menuMarkup(
				$toggle.attr('data-share-url'),
				$toggle.attr('data-share-title') || print('site:name', 'SREDA'),
				$toggle.attr('data-share-text'),
				$toggle.attr('data-share-image')
			));
		}
		$dropdown.find('.ossn-wall-share-menu').attr('hidden', 'hidden');
		return $dropdown;
	}

	function isMobileShareDevice() {
		return window.matchMedia && window.matchMedia('(max-width: 991px)').matches;
	}

	function loadShareImage(image) {
		if (!image || !window.fetch || !window.File) {
			return Promise.resolve(null);
		}
		return window.fetch(image, {credentials: 'same-origin'}).then(function (response) {
			if (!response.ok) {
				return null;
			}
			return response.blob();
		}).then(function (blob) {
			if (!blob) {
				return null;
			}
			var mime = blob.type || 'image/jpeg';
			var extension = (mime.split('/')[1] || 'jpg').replace(/[^a-z0-9]/gi, '') || 'jpg';
			return new File([blob], 'sreda-share.' + extension, {type: mime});
		}).catch(function () {
			return null;
		});
	}

	function prefetchShareImage($dropdown) {
		var $menu = $dropdown.find('.ossn-wall-share-menu');
		var image = $menu.attr('data-share-image');
		if (!image || $menu.data('share-image-requested')) {
			return;
		}
		$menu.data('share-image-requested', true);
		loadShareImage(image).then(function (file) {
			if (file) {
				$menu.data('share-image-file', file);
			}
		});
	}

	function nativeShare($action) {
		var $dropdown = $action.closest('.ossn-wall-share-dropdown');
		var $toggle = $dropdown.find('.ossn-wall-share-toggle');
		var $menu = $dropdown.find('.ossn-wall-share-menu');
		var url = $toggle.attr('data-share-url');
		var title = $toggle.attr('data-share-title') || print('site:name', 'SREDA');
		var text = $toggle.attr('data-share-text') || title;
		var shareData = {title: title, text: text, url: url};
		var file = $menu.data('share-image-file');

		if ($action.attr('data-share-native-image') && file && navigator.canShare) {
			try {
				if (navigator.canShare({files: [file]})) {
					shareData.files = [file];
				}
			} catch (error) {
				/* Share text and URL even when the browser rejects the image payload. */
			}
		}

		try {
			return navigator.share(shareData).then(function () {
				return 'handled';
			}, function (error) {
				return error && error.name === 'AbortError' ? 'cancelled' : 'failed';
			});
		} catch (error) {
			return Promise.resolve('failed');
		}
	}

	function copyText(value, callback) {
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(value).then(function () {
				callback(true);
			}, function () {
				callback(false);
			});
			return;
		}

		var $input = $('<textarea>').val(value).css({position: 'fixed', left: '-9999px', top: '0'}).appendTo('body');
		$input[0].focus();
		$input[0].select();
		var copied = false;
		try {
			copied = document.execCommand('copy');
		} catch (error) {
			copied = false;
		}
		$input.remove();
		callback(copied);
	}

	$(document).on('click', '.ossn-wall-share-toggle', function (event) {
		event.preventDefault();
		event.stopPropagation();

		var $toggle = $(this);
		var $dropdown = ensureDropdown($toggle);
		var open = !$dropdown.hasClass('is-open');
		closeMenus();
		if (open) {
			$dropdown.addClass('is-open');
			$toggle.attr('aria-expanded', 'true');
			$dropdown.find('.ossn-wall-share-menu').removeAttr('hidden');
			prefetchShareImage($dropdown);
			positionShareMenu($dropdown);
		}
	});

	$(document).on('click', '.ossn-wall-share-action', function (event) {
		var $action = $(this);
		var mode = $action.data('share-mode');
		if (mode === 'native') {
			var anyDevice = $action.attr('data-share-any-device') === '1';
			if (!navigator.share) {
				if (anyDevice) {
					event.preventDefault();
					event.stopPropagation();
					closeMenus();
					Ossn.trigger_message(print('share:web:unsupported', 'Системное меню «Поделиться» недоступно на этом устройстве'), 'error');
					return;
				}
				closeMenus();
				return;
			}
			if (!anyDevice && !isMobileShareDevice()) {
				closeMenus();
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			nativeShare($action).then(function (result) {
				closeMenus();
				if (result === 'failed') {
					window.location.href = $action.attr('href');
				}
			});
			return;
		}
		if (mode !== 'copy') {
			closeMenus();
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		var url = $action.closest('.ossn-wall-share-dropdown').find('.ossn-wall-share-toggle').attr('data-share-url');
		copyText(url, function (copied) {
			closeMenus();
			Ossn.trigger_message(copied ? print('share:copied', 'Ссылка скопирована') : print('share:copy:error', 'Не удалось скопировать ссылку'), copied ? 'success' : 'error');
		});
	});

	$(document).on('click', function () {
		closeMenus();
	});

	$(window).on('resize orientationchange scroll', function () {
		$('.ossn-wall-share-dropdown.is-open').each(function () {
			positionShareMenu($(this));
		});
	});
})(jQuery);
