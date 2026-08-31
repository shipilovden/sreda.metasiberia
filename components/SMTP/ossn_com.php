<?php
/**
 * SREDA SMTP notifications component.
 *
 * Uses the PHPMailer copy already bundled with OSSN. Credentials are stored
 * only in the component settings and are never part of the Git repository.
 */
define('__SMTP__', ossn_route()->com . 'SMTP/');

function ossn_com_smtp_init() {
    ossn_add_hook('email', 'config', 'ossn_smtp', 1);

    if (ossn_isAdminLoggedin()) {
        ossn_register_com_panel('SMTP', 'settings');
        ossn_register_action('admin/smtp/settings/save', __SMTP__ . 'actions/admin/settings/save.php');
    }
}

function ossn_smtp_settings() {
    $component = new OssnComponents();
    $settings = $component->getSettings('SMTP');

    if (!$settings || empty($settings->host) || empty($settings->port) || empty($settings->username) || empty($settings->password)) {
        return false;
    }

    return $settings;
}

function ossn_smtp_configure_mail($mail, $settings) {
    if (!is_object($mail) || !$settings) {
        return false;
    }

    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = trim($settings->host);
    $mail->Port = (int) $settings->port;
    $mail->Username = $settings->username;
    $mail->Password = $settings->password;
    $mail->Timeout = 15;

    if ((int) $settings->port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ((int) $settings->port === 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = '';
    }

    return $mail;
}

function ossn_smtp($hook, $type, $mail, $return) {
    $settings = ossn_smtp_settings();
    $configured = ossn_smtp_configure_mail($mail, $settings);

    return $configured ?: $return;
}

function ossn_smtp_connected() {
    $settings = ossn_smtp_settings();
    $result = array('connected' => false);

    if (!$settings) {
        return $result;
    }

    $mail = new OssnMail();
    if (!ossn_smtp_configure_mail($mail, $settings)) {
        return $result;
    }

    try {
        if ($mail->smtpConnect()) {
            $mail->smtpClose();
            $result['connected'] = true;
        }
    } catch (Exception $exception) {
        // Do not expose SMTP credentials or provider details in the admin UI.
        $result['connected'] = false;
    }

    return $result;
}

ossn_register_callback('ossn', 'init', 'ossn_com_smtp_init');
