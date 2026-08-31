/***********************************
	Ossn Wall <style>
*************************************/

.ossn-wall {}

.ossn-wall-items {}

.ossn-wall-item {
	padding: 15px;
	padding-top: 10px;
	margin-top: 20px;
	background-color: #fff;
	padding-bottom: 0px;
	border-radius: 10px;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.ossn-wall-item:first-child {
	margin-top: 0px;
}

.ossn-wall-item .friends a {
	text-decoration: none;
}

.ossn-wall-item .friends a:first-child:before {
	content: "-";
	margin-left: 5px;
	margin-right: 5px;
}

.ossn-wall-item .user-img {
	border-radius: 50px;
	display: inline-block;
	float: left;
	margin-right: 10px;
}

.ossn-wall-item .meta {}

.ossn-wall-item .meta .user {
	margin-top: 3px;
}

.ossn-wall-item .meta .user a {
	font-weight: bold;
}

.ossn-wall-item .meta .user span {
	color: #999;
}

.ossn-wall-item .post-contents {
	margin-top: 15px;
}

.ossn-wall-item .post-contents p {
	/** Incorrect Hyphenation in the theme GoBlue 3.0 #824 **/
	word-break: break-word;
	text-align: justify;
}

.ossn-wall-item .post-contents img {
	max-width: 100%;
	border: 1px solid #eae8e8;
	display: block;
	margin-bottom: 10px;
}

.ossn-wall-item .meta .post-menu {
	float: right;
}

.ossn-wall-item .meta .post-menu .btn-link {
	font-size: 14px;
}

.ossn-wall-container {
	margin-bottom: 10px;
	border-radius: 10px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.ossn-wall-container .controls {
	background-color: #F6F7F8;
	margin-top: 5px;
	border: 1px solid #E9EAED;
	padding: 5px 10px;
	margin-left: -10px;
	margin-right: -10px;
	border-left: 0;
	border-right: 0;
}

.ossn-wall-container .wall-tabs {
	background-color: #F6F7F8;
	border: 1px solid #E9EAED;
	border-top-right-radius: 10px;
	border-top-left-radius: 10px;
}

.ossn-wall-container .wall-tabs .item {
	padding: 10px;
	display: inline-flex;
	cursor: pointer;
	align-items: center; 
}
.wall-tabs .item span {
    line-height: 1; 
}
.ossn-wall-container .wall-tabs .item:hover {
	background: #eee;
}

.ossn-wall-container .wall-tabs .item:first-child {
	border-top-left-radius: 10px;
}

.ossn-wall-container .wall-tabs .item div {
	display: inline-block;
}

.ossn-wall-container .wall-tabs .item .text {
	font-weight: bold;
	margin-top: 1px;
	margin-left: 5px;
	position: absolute;


	font-size: 15px;
}

.ossn-wall-container .tabs-input {}

.ossn-wall-container .controls li {
	padding: 7px;
	background: #e5e5e5e0;
	display: inline-block;
	border-radius: 50%;
	cursor: pointer;
	width: 35px;
	height: 35px;
	text-align: center;
}

.ossn-wall-container .controls .ossn-wall-friend,
.ossn-wall-container .controls .ossn-wall-location,
.ossn-wall-container .controls .ossn-wall-photo,
.ossn-wall-container-control-menu-emojii-selector {
	color: #5d5d5d;
}

.ossn-wall-container .controls li:hover {
	background: #fff;
}

.ossn-wall-post-button-container {
	display: inline-table;
	float: right;
}

.ossn-wall-privacy-dummy,
.ossn-wall-privacy {
	margin-right: 5px;
	padding: 5px 10px;
	background: #e5e5e5e0;
	border-radius: 10px;
	cursor: pointer;
	display: inline-block;
	margin-top: 10px;
}

.ossn-wall-privacy-dummy {
	background: #e5e5e5e0;
	cursor: initial;
	opacity: 0.5;
}

.ossn-wall-privacy:hover {
	background: #eeeeee8c;
}

.ossn-wall-privacy-dummy span>span,
.ossn-wall-privacy span>span {
	margin-left: 5px;
	float: right;
}

.ossn-wall-container .ossn-wall-post {
	padding: 3px 20px;
	margin-top: 6px;
	margin: 10px auto;
	border-radius: 5px;
}

.ossn-wall-container i {
	font-size: 15px;
	margin-right: 0;
}

.ossn-wall-container-data {
	background: #fff;
	padding: 10px;
	border-bottom-left-radius: 10px;
	border-bottom-right-radius: 10px;
	border: 1px solid #E5E5E5;
	border-bottom-color: #ccc;
	border-width: 0 1px 2px 1px;
}

#ossn-wall-photo {
	margin-top: 10px;
}

.ossn-wall-container input[type="file"],
.ossn-wall-container input[type="text"] {
	width: 100%;
	border-top: 1px dashed #E9EAED;
	padding: 5px;
	margin-bottom: 5px;
	margin-top: -5px;
	outline: none;
}

.ossn-wall-container input[type="file"] {
	border: 1px solid #E9EAED;
	border-radius: 10px;
	background: #fff;
}

#token-input-ossn-wall-friend-input {
	width: 100% !important;
	padding: 7px;
	margin-bottom: 5px;
	margin-top: -5px;
	background: #fff;
	border: 0;
}

#ossn-wall-location-input {
	background: #fff;
	border: 1px solid #E9EAED;
	border-radius: 10px;
}

#ossn-wall-location .ap-input-icon svg {
	top: 15px
}

#ossn-wall-form .ossn-loading {
	margin: 7px;
}

.ossn-wall-item-type {
	display: inline-block;
}

.ossn-wall-item .friends {
	display: inline-block;
}

.ossn-form textarea#post-edit {
	height: 125px;
}

.ossn-wall-post-delete {
	color: #EC2020 !important;
}

.ossn-wall-loading {
	text-align: center;
	padding: 10px;
	width: 100%;
}

.ossn-wall-loading .ossn-loading {
	display: inline-block;
}

#ossn-wall-form .ui-autocomplete-loading {
	background: white url("<?php echo ossn_theme_url();?>images/loading.gif") right center no-repeat;
}

#ossn-wall-form .ui-helper-hidden-accessible {
	display: none;
}

.ossn-wall-post-time {
	cursor: pointer;
}

.ossn-wall-post-time:hover {
	text-decoration: underline;
}

.wall-tabs .item span {
	padding-left: 5px;
	font-weight: bold;
	font-family: 'PT Sans', sans-serif;
	font-weight: bold;
	font-size: 13px;
	bottom: 0;
}

.group-wall .ossn-wall-post-button-container {
	height: 50px;
	display: inline-block;
}

.group-wall .ossn-wall-post {
	float: right;
}

#ossn-wall-location .mapboxgl-ctrl-geocoder--input {
	padding-left: 30px;
	background: initial;
	border-radius: 10px;
	border: 1px dashed #eee;
	margin-top: 5px;
}

.ossn-wall-image-container {
	background: #f8f8f8;
}

.ossn-wall-image-container>img {
	max-height: 80vh;
	margin: 0 auto;
}

.ossn-wall-item>.dropdown-menu {
	min-width: 200px;
}

.ossn-wall-item .dropdown-menu li a:before {
	content: "\f068";
	display: inline-block;
	float: left;
	margin-right: 10px;
	font-family: var(--fa-style-family, "Font Awesome 6 Free");
	font-weight: var(--fa-style, 900);
}

.ossn-wall-item .post-control-edit:before {
	content: "\f303" !important;
}

.ossn-wall-item .post-control-delete:before {
	content: "\f2ed" !important;
}

.ossn-wall-item .post-control-repost {
	display: inline-flex;
	align-items: center;
	gap: 4px;
}

.ossn-wall-item .post-control-repost .ossn-lucide-icon {
	    width: 1.35em;
	    height: 1.35em;
	    vertical-align: -0.2em;
}

.ossn-wall-item .post-control-repost.ossn-repost-in-xhr {
	opacity: 0.5;
	pointer-events: none;
}

.ossn-wall-item .post-control-share {
	display: inline-flex;
	align-items: center;
	gap: 4px;
}

.ossn-wall-item .post-control-share::before {
	display: none !important;
	content: none !important;
}

.ossn-wall-item .menu-likes-comments-share > li a.post-control-share::before,
.ossn-wall-item .menu-likes-comments-share > li a.ossn-wall-share-toggle::before,
.ossn-wall-item .ossn-wall-share-toggle::before {
	display: none !important;
	content: none !important;
}

.ossn-wall-item .ossn-wall-share-icon-only {
	gap: 0;
}

/* Keep all wall actions as equal icon-only controls. */
.ossn-wall-item .menu-likes-comments-share > li {
	display: flex;
	align-items: center;
	justify-content: center;
}

.ossn-wall-item .menu-likes-comments-share > li > a,
.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-repost-dropdown,
.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-share-dropdown {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 40px !important;
	height: 40px !important;
	margin: 0 auto !important;
	gap: 0 !important;
}

.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-repost-dropdown > a,
.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-share-dropdown > a {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 40px !important;
	height: 40px !important;
	gap: 0 !important;
}

.ossn-wall-item .menu-likes-comments-share > li > a.post-control-like,
.ossn-wall-item .menu-likes-comments-share > li > a.post-control-comment,
.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-repost-dropdown > a,
.ossn-wall-item .menu-likes-comments-share > li > .ossn-wall-share-dropdown > a,
.ossn-wall-item .menu-likes-comments-share > li > a.entity-menu-extra-like,
.ossn-wall-item .menu-likes-comments-share > li > a.entity-menu-extra-comment,
.ossn-wall-item .menu-likes-comments-share > li > a.entity-menu-extra-repost,
.ossn-wall-item .menu-likes-comments-share > li > a.entity-menu-extra-share {
	font-size: 0 !important;
}

.ossn-wall-item .menu-likes-comments-share > li a.post-control-like > span,
.ossn-wall-item .menu-likes-comments-share > li a.post-control-comment > span,
.ossn-wall-item .menu-likes-comments-share > li a.post-control-repost > span,
.ossn-wall-item .menu-likes-comments-share > li a.entity-menu-extra-like > span,
.ossn-wall-item .menu-likes-comments-share > li a.entity-menu-extra-comment > span,
.ossn-wall-item .menu-likes-comments-share > li a.entity-menu-extra-repost > span {
	display: none !important;
}

.ossn-wall-item .menu-likes-comments-share > li a.post-control-like::before,
.ossn-wall-item .menu-likes-comments-share > li a.post-control-comment::before,
.ossn-wall-item .menu-likes-comments-share > li a.entity-menu-extra-like::before,
.ossn-wall-item .menu-likes-comments-share > li a.entity-menu-extra-comment::before {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	height: 18px;
	margin: 0 !important;
	font-size: 18px;
	line-height: 1 !important;
}

.ossn-wall-item .menu-likes-comments-share > li .post-control-repost .ossn-lucide-icon,
.ossn-wall-item .menu-likes-comments-share > li .entity-menu-extra-repost .ossn-lucide-icon {
	width: 18px;
	height: 18px;
	margin: 0;
}

.ossn-wall-item .menu-likes-comments-share > li .post-control-share .ossn-lucide-icon,
.ossn-wall-item .menu-likes-comments-share > li .entity-menu-extra-share .ossn-lucide-icon {
	width: 16px;
	height: 16px;
	margin: 0;
}

.ossn-wall-item .post-control-share .ossn-lucide-icon {
	width: 1.35em;
	height: 1.35em;
	vertical-align: -0.2em;
}

.ossn-wall-item .ossn-wall-share-dropdown {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 40px;
	z-index: 10;
}

.ossn-wall-item .ossn-wall-share-menu {
	display: none;
	position: absolute;
	left: 50%;
	bottom: calc(100% + 6px);
	transform: translateX(-50%);
	grid-template-columns: repeat(2, minmax(0, 1fr));
	width: min(360px, calc(100vw - 24px));
	min-width: min(300px, calc(100vw - 24px));
	padding: 4px;
	background: #fff;
	border: 1px solid #d6dce5;
	box-shadow: 0 5px 18px rgba(15, 23, 42, 0.2);
	max-height: 70vh;
	max-height: min(70dvh, 560px);
	overflow-x: hidden;
	overflow-y: auto;
	z-index: 100;
}

.ossn-wall-item .ossn-wall-share-menu[hidden] {
	display: none !important;
}

.ossn-wall-item .ossn-wall-share-dropdown.is-open .ossn-wall-share-menu {
	display: grid;
}

.ossn-wall-item .ossn-wall-share-menu a {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-start !important;
	gap: 8px !important;
	width: auto !important;
	height: auto !important;
	min-height: 36px;
	padding: 8px 10px !important;
	color: #27364a !important;
	font-size: 13px;
	font-weight: 600;
	white-space: normal;
	line-height: 1.2;
	text-align: left;
}

.ossn-wall-item .ossn-wall-share-menu a::before {
	display: none !important;
	content: none !important;
}

.ossn-wall-item .ossn-wall-share-menu a:hover {
	background: #eef3f8 !important;
}

.ossn-wall-item .ossn-wall-share-menu-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	flex: 0 0 18px;
	font-size: 16px;
	color: #27364a;
}

.ossn-wall-item .menu-likes-comments-share > .ossn-wall-share-dropdown {
	flex: 1;
}

/* Repost menu: one predictable action in every wall row, with two real modes. */
.ossn-wall-item .ossn-wall-repost-dropdown {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 40px;
	z-index: 10;
}

.ossn-wall-item .ossn-wall-repost-toggle {
	position: relative;
}

.ossn-wall-item .ossn-wall-repost-menu {
	display: none;
	position: absolute;
	left: 50%;
	bottom: calc(100% + 6px);
	transform: translateX(-50%);
	min-width: 170px;
	padding: 4px;
	background: #fff;
	border: 1px solid #d6dce5;
	box-shadow: 0 5px 18px rgba(15, 23, 42, 0.2);
	z-index: 100;
}

.ossn-wall-item .ossn-wall-repost-menu[hidden] {
	display: none !important;
}

.ossn-wall-item .ossn-wall-repost-dropdown.is-open .ossn-wall-repost-menu {
	display: flex;
	flex-direction: column;
}

.ossn-wall-item .ossn-wall-repost-menu a {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-start !important;
	gap: 9px !important;
	width: auto !important;
	height: auto !important;
	min-height: 36px;
	padding: 8px 11px !important;
	color: #27364a !important;
	font-size: 14px;
	font-weight: 600;
	white-space: nowrap;
	text-align: left;
}

.ossn-wall-item .ossn-wall-repost-menu a::before {
	display: none !important;
	content: none !important;
}

.ossn-wall-item .ossn-wall-repost-menu a:hover {
	background: #eef3f8 !important;
}

.ossn-wall-item .ossn-wall-repost-menu .ossn-lucide-icon {
	width: 18px;
	height: 18px;
	flex: 0 0 18px;
}

/* Entity menus render anchors without the wall template's <li> wrapper. */
.ossn-wall-item .menu-likes-comments-share > .ossn-wall-repost-dropdown {
	flex: 1;
}

.ossn-wall-repost-label {
	display: flex;
	align-items: center;
	gap: 5px;
	margin-bottom: 8px;
	color: #777;
	font-size: 13px;
}

.ossn-wall-repost-label .ossn-lucide-icon {
	width: 1em;
	height: 1em;
}

.ossn-wall-repost-quote {
	margin: 0 0 10px;
	font-weight: 500;
}

.ossn-wall-textarea {
	min-height: 200px;
	outline: none;
}

#ossn-wall-form .ossn-wall-textarea[contenteditable="true"]:empty::before {
	content: attr(placeholder);
	pointer-events: none;
	display: block;
}

/**************************
	Mobile Layout Settings
***************************/

@media (max-width: 991px) {
	/* The share popup is viewport-positioned by ossn_share.php on every screen. */
	.ossn-wall-item .ossn-wall-share-menu {
		position: fixed;
		top: 8px;
		left: 8px;
		right: 8px;
		bottom: auto;
		width: auto;
		min-width: 0;
		max-width: none;
		max-height: min(70dvh, 420px);
		overflow-x: hidden;
		overflow-y: auto;
		transform: none;
	}
}

@media (max-width: 480px) {
	.ossn-wall-item-type {
		display: block;
	}

	.ossn-wall-privacy-dummy,
	.ossn-wall-privacy {
		float: none;
		margin-right: 0;
	}

	.ossn-wall-container .controls {
		height: auto;
	}

	.ossn-wall-container textarea {
		margin-left: 0px;
		width: 100%;
	}
}

@media screen and (min-width:1500px) {
	.ossn-wall-container .wall-tabs i {
		margin-top: 3px;
	}
}


/********************
	Changes 9.6
*********************/
.ossn-wall-textarea {
	min-height: 60px;
	outline: none;
	padding: 10px;
	padding-left: 40px;
}

.ossn-wall-textarea {
	white-space: pre-wrap;
	/* Make sure browser don't add &nbps  */
}

.ossn-wall-token {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 6px;
	font-weight: 500;
	line-height: 1.2;
	user-select: none;
	transition: background 0.2s;
}

.ossn-wall-userimage-form {
	width: 30px;
	height: 30px;
	position: absolute;
	margin-top: 7px;
}

.ossn-wall-userimage-form img {
	border-radius: 50%;
}
