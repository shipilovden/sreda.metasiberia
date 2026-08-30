<?php
	$hide_loggedin = '';
	$topbar_float =  "position-relative";
	if(ossn_isLoggedin()){		
		$hide_loggedin = "d-none d-md-inline-block";
		$topbar_float = "";
	}
?>
<!-- ossn topbar -->
<div class="topbar <?php echo $topbar_float;?>">
			<?php if(ossn_isLoggedin()){ ?>
			<div class="left-side d-inline-block">
				<div class="topbar-menu-left">
					<li id="sidebar-toggle" data-toggle='0'>
						<a role="button" data-bs-target="#"><?php echo ossn_goblue_lucide_icon('list'); ?></a>
					</li>
				</div>
</div>
<?php } ?>
            <?php if(!ossn_isLoggedin()){ ?>
            <a class="sreda-topbar-favicon" href="<?php echo ossn_site_url();?>"
                aria-label="<?php echo ossn_site_settings('site_name');?>">
                <img src="<?php echo ossn_add_cache_to_url(ossn_theme_url() . 'images/favicon.svg?v=waypoints'); ?>"
                    alt="" width="24" height="24" />
            </a>
            <?php } ?>
			<div class="site-name text-center <?php echo $hide_loggedin;?>">
				<span><a href="<?php echo ossn_site_url();?>"><?php echo ossn_site_settings('site_name');?></a></span>
			</div>
            <?php if(ossn_isLoggedin()){ ?>
			<div class="text-right right-side d-inline-block">
				<div class="topbar-menu-right">
					<ul>
					<?php if(ossn_isAdminLoggedin()){ ?>
					<li class="ossn-topbar-ads">
						<a href="<?php echo ossn_site_url('administrator/component/OssnAds'); ?>"
							 title="<?php echo ossn_print('ads:manager'); ?>"
							 aria-label="<?php echo ossn_print('ads:manager'); ?>">
							<?php echo ossn_goblue_lucide_icon('megaphone'); ?>
						</a>
					</li>
					<?php } ?>
					<li class="ossn-topbar-dropdown-menu">
						<div class="dropdown">
						<?php
							if(ossn_isLoggedin()){						
								echo ossn_plugin_view('output/url', array(
									'role' => 'button',
									'data-bs-toggle' => 'dropdown',
									'data-bs-target' => '#',
									'text' => ossn_goblue_lucide_icon('chevron-down'),
								));									
								echo ossn_view_menu('topbar_dropdown'); 
							}
							?>
						</div>
					</li>                
					<?php
					if(ossn_isLoggedin()){
							echo ossn_plugin_view('notifications/page/topbar');
					}
					?>
					<li id="sibcore-friends-toggle" class="sibcore-friends-rail-toggle">
						<a href="javascript:void(0);" role="button"
							title="<?php echo ossn_print('friends'); ?>"
							aria-label="<?php echo ossn_print('friends'); ?>"
							aria-expanded="false">
							<?php echo ossn_goblue_lucide_icon('users-round'); ?>
						</a>
					</li>
					</ul>
				</div>
			</div>
			<?php } ?>   
            <?php if(!ossn_isLoggedin()){ ?>
            	<a class="btn ossn-topbar-login-btn" href="<?php echo ossn_site_url('login'); ?>"><?php echo ossn_print('site:login');?></a>
            <?php } ?>         
</div>
<!-- ./ ossn topbar -->
