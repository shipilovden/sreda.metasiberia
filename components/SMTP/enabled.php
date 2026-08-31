<?php
/**
 * Default SMTP component settings.
 */
$component = new OssnComponents();
if (!$component->getSettings('SMTP')) {
    $component->setSettings('SMTP', array(
        'host' => '',
        'port' => '465',
        'username' => '',
        'password' => '',
    ));
}
