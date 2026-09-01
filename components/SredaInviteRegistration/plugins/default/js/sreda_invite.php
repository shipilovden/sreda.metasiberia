(function ($) {
	'use strict';

	if (window.sredaInviteRegistrationLoaded) {
		return;
	}
	window.sredaInviteRegistrationLoaded = true;

	function clearInviteAdminModalClass() {
		$('.ossn-message-box.sreda-invite-admin-modal').removeClass('sreda-invite-admin-modal');
	}

	function installInviteAdminModalCloseGuard() {
		if (!window.Ossn || typeof Ossn.MessageBoxClose !== 'function' || Ossn.sredaInviteCloseGuardInstalled) {
			return;
		}
		var originalMessageBoxClose = Ossn.MessageBoxClose;
		Ossn.MessageBoxClose = function () {
			clearInviteAdminModalClass();
			return originalMessageBoxClose.apply(this, arguments);
		};
		Ossn.sredaInviteCloseGuardInstalled = true;
	}

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
		var $messageBox = $('.ossn-message-box');
		$messageBox.addClass('sreda-invite-registration-modal');
		$messageBox.find('.control .btn-default').text(print('sreda:invite:registration:acknowledge', 'Понятно'));
	}

	$(document).on('click.sredaInviteRegistrationModal', '.ossn-message-box.sreda-invite-registration-modal .close-box, .ossn-message-box.sreda-invite-registration-modal .control .btn-default', function () {
		$(this).closest('.ossn-message-box').removeClass('sreda-invite-registration-modal');
	});

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
		$('.ossn-message-box').addClass('sreda-invite-admin-modal');
		$('.ossn-halt').addClass('ossn-light')
			.css('height', $(document).height() + 'px')
			.fadeIn(300);
		$('.ossn-message-box').html('<div class="ossn-loading ossn-box-loading"></div>').fadeIn(400);
	}

	function showInviteDialogError() {
		$('.ossn-message-box').addClass('sreda-invite-admin-modal');
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
		installInviteAdminModalCloseGuard();

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
				$('.ossn-message-box').addClass('sreda-invite-admin-modal').html(content);
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

	function refreshInviteDialog($dialog, callback) {
		var $messageBox = $dialog.closest('.ossn-message-box');
		if (!$messageBox.length || !window.Ossn || typeof Ossn.PostRequest !== 'function') {
			showError(print('sreda:invite:dialog:error', 'Не удалось загрузить окно приглашения.'));
			return;
		}
		Ossn.PostRequest({
			url: Ossn.site_url + 'sreda/invite/dialog',
			params: '',
			callback: function (content) {
				if (typeof content !== 'string' || content.indexOf('sreda-invite-dialog') === -1) {
					showError(print('sreda:invite:dialog:error', 'Не удалось загрузить окно приглашения.'));
					return;
				}
				$messageBox.addClass('sreda-invite-admin-modal').html(content);
				if (typeof callback === 'function') {
					callback($messageBox.find('.sreda-invite-dialog'));
				}
			},
			error: function () {
				showError(print('sreda:invite:dialog:error', 'Не удалось загрузить окно приглашения.'));
			}
		});
	}

	function updateShareToggle($dialog, inviteUrl) {
		var $toggle = $dialog.find('.sreda-invite-share-toggle');
		$toggle.attr('data-share-url', inviteUrl);
		$dialog.find('.ossn-wall-share-menu').remove();
		$dialog.find('.ossn-wall-share-dropdown').removeClass('is-open');
	}

	function currentInviteMarkup(data) {
		var email = escapeHtml(data.email || data.invited_email || '');
		var token = escapeHtml(data.token || '');
		var url = escapeHtml(data.invite_url || '');
		var shareLabel = escapeHtml(print('sreda:invite:share', 'Поделиться приглашением'));
		var shareButton = escapeHtml(print('sreda:invite:share:button', 'Открыть Share-меню'));
		var shareTitle = escapeHtml(print('sreda:invite:share:title', 'Приглашение в SREDA'));
		var shareText = escapeHtml(print('sreda:invite:share:text', 'Приглашаю тебя присоединиться к SREDA. Это персональное приглашение для регистрации.'));
		return '<section class="sreda-invite-current" data-invite-id="' + escapeHtml(data.invite_id || data.id || '') + '">'
			+ '<div class="sreda-invite-current-heading"><strong>' + escapeHtml(print('sreda:invite:current', 'Текущее приглашение')) + '</strong><span>' + email + '</span></div>'
			+ '<label class="sreda-invite-field-label" for="sreda-invite-url">' + escapeHtml(print('sreda:invite:link', 'Ссылка-приглашение')) + '</label>'
			+ '<div class="sreda-invite-link-row"><input id="sreda-invite-url" class="sreda-invite-url" type="text" readonly value="' + url + '" aria-label="' + escapeHtml(print('sreda:invite:link', 'Ссылка-приглашение')) + '" /><button type="button" class="btn btn-primary sreda-invite-copy">' + escapeHtml(print('sreda:invite:copy', 'Копировать')) + '</button></div>'
			+ '<div class="sreda-invite-actions" data-invite-id="' + escapeHtml(data.invite_id || data.id || '') + '" data-invite-token="' + token + '"><button type="button" class="btn btn-default sreda-invite-resend">' + escapeHtml(print('sreda:invite:resend', 'Отправить повторно')) + '</button><button type="button" class="btn btn-danger sreda-invite-revoke">' + escapeHtml(print('sreda:invite:revoke', 'Отозвать приглашение')) + '</button><span class="ossn-wall-share-dropdown"><a href="javascript:void(0);" class="sreda-invite-share-toggle ossn-wall-share-toggle" data-share-url="' + url + '" data-share-title="' + shareTitle + '" data-share-text="' + shareText + '" data-share-image="" aria-haspopup="true" aria-expanded="false" aria-label="' + shareLabel + '" title="' + shareLabel + '"><i class="fa fa-share-alt" aria-hidden="true"></i><span>' + shareButton + '</span></a></span></div>'
			+ '<div class="sreda-invite-current-status" role="status" aria-live="polite"></div></section>';
	}

	function renderCurrentInvite($dialog, data) {
		if (!data || !data.invite_url || !data.token) {
			return;
		}
		$dialog.find('.sreda-invite-current-wrap').html(currentInviteMarkup(data));
		$dialog.find('.sreda-invite-email').val(data.email || data.invited_email || '');
	}

	function requestInviteSend($button, $dialog, token, callback) {
		if (!token) {
			showError(print('sreda:invite:token:unavailable', 'Ссылка недоступна в этой сессии. Создайте новое приглашение.'));
			return;
		}
		$button.prop('disabled', true);
		dialogRequest('action/sreda/invite/resend', 'invite_token=' + encodeURIComponent(token), function (data) {
			if (data && data.success) {
				renderCurrentInvite($dialog, data);
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || print('sreda:invite:resent', 'Приглашение отправлено повторно'));
				}
				refreshInviteDialog($dialog);
			} else {
				showError((data && data.error) || print('sreda:invite:send:error', 'Не удалось отправить приглашение. Попробуйте ещё раз.'));
				if (data && data.invite_url && data.token) {
					refreshInviteDialog($dialog);
				}
			}
			if (typeof callback === 'function') {
				callback(data);
			}
			$button.prop('disabled', false);
		}, null, function () {
			$button.prop('disabled', false);
		});
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

	$(document).on('click', '.sreda-invite-send', function (event) {
		event.preventDefault();
		var $button = $(this);
		var $dialog = $button.closest('.sreda-invite-dialog');
		var email = $.trim($dialog.find('.sreda-invite-email').val() || '');
		if (!email) {
			showError(print('sreda:invite:email:invalid', 'Введите корректный e-mail'));
			return;
		}
		$button.prop('disabled', true);
		dialogRequest('action/sreda/invite/create', 'email=' + encodeURIComponent(email), function (data) {
			if (data && data.invite_url && data.token) {
				renderCurrentInvite($dialog, data);
			}
			if (data && data.success) {
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || print('sreda:invite:sent', 'Приглашение отправлено'));
				}
				refreshInviteDialog($dialog);
			} else {
				showError((data && data.error) || print('sreda:invite:send:error', 'Не удалось отправить приглашение. Попробуйте ещё раз.'));
				if (data && data.invite_url && data.token) {
					refreshInviteDialog($dialog);
				}
			}
			$button.prop('disabled', false);
		}, null, function () {
			$button.prop('disabled', false);
		});
	});

	$(document).on('click', '.sreda-invite-resend, .sreda-invite-history-resend', function (event) {
		event.preventDefault();
		var $button = $(this);
		var $dialog = $button.closest('.sreda-invite-dialog');
		var $source = $button.closest('[data-invite-token]');
		var token = $source.attr('data-invite-token') || '';
		requestInviteSend($button, $dialog, token, function (data) {
			// The modal content is refreshed by requestInviteSend without a page reload.
		});
	});

	function revokeInvite($button, $dialog, inviteId, token) {
		if (!inviteId && !token) {
			showError(print('sreda:invite:revoke:error', 'Не удалось отозвать приглашение'));
			return;
		}
		$button.prop('disabled', true);
		var params = 'invite_id=' + encodeURIComponent(inviteId || '');
		if (token) {
			params += '&invite_token=' + encodeURIComponent(token);
		}
		dialogRequest('action/sreda/invite/revoke', params, function (data) {
			if (data && data.success) {
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || print('sreda:invite:revoked', 'Приглашение отозвано'));
				}
				refreshInviteDialog($dialog);
			} else {
				showError((data && data.error) || print('sreda:invite:revoke:error', 'Не удалось отозвать приглашение'));
			}
			$button.prop('disabled', false);
		}, null, function () {
			$button.prop('disabled', false);
		});
	}

	$(document).on('click', '.sreda-invite-revoke, .sreda-invite-history-revoke', function (event) {
		event.preventDefault();
		var $button = $(this);
		var $source = $button.closest('.sreda-invite-history-item, .sreda-invite-current');
		var email = $.trim($source.find('.sreda-invite-history-email').text() || '');
		if (!email) {
			email = $.trim($source.find('.sreda-invite-current-heading span').text() || '');
		}
		var confirmText = print('sreda:invite:revoke:confirm', 'Отозвать приглашение для %s?\\n\\nПосле этого зарегистрироваться по этой ссылке будет невозможно.');
		confirmText = confirmText.replace(/%s/g, email || 'этого пользователя').replace(/\\n/g, '\n');
		if (!window.confirm(confirmText)) {
			return;
		}
		var $dialog = $button.closest('.sreda-invite-dialog');
		var token = $source.attr('data-invite-token') || $source.find('[data-invite-token]').first().attr('data-invite-token') || '';
		revokeInvite($button, $dialog, $source.attr('data-invite-id') || '', token);
	});

	$(document).on('click', '.sreda-invite-history-rotate', function (event) {
		event.preventDefault();
		var $button = $(this);
		var $source = $button.closest('[data-invite-id]');
		var $dialog = $button.closest('.sreda-invite-dialog');
		var email = $.trim($source.find('.sreda-invite-history-email').text() || '');
		var confirmText = print('sreda:invite:rotate:confirm', 'Создать новую ссылку для %s?\\n\\nСтарая ссылка сразу станет недействительной.');
		confirmText = confirmText.replace(/\\n/g, '\n').replace(/%s/g, email || 'этого пользователя');
		if (!$source.attr('data-invite-id') || !window.confirm(confirmText)) {
			return;
		}
		$button.prop('disabled', true);
		dialogRequest('action/sreda/invite/rotate', 'invite_id=' + encodeURIComponent($source.attr('data-invite-id')), function (data) {
			if (data && data.invite_url && data.token) {
				renderCurrentInvite($dialog, data);
			}
			if (data && data.success) {
				if (window.Ossn && typeof Ossn.trigger_message === 'function') {
					Ossn.trigger_message(data.message || print('sreda:invite:sent', 'Приглашение отправлено'));
				}
				refreshInviteDialog($dialog);
			} else {
				showError((data && data.error) || print('sreda:invite:send:error', 'Не удалось отправить приглашение. Попробуйте ещё раз.'));
				if (data && data.rotation && data.invite_url && data.token) {
					refreshInviteDialog($dialog);
				}
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

	function applyInviteEmailBinding() {
		var binding = document.querySelector('.sreda-invite-email-binding');
		var form = document.getElementById('ossn-home-signup');
		if (!binding || !form || !binding.value) {
			return;
		}
		var email = binding.value;
		['email', 'email_re'].forEach(function (name) {
			var field = form.querySelector('input[name="' + name + '"]');
			if (!field) {
				return;
			}
			field.value = email;
			field.readOnly = true;
			field.classList.add('sreda-invite-bound-email');
		});
	}

	$(function () {
		applyInviteEmailBinding();
		removeInviteTokenFromAddressBar();
	});
}(jQuery));
