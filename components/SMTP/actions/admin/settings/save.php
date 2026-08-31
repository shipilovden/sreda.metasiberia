<?php
/**
 * Save SREDA SMTP settings.
 */
$component = new OssnComponents();
$current = $component->getSettings('SMTP');

$host = trim((string) input('host'));
$port = (int) input('port');
$username = trim((string) input('username'));
$password = (string) input('password');

if (empty($host) || $port < 1 || $port > 65535 || empty($username)) {
    ossn_trigger_message(ossn_print('sreda:smtp:settings:required'), 'error');
    redirect(REF);
}

if ($password === '' && $current && isset($current->password)) {
    $password = $current->password;
}

if ($password === '') {
    ossn_trigger_message(ossn_print('sreda:smtp:settings:password:required'), 'error');
    redirect(REF);
}

$settings = array(
    'host' => $host,
    'port' => (string) $port,
    'username' => $username,
    'password' => $password,
);

if ($component->setSettings('SMTP', $settings)) {
    ossn_trigger_message(ossn_print('sreda:smtp:settings:saved'));
} else {
    ossn_trigger_message(ossn_print('sreda:smtp:settings:error'), 'error');
}

redirect(REF);
