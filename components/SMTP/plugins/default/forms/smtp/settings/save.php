<?php
$component = new OssnComponents();
$settings = $component->getSettings('SMTP');
?>
<div>
    <label class="required-smtp"><?php echo ossn_print('sreda:smtp:host'); ?></label>
    <input type="text" name="host" value="<?php echo !empty($settings->host) ? htmlspecialchars($settings->host, ENT_QUOTES, 'UTF-8') : ''; ?>" />
</div>
<div>
    <label class="required-smtp"><?php echo ossn_print('sreda:smtp:port'); ?></label>
    <input type="number" name="port" min="1" max="65535" value="<?php echo !empty($settings->port) ? (int) $settings->port : 465; ?>" />
</div>
<div>
    <label class="required-smtp"><?php echo ossn_print('sreda:smtp:username'); ?></label>
    <input type="text" name="username" autocomplete="username" value="<?php echo !empty($settings->username) ? htmlspecialchars($settings->username, ENT_QUOTES, 'UTF-8') : ''; ?>" />
</div>
<div>
    <label class="required-smtp"><?php echo ossn_print('sreda:smtp:password'); ?></label>
    <input type="password" name="password" autocomplete="new-password" value="" />
    <small><?php echo ossn_print('sreda:smtp:password:hint'); ?></small>
</div>
<div>
    <input type="submit" class="btn btn-primary" value="<?php echo ossn_print('save'); ?>" />
</div>
