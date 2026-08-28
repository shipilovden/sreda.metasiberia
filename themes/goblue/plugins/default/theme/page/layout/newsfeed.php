<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network (OSSN)
 * @author    OSSN Core Team <info@openteknik.com>
 * @copyright (C) OpenTeknik LLC
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
 
//unused pagebar skeleton when ads are disabled #628 
$sidebar = '';
$isempty = '';
if(ossn_is_hook('newsfeed', "sidebar:right")) {
	$newsfeed_right = ossn_call_hook('newsfeed', "sidebar:right", NULL, array());
	$sidebar = implode('', $newsfeed_right);
	$isempty = trim($sidebar);
}
$newsfeed_wall_class = !empty($isempty)
	? 'col-12 col-lg-8 newsfeed-col-wall'
	: 'col-12 col-lg-8 newsfeed-col-wall newsfeed-col-wall-centered';
//show center:top div only when there is something otherwise on phone it results empty div with padding/whitebg.
if(ossn_is_hook('newsfeed', "center:top")) {
	$newsfeed_center_top = ossn_call_hook('newsfeed', "center:top", NULL, array());
	$newsfeed_center_top = implode('', $newsfeed_center_top);
	$isempty_top 	     = trim($newsfeed_center_top);
}
//[E] Change container fluid to container-xl layout newsfeed #2564
?>
<div class="container-xl">
	<div class="ossn-layout-newsfeed">
		<div class="row">
			<div class="<?php echo $newsfeed_wall_class; ?>">
				<?php if(!empty($isempty_top)){ ?>
				<div class="newsfeed-middle-top">
					<?php echo $newsfeed_center_top; ?>
				</div>
				 <?php } ?>
				<div class="newsfeed-middle">
					<?php echo $params['content']; ?>
				</div>
			</div>
			
			<?php if(!empty($isempty)){ ?>
			<div class="col-12 col-lg-4 newsfeed-col-sidebar">
				<?php if(!empty($isempty)){ ?>
				<div class="newsfeed-right">
					<?php echo $sidebar; ?>                                     
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php echo ossn_plugin_view('theme/page/elements/footer');?>
</div>
