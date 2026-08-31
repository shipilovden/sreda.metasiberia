/** <style> **/
.ossn-wall-container .controls .sreda-giphy-wall-control {
	color: #5d5d5d;
}

.sreda-giphy-wall-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	font-size: 11px;
	font-weight: 700;
	line-height: 1;
}

.sreda-giphy-wall-picker {
	display: none;
	position: fixed;
	width: 360px;
	max-width: calc(100vw - 16px);
	max-height: 460px;
	max-height: 70vh;
	max-height: min(70dvh, 460px);
	overflow: hidden;
	padding: 10px;
	background: #fff;
	border: 1px solid #d7dce3;
	box-shadow: 0 8px 24px rgba(15, 23, 42, 0.22);
	z-index: 10000;
	box-sizing: border-box;
}

.sreda-giphy-wall-picker.is-open {
	display: block;
}

.sreda-giphy-wall-picker,
.sreda-giphy-wall-picker * {
	box-sizing: border-box;
}

.sreda-giphy-wall-picker-header,
.sreda-giphy-wall-search-row {
	display: flex;
	align-items: center;
	gap: 8px;
}

.sreda-giphy-wall-picker-header {
	justify-content: space-between;
	margin-bottom: 8px;
	color: #27364a;
	font-size: 14px;
}

.sreda-giphy-wall-picker-close {
	width: 28px;
	height: 28px;
	padding: 0;
	border: 0;
	background: transparent;
	color: #6b7280;
	cursor: pointer;
	font-size: 20px;
	line-height: 1;
}

.sreda-giphy-wall-picker-close:hover {
	background: #eef3f8;
}

.sreda-giphy-wall-search {
	min-width: 0;
	flex: 1;
	height: 34px;
	padding: 6px 10px;
	border: 1px solid #ccd0d5;
	border-radius: 4px;
	outline: none;
}

.sreda-giphy-wall-search:focus {
	border-color: #278bb0;
	box-shadow: 0 0 0 2px rgba(39, 139, 176, 0.16);
}

.sreda-giphy-wall-search-submit {
	flex: 0 0 auto;
	height: 34px;
	padding: 0 10px;
	border: 0;
	background: #278bb0;
	color: #fff;
	cursor: pointer;
	font-weight: 600;
}

.sreda-giphy-wall-search-submit:hover {
	background: #1f7898;
}

.sreda-giphy-wall-powered {
	margin: 7px 2px;
	color: #7a8492;
	font-size: 11px;
}

.sreda-giphy-wall-results {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 6px;
	max-height: 330px;
	overflow-x: hidden;
	overflow-y: auto;
	padding: 2px;
}

.sreda-giphy-wall-results.is-loading {
	min-height: 90px;
	opacity: 0.55;
	pointer-events: none;
}

.sreda-giphy-wall-result {
	min-width: 0;
	padding: 0;
	border: 1px solid #e1e5ea;
	background: #f6f7f8;
	cursor: pointer;
	overflow: hidden;
}

.sreda-giphy-wall-result:hover,
.sreda-giphy-wall-result:focus {
	border-color: #278bb0;
	outline: none;
}

.sreda-giphy-wall-result img {
	display: block;
	width: 100%;
	height: 84px;
	object-fit: cover;
}

.sreda-giphy-wall-status {
	padding: 14px 4px;
	color: #6b7280;
	font-size: 13px;
	text-align: center;
}

.sreda-giphy-wall-status.is-error {
	color: #b42318;
}

.sreda-giphy-wall-preview {
	display: flex;
	align-items: flex-start;
	gap: 10px;
	margin: 8px 0 0;
	padding: 8px;
	border: 1px solid #e1e5ea;
	background: #fafbfc;
}

.sreda-giphy-wall-preview[hidden] {
	display: none;
}

.sreda-giphy-wall-preview img {
	display: block;
	width: auto;
	max-width: min(220px, 60vw);
	height: auto;
	max-height: 150px;
	object-fit: contain;
}

.sreda-giphy-wall-preview-remove {
	margin-left: auto;
	padding: 4px 7px;
	border: 0;
	background: transparent;
	color: #b42318;
	cursor: pointer;
	font-size: 12px;
}

.ossn-wall-container .controls .sreda-link-preview-control {
	color: #5d5d5d;
}

.sreda-link-preview-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 5px;
	line-height: 1;
}

.sreda-link-preview-button svg {
	width: 15px;
	height: 15px;
}

.sreda-link-preview-panel {
	display: none;
	width: 100%;
	margin: 8px 0 0;
	padding: 10px;
	background: #fff;
	border: 1px solid #d7dce3;
	box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16);
	box-sizing: border-box;
}

.sreda-link-preview-panel.is-open {
	display: block;
}

.sreda-link-preview-panel[hidden],
.sreda-link-preview-selection[hidden],
.sreda-link-preview-status[hidden] {
	display: none;
}

.sreda-link-preview-panel,
.sreda-link-preview-panel * {
	box-sizing: border-box;
}

.sreda-link-preview-header,
.sreda-link-preview-row {
	display: flex;
	align-items: center;
	gap: 8px;
}

.sreda-link-preview-header {
	justify-content: space-between;
	margin-bottom: 8px;
	color: #27364a;
	font-size: 14px;
}

.sreda-link-preview-close {
	width: 28px;
	height: 28px;
	padding: 0;
	border: 0;
	background: transparent;
	color: #6b7280;
	cursor: pointer;
	font-size: 20px;
	line-height: 1;
}

.sreda-link-preview-close:hover {
	background: #eef3f8;
}

.sreda-link-preview-input {
	min-width: 0;
	flex: 1;
	height: 34px;
	padding: 6px 10px;
	border: 1px solid #ccd0d5;
	border-radius: 4px;
	outline: none;
}

.sreda-link-preview-input:focus {
	border-color: #278bb0;
	box-shadow: 0 0 0 2px rgba(39, 139, 176, 0.16);
}

.sreda-link-preview-submit {
	flex: 0 0 auto;
	height: 34px;
	padding: 0 12px;
	border: 0;
	background: #278bb0;
	color: #fff;
	cursor: pointer;
	font-weight: 600;
}

.sreda-link-preview-submit:hover {
	background: #1f7898;
}

.sreda-link-preview-status {
	padding: 10px 2px 2px;
	color: #6b7280;
	font-size: 13px;
}

.sreda-link-preview-status.is-error {
	color: #b42318;
}

.sreda-link-preview-selection {
	display: flex;
	align-items: stretch;
	gap: 10px;
	margin-top: 10px;
	padding: 8px;
	border: 1px solid #e1e5ea;
	background: #fafbfc;
}

.sreda-link-preview-selection[hidden] {
	display: none;
}

.sreda-link-preview-selection-image {
	display: block;
	flex: 0 0 112px;
	width: 112px;
	height: 78px;
	object-fit: cover;
	background: #eef1f4;
}

.sreda-link-preview-selection-content {
	display: flex;
	min-width: 0;
	flex: 1;
	flex-direction: column;
	gap: 4px;
}

.sreda-link-preview-selection-title,
.sreda-link-preview-selection-description,
.sreda-link-preview-selection-domain {
	overflow: hidden;
	text-overflow: ellipsis;
}

.sreda-link-preview-selection-title {
	color: #27364a;
	white-space: nowrap;
}

.sreda-link-preview-selection-description {
	display: -webkit-box;
	color: #5f6b78;
	font-size: 12px;
	line-height: 1.35;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}

.sreda-link-preview-selection-domain {
	color: #7a8492;
	font-size: 11px;
	white-space: nowrap;
}

.sreda-link-preview-selection-remove {
	align-self: flex-start;
	padding: 3px 0;
	border: 0;
	background: transparent;
	color: #b42318;
	cursor: pointer;
	font-size: 12px;
}

.sreda-link-preview-card {
	display: flex;
	align-items: stretch;
	width: 100%;
	margin: 10px 0;
	padding: 0;
	border: 1px solid #dfe4ea;
	background: #fff;
	color: inherit;
	text-decoration: none;
	overflow: hidden;
}

.sreda-link-preview-card:hover {
	border-color: #b8c5d2;
	text-decoration: none;
}

.sreda-link-preview-image {
	display: block;
	flex: 0 0 160px;
	width: 160px;
	min-height: 108px;
	background: #eef1f4;
}

.sreda-link-preview-image img {
	display: block;
	width: 100%;
	height: 100%;
	min-height: 108px;
	object-fit: cover;
}

.sreda-link-preview-content {
	display: flex;
	min-width: 0;
	flex: 1;
	flex-direction: column;
	gap: 5px;
	padding: 10px 12px;
}

.sreda-link-preview-title {
	color: #27364a;
	font-size: 15px;
	line-height: 1.3;
}

.sreda-link-preview-description {
	display: -webkit-box;
	overflow: hidden;
	color: #5f6b78;
	font-size: 13px;
	line-height: 1.35;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 3;
}

.sreda-link-preview-domain {
	margin-top: auto;
	color: #7a8492;
	font-size: 11px;
}

@media (max-width: 480px) {
	.sreda-giphy-wall-picker {
		width: calc(100vw - 16px);
		max-width: calc(100vw - 16px);
		padding: max(8px, env(safe-area-inset-top)) max(8px, env(safe-area-inset-right)) max(8px, env(safe-area-inset-bottom)) max(8px, env(safe-area-inset-left));
	}

	.sreda-giphy-wall-results {
		max-height: 48dvh;
	}

	.sreda-giphy-wall-result img {
		height: 72px;
	}

	.sreda-link-preview-row {
		align-items: stretch;
		flex-wrap: wrap;
	}

	.sreda-link-preview-input,
	.sreda-link-preview-submit {
		width: 100%;
		flex-basis: 100%;
	}

	.sreda-link-preview-selection {
		gap: 8px;
	}

	.sreda-link-preview-selection-image {
		flex-basis: 88px;
		width: 88px;
		height: 68px;
	}

	.sreda-link-preview-card {
		flex-direction: column;
	}

	.sreda-link-preview-image {
		width: 100%;
		height: 160px;
		min-height: 0;
		flex-basis: auto;
	}

	.sreda-link-preview-image img {
		min-height: 0;
	}
}
