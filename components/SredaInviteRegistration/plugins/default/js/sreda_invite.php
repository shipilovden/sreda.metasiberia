(function ($) {
	'use strict';

	if (window.sredaInviteRegistrationLoaded) {
		return;
	}
	window.sredaInviteRegistrationLoaded = true;

	function escapeHtml(value) {
		return $('<div>').text(value || '').html();
	}

	function showError(message) {
		if (window.Ossn && typeof Ossn.trigger_message === 'function') {
			Ossn.trigger_message(message, 'error');
		}
	}

	function print(key, fallback) {
		if (window.Ossn && typeof Ossn.Print === 'function') {
			var value = Ossn.Print(key);
			return value && value !== key ? value : fallback;
		}
		return fallback;
	}

	function showInviteRegistrationError(data) {
		var message = data.invite_error_message || data.dataerr || '';
		if (!message || !window.Ossn || typeof Ossn.ModalBox !== 'function') {
			return;
		}
		$('#ossn-signup-errors').addClass('d-none').empty();
		Ossn.ModalBox({
			title: escapeHtml(data.invite_error_title || print('sreda:invite:registration:title', 'SREDA')),
			content: '<p class="sreda-invite-registration-error">' + escapeHtml(message) + '</p>'
		});
	}

	function removeInviteTokenFromAddressBar() {
		var signupForm = document.getElementById('ossn-home-signup');
		if (!signupForm || !window.history || typeof window.history.replaceState !== 'function' || typeof window.URL !== 'function') {
			return;
		}

		try {
			var currentUrl = new window.URL(window.location.href);
			if (!currentUrl.searchParams.has('invite')) {
				return;
			}
			var hiddenToken = signupForm.querySelector('input[name="invite_token"]');
			if (!hiddenToken || !hiddenToken.value) {
				return;
			}
			currentUrl.searchParams.delete('invite');
			window.history.replaceState(window.history.state, document.title, currentUrl.pathname + currentUrl.search + currentUrl.hash);
		} catch (error) {
			// Keeping the query token is safer than removing it before the form
			// has a confirmed hidden value.
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

		var $input = $('<textarea>').val(value).css({
			position: 'fixed',
			left: '-9999px',
			top: '0'
		}).appendTo('body');
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

	function dialogRequest(url, params, callback, beforeSend, requestError) {
		if (!window.Ossn || typeof Ossn.PostRequest !== 'function') {
			showError('SREDA: запрос недоступен');
			return;
		}
		Ossn.PostRequest({
			url: Ossn.site_url + url,
			params: params || '',
			beforeSend: beforeSend || function () {},
			callback: callback,
			error: function () {
				showError('SREDA: не удалось выполнить запрос');
				if (typeof requestError === 'function') {
					requestError();
				}
			}
		});
	}

	function showInviteDialogLoading() {
		$('.ossn-halt').addClass('ossn-light')
			.css('height', $(document).height() + 'px')
			.fadeIn(300);
		$('.ossn-message-box').html('<div class="ossn-loading ossn-box-loading"></div>').fadeIn(400);
	}

	function showInviteDialogError() {
		var title = escapeHtml(print('sreda:invite:title', 'Пригласить в SREDA'));
		var message = escapeHtml(print('sreda:invite:dialog:error', 'Не удалось загрузить окно приглашения.'));
		var retry = escapeHtml(print('sreda:invite:dialog:retry', 'Повторить'));
		var content = '<div class="title">' + title + '<div class="close-box" onclick="Ossn.MessageBoxClose();"><i class="fa fa-times"></i></div></div>';
		content += '<div class="contents"><div class="ossn-box-inner"><p class="sreda-invite-dialog-error">' + message + '</p><button type="button" class="btn btn-primary sreda-invite-dialog-retry">' + retry + '</button></div></div>';
		$('.ossn-message-box').html(content).fadeIn();
	}

	function openInviteDialog() {
		if (!window.Ossn || typeof Ossn.PostRequest !== 'function') {
			showInviteDialogLoading();
			showInviteDialogError();
			return;
		}

		Ossn.PostRequest({
			url: Ossn.site_url + 'sreda/invite/dialog',
			params: '',
			beforeSend: showInviteDialogLoading,
			callback: function (content) {
				if (typeof content !== 'string' || content.indexOf('sreda-invite-dialog') === -1) {
					if (window.console && typeof console.error === 'function') {
						console.error('SREDA invite dialog returned an unexpected response.');
					}
					showInviteDialogError();
					return;
				}
				$('.ossn-message-box').html(content);
			},
			error: function (xhr, status) {
				if (window.console && typeof console.error === 'function') {
					console.error('SREDA invite dialog request failed.', {
						status: xhr && xhr.status ? xhr.status : 0,
						textStatus: status || 'error'
					});
				}
				showInviteDialogError();
			}
		});
	}

	function updateShareToggle($dialog, inviteUrl) {
		var $toggle = $dialog.find('.sreda-invite-share-toggle');
		$toggle.attr('data-share-url', inviteUrl);
		$dialog.find('.ossn-wall-share-menu').remove();
		$dialog.find('.ossn-wall-share-dropdown').removeClass('is-open');
	}

	$(document).on('click', '.menu-section-item-a-sreda-invite-registration', function (event) {
		event.preventDefault();
		openInviteDialog();
	});

	$(document).on('click', '.sreda-invite-dialog-retry', function (event) {
		event.preventDefault();
		openInviteDialog();
	});

	$(document).on('click', '.sreda-invite-copy', function (event) {
		event.preventDefault();
		var $dialog = $(this).closest('.sreda-invite-dialog');
		copyText($dialog.find('.sreda-invite-url').val(), function (copied) {
			if (window.Ossn && typeof Ossn.trigger_message === 'function') {
				Ossn.trigger_message(copied ? print('share:copied', 'Ссылка скопирована') : print('share:copy:error', 'Не удалось скопировать ссылку'), copied ? 'success' : 'error');
			}
		});
	});

	$(document).on('change', '.sreda-invite-only', function () {
		var $checkbox = $(this);
		var $dialog = $checkbox.closest('.sreda-invite-dialog');
		$checkbox.prop('disabled', true);
		dialogRequest('action/sreda/invite/settings', 'invite_only=' + ($checkbox.prop('checked') ? 'on' : 'off'), function (data) {
			if (data && data.success) {
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || 'Настройки сохранены');
				}
			} else {
				$checkbox.prop('checked', !$checkbox.prop('checked'));
				showError((data && data.error) || 'Не удалось сохранить настройку');
			}
			$checkbox.prop('disabled', false);
		}, function () {
			$dialog.find('.sreda-invite-status').text('');
		}, function () {
			$checkbox.prop('disabled', false);
		});
	});

	$(document).on('click', '.sreda-invite-new', function (event) {
		event.preventDefault();
		var $button = $(this);
		var $dialog = $button.closest('.sreda-invite-dialog');
		$button.prop('disabled', true);
			dialogRequest('action/sreda/invite/create', '', function (data) {
			if (data && data.success && data.invite_url) {
				if ($dialog.find('.sreda-invite-url').length) {
					$dialog.find('.sreda-invite-url').val(data.invite_url);
					updateShareToggle($dialog, data.invite_url);
				} else if (window.Ossn && typeof Ossn.MessageBoxClose === 'function') {
					Ossn.MessageBoxClose();
					setTimeout(function () {
						openInviteDialog();
					}, 0);
				}
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || 'Новая ссылка создана');
				}
			} else {
				showError((data && data.error) || 'Не удалось создать ссылку');
			}
			$button.prop('disabled', false);
		}, null, function () {
			$button.prop('disabled', false);
		});
	});

	// OSSN's standard signup handler keeps the form in place on dataerr. This
	// second listener adds the requested dialog without replacing that form.
	$(document).ajaxSuccess(function (event, xhr, settings, response) {
		var data = response;
		if (typeof data === 'string') {
			try {
				data = JSON.parse(data);
			} catch (error) {
				return;
			}
		}
		if (!data || !data.invite_error || !settings || settings.url.indexOf('action/user/register') === -1) {
			return;
		}
		showInviteRegistrationError(data);
	});

	$(removeInviteTokenFromAddressBar);
}(jQuery));
