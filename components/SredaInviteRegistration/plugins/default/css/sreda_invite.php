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

@media (max-width: 480px) {
	.ossn-message-box.sreda-invite-registration-modal {
		margin-top: 24px;
		width: calc(100% - 16px);
	}

	.sreda-invite-link-row {
		flex-direction: column;
	}

	.sreda-invite-copy,
	.sreda-invite-new {
		width: 100%;
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
