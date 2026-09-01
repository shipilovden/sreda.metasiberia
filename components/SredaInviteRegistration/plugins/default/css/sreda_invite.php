.sreda-invite-dialog {
	box-sizing: border-box;
	width: 100%;
	max-width: 640px;
	color: #27364a;
}

.sreda-invite-description {
	margin: 0 0 16px;
	line-height: 1.55;
}

.sreda-invite-email-row {
	display: flex;
	align-items: stretch;
	gap: 8px;
	width: 100%;
}

.sreda-invite-email {
	box-sizing: border-box;
	min-width: 0;
	flex: 1 1 auto;
	width: 100%;
	padding: 9px 10px;
	border: 1px solid #ccd4df;
	background: #fff;
	color: #27364a;
}

.sreda-invite-email-hint {
	margin: 6px 0 0;
	color: #6c7b8d;
	font-size: 13px;
	line-height: 1.45;
}

.sreda-invite-current {
	margin-top: 18px;
	padding: 14px;
	border: 1px solid #d6dce5;
	background: #f8fafc;
}

.sreda-invite-current-heading {
	display: flex;
	align-items: baseline;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 12px;
}

.sreda-invite-current-heading span {
	min-width: 0;
	color: #5e6e80;
	word-break: break-word;
}

.sreda-invite-actions,
.sreda-invite-history-actions {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
	margin-top: 10px;
}

.sreda-invite-current-status {
	min-height: 18px;
	margin-top: 8px;
	color: #6c7b8d;
	font-size: 13px;
}

.sreda-invite-history {
	margin-top: 22px;
	padding-top: 16px;
	border-top: 1px solid #dfe5ec;
}

.sreda-invite-history h4 {
	margin: 0 0 8px;
	font-size: 16px;
}

.sreda-invite-empty {
	margin: 0;
	color: #6c7b8d;
}

.sreda-invite-history-actions {
	justify-content: flex-end;
	margin-top: 0;
}

.sreda-invite-history-table-wrap {
	width: 100%;
	overflow-x: auto;
}

.sreda-invite-history-table {
	width: 100%;
	min-width: 640px;
	border-collapse: collapse;
	font-size: 13px;
}

.sreda-invite-history-table th,
.sreda-invite-history-table td {
	padding: 9px 8px;
	border-bottom: 1px solid #e5eaf0;
	vertical-align: middle;
	text-align: left;
}

.sreda-invite-history-table th {
	background: #f1f5f9;
	color: #526274;
	font-size: 12px;
	font-weight: 700;
	text-transform: uppercase;
}

.sreda-invite-history-table td:first-child {
	width: 30%;
}

.sreda-invite-history-table td:nth-child(2) {
	width: 19%;
}

.sreda-invite-history-table td:nth-child(3) {
	width: 22%;
	white-space: nowrap;
}

.sreda-invite-history-email {
	word-break: break-word;
}

.sreda-invite-status-badge {
	display: inline-block;
	padding: 3px 7px;
	border-radius: 12px;
	background: #eef2f6;
	color: #526274;
	font-size: 12px;
	font-weight: 600;
}

.sreda-invite-status-active .sreda-invite-status-badge {
	background: #e5f6ed;
	color: #197044;
}

.sreda-invite-status-reserved .sreda-invite-status-badge {
	background: #fff5d6;
	color: #8a6500;
}

.sreda-invite-status-used .sreda-invite-status-badge {
	background: #e8eef8;
	color: #365b8c;
}

.sreda-invite-status-revoked .sreda-invite-status-badge {
	background: #f4e7e7;
	color: #8a3d3d;
}

.sreda-invite-history-date,
.sreda-invite-history-date-label {
	display: block;
}

.sreda-invite-history-date-label {
	margin-top: 3px;
	color: #6c7b8d;
	font-size: 11px;
	line-height: 1.3;
	white-space: normal;
}

.sreda-invite-history-action-cell {
	min-width: 230px;
}

.sreda-invite-no-action {
	color: #6c7b8d;
}

.sreda-invite-field-label {
	display: block;
	margin: 0 0 6px;
	font-weight: 600;
}

.sreda-invite-link-row {
	display: flex;
	align-items: stretch;
	gap: 8px;
	width: 100%;
}

.sreda-invite-url {
	box-sizing: border-box;
	min-width: 0;
	flex: 1 1 auto;
	width: 100%;
	padding: 9px 10px;
	border: 1px solid #ccd4df;
	background: #f7f9fb;
	color: #27364a;
}

.sreda-invite-copy,
.sreda-invite-new {
	white-space: nowrap;
}

.sreda-invite-share-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-top: 18px;
}

.sreda-invite-share-row .sreda-invite-field-label {
	margin: 0;
}

.sreda-invite-share-toggle {
	display: inline-flex;
	align-items: center;
	gap: 7px;
	padding: 8px 12px;
	background: #eef2f6;
	color: #27364a !important;
	font-weight: 600;
	text-decoration: none;
}

.sreda-invite-checkbox {
	display: flex;
	align-items: flex-start;
	gap: 9px;
	margin: 18px 0 4px;
	font-weight: 600;
}

.sreda-invite-checkbox input {
	margin-top: 3px;
}

.sreda-invite-only-hint {
	margin: 0 0 16px 23px;
	color: #6c7b8d;
	font-size: 13px;
	line-height: 1.45;
}

.sreda-invite-status {
	min-height: 20px;
	margin-top: 8px;
	color: #5e6e80;
	font-size: 13px;
}

/* The existing share JS is intentionally reused; these styles make the same
   menu work inside the invite dialog, outside a wall item. */
.sreda-invite-dialog .ossn-wall-share-dropdown {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	z-index: 10;
}

.sreda-invite-dialog .ossn-wall-share-menu {
	display: none;
	position: fixed;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	box-sizing: border-box;
	width: min(360px, calc(100vw - 16px));
	min-width: 0;
	max-width: none;
	max-height: min(70dvh, 560px);
	padding: 4px;
	overflow-x: hidden;
	overflow-y: auto;
	background: #fff;
	border: 1px solid #d6dce5;
	box-shadow: 0 5px 18px rgba(15, 23, 42, 0.2);
	z-index: 10000;
}

.sreda-invite-dialog .ossn-wall-share-menu[hidden] {
	display: none !important;
}

.sreda-invite-dialog .ossn-wall-share-dropdown.is-open .ossn-wall-share-menu {
	display: grid;
}

.sreda-invite-dialog .ossn-wall-share-menu a {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-start !important;
	gap: 8px !important;
	min-height: 36px;
	padding: 8px 10px !important;
	color: #27364a !important;
	font-size: 13px;
	font-weight: 600;
	line-height: 1.2;
	text-align: left;
	text-decoration: none;
}

.sreda-invite-dialog .ossn-wall-share-menu a:hover {
	background: #eef3f8 !important;
}

.sreda-invite-dialog .ossn-wall-share-menu-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	flex: 0 0 18px;
	font-size: 16px;
}

.sreda-invite-registration-error {
	margin: 0;
	line-height: 1.5;
}

/* Match the invite-only notice to the registration form and keep the change
   scoped to this modal instead of changing OSSN's shared message boxes. */
.ossn-message-box.sreda-invite-registration-modal {
	box-sizing: border-box;
	width: calc(100% - 24px);
	max-width: 470px;
}

.ossn-message-box.sreda-invite-registration-modal .title {
	background: #1e293b !important;
	border-bottom: 0 !important;
	color: #fff !important;
}

.ossn-message-box.sreda-invite-registration-modal .title .close-box {
	color: #fff !important;
	opacity: 0.8;
}

.ossn-message-box.sreda-invite-registration-modal .contents {
	background: #fff;
	color: #27364a;
}

.ossn-message-box.sreda-invite-registration-modal .control {
	background: #1e293b !important;
	border-top: 0 !important;
}

.ossn-message-box.sreda-invite-registration-modal .control .controls .btn-default {
	background: #fff !important;
	border-color: #fff !important;
	color: #1e293b !important;
	font-weight: 600;
}

.ossn-message-box.sreda-invite-registration-modal .control .controls .btn-default:hover,
.ossn-message-box.sreda-invite-registration-modal .control .controls .btn-default:focus {
	background: #f1f5f9 !important;
	border-color: #f1f5f9 !important;
	color: #1e293b !important;
}

/* Only the administrator invite dialog is resizable on desktop. The class is
   attached by this component after the real OSSN message-box is opened. */
@media (min-width: 769px) {
	body .ossn-message-box.sreda-invite-admin-modal {
		box-sizing: border-box;
		top: 16px;
		margin-top: 0;
		width: min(760px, calc(100vw - 32px)) !important;
		min-width: 560px !important;
		max-width: calc(100vw - 32px) !important;
		height: min(560px, calc(100dvh - 32px));
		min-height: 420px;
		max-height: calc(100dvh - 32px);
		resize: both;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	body .ossn-message-box.sreda-invite-admin-modal .title,
	body .ossn-message-box.sreda-invite-admin-modal .control {
		flex: 0 0 auto;
	}

	body .ossn-message-box.sreda-invite-admin-modal .contents {
		box-sizing: border-box;
		min-height: 0;
		max-height: none;
		flex: 1 1 auto;
		overflow-x: hidden;
		overflow-y: auto;
	}
}

@media (max-width: 768px) {
	body .ossn-message-box.sreda-invite-admin-modal {
		box-sizing: border-box;
		top: 8px;
		margin-top: 0;
		width: calc(100vw - 16px) !important;
		min-width: 0 !important;
		max-width: calc(100vw - 16px) !important;
		height: auto !important;
		max-height: calc(100dvh - 16px);
		resize: none;
		overflow: hidden;
	}

	body .ossn-message-box.sreda-invite-admin-modal .contents {
		max-height: calc(100dvh - 120px);
		overflow-x: hidden;
		overflow-y: auto;
	}

	.ossn-message-box.sreda-invite-registration-modal {
		margin-top: 24px;
		width: calc(100% - 16px);
	}

	.sreda-invite-link-row {
		flex-direction: column;
	}

	.sreda-invite-email-row {
		flex-direction: column;
	}

	.sreda-invite-copy,
	.sreda-invite-new,
	.sreda-invite-send {
		width: 100%;
	}

	.sreda-invite-current-heading {
		align-items: flex-start;
		flex-direction: column;
	}

	.sreda-invite-history-table,
	.sreda-invite-history-table thead,
	.sreda-invite-history-table tbody,
	.sreda-invite-history-table tr,
	.sreda-invite-history-table td {
		display: block;
		box-sizing: border-box;
		width: 100% !important;
		min-width: 0;
	}

	.sreda-invite-history-table {
		min-width: 0;
	}

	.sreda-invite-history-table thead {
		position: absolute;
		width: 1px;
		height: 1px;
		padding: 0;
		margin: -1px;
		overflow: hidden;
		clip: rect(0, 0, 0, 0);
		white-space: nowrap;
		border: 0;
	}

	.sreda-invite-history-table tr {
		margin-bottom: 10px;
		padding: 8px;
		border: 1px solid #e5eaf0;
		background: #fff;
	}

	.sreda-invite-history-table td {
		display: grid;
		grid-template-columns: minmax(88px, 34%) minmax(0, 1fr);
		gap: 8px;
		padding: 6px 0;
		border-bottom: 0;
		text-align: left;
	}

	.sreda-invite-history-table td::before {
		content: attr(data-label);
		color: #6c7b8d;
		font-size: 12px;
		font-weight: 600;
	}

	.sreda-invite-history-table .sreda-invite-history-action-cell {
		display: block;
		padding-top: 9px;
		border-top: 1px solid #edf0f4;
	}

	.sreda-invite-history-table .sreda-invite-history-action-cell::before {
		display: block;
		margin-bottom: 6px;
	}

	.sreda-invite-history-actions {
		justify-content: flex-start;
		width: 100%;
	}

	.sreda-invite-history-actions .btn,
	.sreda-invite-actions .btn,
	.sreda-invite-actions .ossn-wall-share-dropdown {
		max-width: 100%;
	}

	.sreda-invite-share-row {
		align-items: flex-start;
		flex-direction: column;
		gap: 8px;
	}

	.sreda-invite-dialog .ossn-wall-share-menu {
		width: calc(100vw - 16px);
		max-height: calc(100dvh - 16px);
	}
}
