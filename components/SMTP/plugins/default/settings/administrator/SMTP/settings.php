<?php
$component = new OssnComponents();
$settings = $component->getSettings('SMTP');
$status = ossn_smtp_connected();

echo ossn_view_form('smtp/settings/save', array(
    'action' => ossn_site_url() . 'action/admin/smtp/settings/save',
    'class' => 'ossn-admin-form',
));

if (!empty($status['connected'])) {
    echo '<div class="margin-top-10"><strong style="color:#15803d;">', ossn_print('sreda:smtp:status:connected'), '</strong></div>';
} else {
    echo '<div class="margin-top-10"><strong style="color:#b91c1c;">', ossn_print('sreda:smtp:status:failed'), '</strong></div>';
}
