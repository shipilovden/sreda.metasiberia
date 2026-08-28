(function ($) {
	var repeatIcon = '<svg class="ossn-lucide-icon" xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m2 9 3-3 3 3"></path><path d="M13 18H7a2 2 0 0 1-2-2V6"></path><path d="m22 15-3 3-3-3"></path><path d="M11 6h6a2 2 0 0 1 2 2v10"></path></svg>';
	var quoteIcon = '<svg class="ossn-lucide-icon" xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 21c3 0 7-1 7-8V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h2c0 2-1 3-3 4v4z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h2c0 2-1 3-3 4v4z"></path></svg>';

	function menuMarkup(guid) {
		return '<div class="ossn-wall-repost-menu" role="menu" hidden>'
			+ '<a href="javascript:void(0);" class="ossn-wall-repost-action" data-mode="repost" data-guid="' + guid + '" role="menuitem">'
			+ repeatIcon + '<span>' + Ossn.Print('repost:post') + '</span></a>'
			+ '<a href="javascript:void(0);" class="ossn-wall-repost-action" data-mode="quote" data-guid="' + guid + '" role="menuitem">'
			+ quoteIcon + '<span>' + Ossn.Print('repost:quote') + '</span></a>'
			+ '</div>';
	}

	function closeMenus() {
		$('.ossn-wall-repost-dropdown.is-open').removeClass('is-open')
			.find('.ossn-wall-repost-toggle').attr('aria-expanded', 'false')
			.end().find('.ossn-wall-repost-menu').attr('hidden', 'hidden');
	}

	function ensureDropdown($toggle) {
		var $dropdown = $toggle.closest('.ossn-wall-repost-dropdown');
		if (!$dropdown.length) {
			$toggle.wrap('<span class="ossn-wall-repost-dropdown"></span>');
			$dropdown = $toggle.parent();
			$dropdown.append(menuMarkup($toggle.data('guid')));
		}
		$dropdown.find('.ossn-wall-repost-menu').attr('hidden', 'hidden');
		return $dropdown;
	}

	$(document).on('click', '.ossn-wall-repost-toggle', function (event) {
		event.preventDefault();
		event.stopPropagation();

		var $toggle = $(this);
		var $dropdown = ensureDropdown($toggle);
		var open = !$dropdown.hasClass('is-open');
		closeMenus();
		if (open) {
			$dropdown.addClass('is-open');
			$toggle.attr('aria-expanded', 'true');
			$dropdown.find('.ossn-wall-repost-menu').removeAttr('hidden');
		}
	});

	$(document).on('click', '.ossn-wall-repost-action', function (event) {
		event.preventDefault();
		event.stopPropagation();

		var $action = $(this);
		var post = $action.data('guid');
		var mode = $action.data('mode');
		closeMenus();
		if (!post) {
			return;
		}
		if (mode === 'quote') {
			Ossn.PostQuote(post);
		} else {
			Ossn.PostRepost(post);
		}
	});

	$(document).on('click', function () {
		closeMenus();
	});
})(jQuery);

Ossn.PostRepost = function (post) {
	var $item = $('#activity-item-' + post);
	var $button = $item.find('.ossn-wall-repost-toggle, .post-control-repost').first();

	if (!$button.length || $button.hasClass('ossn-repost-in-xhr')) {
		return;
	}

	Ossn.PostRequest({
		url: Ossn.site_url + 'action/wall/repost',
		params: '&post=' + encodeURIComponent(post),
		beforeSend: function () {
			$button.addClass('ossn-repost-in-xhr').attr('aria-disabled', 'true');
		},
		callback: function (callback) {
			if (callback && callback.done == 1) {
				window.location.reload();
				return;
			}

			$button.removeClass('ossn-repost-in-xhr').removeAttr('aria-disabled');
			Ossn.trigger_message((callback && callback.error) || Ossn.Print('repost:error'), 'error');
		},
		error: function () {
			$button.removeClass('ossn-repost-in-xhr').removeAttr('aria-disabled');
			Ossn.trigger_message(Ossn.Print('repost:error'), 'error');
		}
	});
};

Ossn.PostQuote = function (post) {
	Ossn.MessageBox('post/quote/' + encodeURIComponent(post));
};

Ossn.WallQuoteForm = function () {
	Ossn.ajaxRequest({
		url: Ossn.site_url + 'action/wall/quote',
		form: '#ossn-wall-quote-form',
		beforeSend: function () {
			$('#ossn-wall-quote-form').find('#ossn-wall-quote-save').prop('disabled', true);
		},
		callback: function (callback) {
			if (callback && callback.done == 1) {
				window.location.reload();
				return;
			}

			$('#ossn-wall-quote-form').find('#ossn-wall-quote-save').prop('disabled', false);
			Ossn.trigger_message((callback && callback.error) || Ossn.Print('repost:quote:error'), 'error');
		},
		error: function () {
			$('#ossn-wall-quote-form').find('#ossn-wall-quote-save').prop('disabled', false);
			Ossn.trigger_message(Ossn.Print('repost:quote:error'), 'error');
		}
	});
};
