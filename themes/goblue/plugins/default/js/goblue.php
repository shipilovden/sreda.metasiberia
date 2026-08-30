//<script>
$(document).ready(function() {
	/* Apply the selected registration language immediately through OSSN's normal locale loader. */
	var signupLanguage = document.getElementById('sreda-signup-language');
	if (signupLanguage && !signupLanguage.dataset.sredaLanguageHandler) {
		signupLanguage.dataset.sredaLanguageHandler = '1';
		signupLanguage.addEventListener('change', function() {
			var currentUrl = new window.URL(window.location.href);
			currentUrl.searchParams.set('language', this.value);
			window.location.assign(currentUrl.toString());
		});
	}
	if ($.fn.tooltip) {
		$('[data-toggle="tooltip"]').tooltip({
			placement:'left',
		});
	}
	//hide the comment-like-menu,  can't add code in comment or like as both needs
	if (Ossn.Config.isLoggedin === false) {
    	$('<style>').prop('type', 'text/css').html('.menu-likes-comments-share { display: none !important; }').appendTo('head');
	}
	/* Put the chat dock into the application stacking context. This lets the sidebar
	 * (z-index 1100) cover it during its slide animation while keeping the dock above
	 * the feed and footer when the sidebar is closed. */
	var sibcoreAppShell = document.querySelector('.opensource-socalnetwork');
	if (sibcoreAppShell) {
		['.ossn-chat-base.d-none.d-lg-block', '.ossn-chat-windows-long'].forEach(function(selector) {
			var chatLayer = document.querySelector(selector);
			if (chatLayer && chatLayer.parentNode !== sibcoreAppShell) {
				sibcoreAppShell.appendChild(chatLayer);
			}
		});
		document.body.classList.add('sibcore-chat-dock-mounted');
	}
	/* On phones the topbar friends control toggles the compact chat rail. */
	document.addEventListener('click', function(event) {
		if (window.innerWidth > 991) {
			return;
		}
		var friendsToggle = event.target.closest('#sibcore-friends-toggle > a');
		if (!friendsToggle) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();
		var rail = document.querySelector('.ossn-chat-windows-long');
		if (!rail) {
			return;
		}
		var isOpen = document.body.classList.toggle('sibcore-friends-rail-open');
		friendsToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	}, true);
	var sibcoreDrawerCloseTimer = null;
	$(document).on('click', '#sidebar-toggle', function() {
		var $toggle = $(this).attr('data-toggle');
		if ($toggle == 0) {
			if (sibcoreDrawerCloseTimer !== null) {
				window.clearTimeout(sibcoreDrawerCloseTimer);
				sibcoreDrawerCloseTimer = null;
			}
			document.body.classList.remove('sibcore-mobile-drawer-closing');
			$('.sidebar').removeClass('sidebar-close');
			$('.ossn-page-container').removeClass('sidebar-close-page-container');
			$(this).attr('data-toggle', 1);
			if($(document).innerWidth() >= 1300 && $('.ossn-page-loading-annimation').is(':visible')){
				$('.sidebar').addClass('sidebar-open-no-annimation');	
				$('.ossn-page-container').addClass('sidebar-open-page-container-no-annimation');
			} else {
				$('.sidebar').addClass('sidebar-open');
				$('.ossn-page-container').addClass('sidebar-open-page-container');
			}			
			$('.topbar .right-side').addClass('right-side-space');
		}
		if ($toggle == 1) {
			$(this).attr('data-toggle', 0);
			document.body.classList.add('sibcore-mobile-drawer-closing');
			
			$('.sidebar').removeClass('sidebar-open');
			$('.sidebar').removeClass('sidebar-open-no-annimation');
			
			$('.ossn-page-container').removeClass('sidebar-open-page-container');
			$('.ossn-page-container').removeClass('sidebar-open-page-container-no-annimation');
			$('.topbar .right-side').removeClass('right-side-space');
			$('.topbar .right-side').addClass('right-side-nospace');
			$('.sidebar').addClass('sidebar-close');
			$('.ossn-page-container').addClass('sidebar-close-page-container');
			sibcoreDrawerCloseTimer = window.setTimeout(function() {
				document.body.classList.remove('sibcore-mobile-drawer-closing');
				$('.sidebar').removeClass('sidebar-close');
				$('.ossn-page-container').removeClass('sidebar-close-page-container');
				sibcoreDrawerCloseTimer = null;
			}, 500);

		}
		var document_height = $(document).height();
		//$(".sidebar").height(document_height);
	});
	var $chatsidebar = $('.ossn-chat-windows-long .inner');
	if($chatsidebar.length){
		$chatsidebar.css('height', $(window).height() - 45);
	}
	$(document).on("scroll", function() {
		$document_height = $(document).height();						
		//$(".sidebar").height($document_height);
		
		if($chatsidebar.length){
			if ($(document).scrollTop() >= 50) {
				$chatsidebar.addClass('ossnchat-scroll-top');
				$chatsidebar.css('height', $(window).height());
			} else if ($(document).scrollTop() == 0) {
				$chatsidebar.removeClass('ossnchat-scroll-top');
				$chatsidebar.css('height', $(window).height() - 45);
			}
		}
	});
	if($(document).innerWidth() >= 1300){
		$('#sidebar-toggle').trigger('click');
	}
});
//https://www.opensource-socialnetwork.org/component/view/3657/optimize-pre-loader
$(document).ready(function() {
	   $(".ossn-page-loading-annimation").fadeOut("slow");
});
$(window).on('load resize', function () {
	if (document.querySelector("#draggable")) {
		var current_cover_height = 0;
		var current_cover_width = 0;
		if($('.profile-cover').length) {
			current_cover_height = ~~($('.profile-cover').height() + 0.5);
			current_cover_width = ~~($('.profile-cover').width() + 0.5);
		} else if($('.ossn-group-cover').length) {
			current_cover_height = ~~($('.ossn-group-cover').height() + 0.5);
			current_cover_width = ~~($('.ossn-group-cover').width() + 0.5);
		}
		if (current_cover_width < 1024) {
			var theme_config = $('#ossn-theme-config');
			var default_cover_height = theme_config.attr('data-desktop-cover-height');
			var default_cover_width = theme_config.attr('data-minimum-cover-image-width');
					
			// we're on mobile
			const desktop_cover_width  = default_cover_width;
			const desktop_cover_height = default_cover_height;
			
			var real_image_width  = document.querySelector("#draggable").naturalWidth;
			var real_image_height = document.querySelector("#draggable").naturalHeight;
			// 1. how many mobile heights would we need to hold the image?
			var mobile_height_factor = real_image_height / current_cover_height;
			// 2. how many pixels wide would be the scaled mobile image in comparison to fix desktop_cover_width?
			var mobile_pixel_width = desktop_cover_width / mobile_height_factor;
			// 3. how often would these pixels fit into the current coverwidth?
			var mobile_width_factor = current_cover_width / mobile_pixel_width;
			// 4. how many pixels do we get with the current mobile cover height?
			var mobile_pixel_height = mobile_width_factor * current_cover_height;
			// setting the new height already here allows us to retrieve the new scaled image width calculated by the browser
			$('#draggable').css('height', mobile_pixel_height);
			mobile_pixel_width = parseInt($('#draggable').css('width'));
			
			// 5. calculate the height-scaling factor for dragging - get maximum possible scroll top position
			var desktop_scroll_top_max = real_image_height - desktop_cover_height;
			var mobile_scroll_top_max  = mobile_pixel_height - current_cover_height;
			var height_scaling_factor  = desktop_scroll_top_max / mobile_scroll_top_max;
			// 6. calculate the width-scaling factor for dragging - get maximum possible scroll left position
			var desktop_scroll_left_max = real_image_width - desktop_cover_width;
			var mobile_scroll_left_max  = mobile_pixel_width - current_cover_width;
			var width_scaling_factor  = desktop_scroll_left_max / mobile_scroll_left_max;
			// 7. retrieve the saved dragging positions and scale accordingly
			var cover_top    = parseInt($('#draggable').data('top'));
			var cover_left   = parseInt($('#draggable').data('left'));
			var mobile_pixel_top  = cover_top / height_scaling_factor;
			var mobile_pixel_left = cover_left / width_scaling_factor;
			$('#draggable').css('top', mobile_pixel_top);
			$('#draggable').css('left', mobile_pixel_left);
		}
	// don't display cover images before final scale and position is known
	$('#draggable').fadeIn();
	}
});
