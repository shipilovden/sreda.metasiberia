<?php
/**
 * SREDA registration page
 */
?>
<div class="landing-main sreda-registration-only">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            <div class="glass-signup-card">
                <div class="signup-title">
                    <h2><?php echo ossn_print('create:account'); ?></h2>
                    <span><?php echo ossn_print('its:free'); ?></span>
                </div>
                <?php 
                    echo ossn_view_form('signup', array(
                        'id' => 'ossn-home-signup',
                        'action' => ossn_site_url('action/user/register')
                    ));
                ?>
            </div>
        </div>
    </div>
</div>
