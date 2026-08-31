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
		var emailBody = encodeURIComponent(shareMessage);
		var items = [
			{key: 'telegram', label: print('share:telegram', 'Telegram'), icon: 'fa-brands fa-telegram', href: 'https://t.me/share/url?url=' + encodedUrl + '&text=' + encodedText},
			{key: 'whatsapp', label: print('share:whatsapp', 'WhatsApp'), icon: 'fa-brands fa-whatsapp', href: 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareMessage)},
			{key: 'vk', label: print('share:vk', 'ВКонтакте'), icon: 'fa-brands fa-vk', href: 'https://vk.com/share.php?url=' + encodedUrl},
			{key: 'ok', label: print('share:ok', 'Одноклассники'), icon: 'fa-brands fa-odnoklassniki', href: 'https://connect.ok.ru/offer?url=' + encodedUrl},
			{key: 'facebook', label: print('share:facebook', 'Facebook'), icon: 'fa-brands fa-facebook', href: 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl},
			{key: 'x', label: print('share:x', 'X'), icon: 'fa-brands fa-x-twitter', href: 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedText},
			{key: 'linkedin', label: print('share:linkedin', 'LinkedIn'), icon: 'fa-brands fa-linkedin', href: 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl},
			{key: 'reddit', label: print('share:reddit', 'Reddit'), icon: 'fa-brands fa-reddit', href: 'https://www.reddit.com/submit?url=' + encodedUrl + '&title=' + encodedTitle},
			{key: 'pinterest', label: print('share:pinterest', 'Pinterest'), icon: 'fa-brands fa-pinterest', href: 'https://pinterest.com/pin/create/button/?url=' + encodedUrl + '&description=' + encodedText},
			{key: 'email', label: print('share:email', 'Email'), icon: 'fa-solid fa-envelope', href: 'mailto:?subject=' + encodedTitle + '&body=' + emailBody, email: true},
			{key: 'copy', label: print('share:copy', 'Скопировать ссылку'), icon: 'fa-solid fa-copy', href: '#', copy: true}
		];

		var html = '<div class="ossn-wall-share-menu" role="menu" data-share-image="' + escapeHtml(image) + '" hidden>';
		$.each(items, function (index, item) {
			html += '<a href="' + escapeHtml(item.href) + '" class="ossn-wall-share-action ossn-wall-share-' + item.key + '" role="menuitem"' + (item.email || item.copy ? '' : ' target="_blank" rel="noopener noreferrer"') + (item.copy ? ' data-share-mode="copy"' : '') + '>'
				+ '<i class="ossn-wall-share-menu-icon ' + item.icon + '" aria-hidden="true"></i><span>' + escapeHtml(item.label) + '</span></a>';
		});
		return html + '</div>';
	}

	function closeMenus() {
		$('.ossn-wall-share-dropdown.is-open').removeClass('is-open')
			.find('.ossn-wall-share-toggle').attr('aria-expanded', 'false')
			.end().find('.ossn-wall-share-menu').attr('hidden', 'hidden');
		$('.ossn-wall-repost-dropdown.is-open').removeClass('is-open')
			.find('.ossn-wall-repost-toggle').attr('aria-expanded', 'false')
			.end().find('.ossn-wall-repost-menu').attr('hidden', 'hidden');
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
		}
	});

	$(document).on('click', '.ossn-wall-share-action', function (event) {
		var $action = $(this);
		if ($action.data('share-mode') !== 'copy') {
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
})(jQuery);
