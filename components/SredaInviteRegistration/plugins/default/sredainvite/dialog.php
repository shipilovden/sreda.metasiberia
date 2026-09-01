<?php
$invite = isset($params['invite']) && is_array($params['invite']) ? $params['invite'] : false;
$invites = isset($params['invites']) && is_array($params['invites']) ? $params['invites'] : array();
$shareTitle = htmlspecialchars(ossn_print('sreda:invite:share:title'), ENT_QUOTES, 'UTF-8');
$shareText = htmlspecialchars(ossn_print('sreda:invite:share:text'), ENT_QUOTES, 'UTF-8');
$onlyEnabled = !empty($params['invite_only']);

function sreda_invite_dialog_share($item, $shareTitle, $shareText) {
    if(empty($item['invite_url'])) {
        return '';
    }
    $url = htmlspecialchars($item['invite_url'], ENT_QUOTES, 'UTF-8');
    return '<span class="ossn-wall-share-dropdown">'
        . '<a href="javascript:void(0);" class="sreda-invite-share-toggle ossn-wall-share-toggle" data-share-url="' . $url . '" data-share-title="' . $shareTitle . '" data-share-text="' . $shareText . '" data-share-image="" aria-haspopup="true" aria-expanded="false" aria-label="' . htmlspecialchars(ossn_print('sreda:invite:share'), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(ossn_print('sreda:invite:share'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-share-alt" aria-hidden="true"></i><span>' . htmlspecialchars(ossn_print('sreda:invite:share:button'), ENT_QUOTES, 'UTF-8') . '</span></a>'
        . '</span>';
}

function sreda_invite_dialog_status($status) {
    $keys = array(
        'active' => 'sreda:invite:status:active',
        'reserved' => 'sreda:invite:status:reserved',
        'used' => 'sreda:invite:status:used',
        'revoked' => 'sreda:invite:status:revoked',
    );
    $key = isset($keys[$status]) ? $keys[$status] : 'sreda:invite:status:revoked';
    return htmlspecialchars(ossn_print($key), ENT_QUOTES, 'UTF-8');
}

function sreda_invite_dialog_date($item) {
    $sentAt = !empty($item['last_sent_at']) ? (int) $item['last_sent_at'] : 0;
    $createdAt = !empty($item['created_at']) ? (int) $item['created_at'] : 0;
    $timestamp = $sentAt > 0 ? $sentAt : $createdAt;
    $date = $timestamp > 0 ? date('d.m.Y H:i', $timestamp) : '';
    $label = $sentAt > 0 ? ossn_print('sreda:invite:date:sent') : ossn_print('sreda:invite:date:created');
    return array(
        'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
        'label' => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
    );
}

function sreda_invite_dialog_current($item, $shareTitle, $shareText) {
    if(empty($item['invite_url']) || empty($item['token'])) {
        return '';
    }
    $url = htmlspecialchars($item['invite_url'], ENT_QUOTES, 'UTF-8');
    $token = htmlspecialchars($item['token'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($item['invited_email'], ENT_QUOTES, 'UTF-8');
    $share = sreda_invite_dialog_share($item, $shareTitle, $shareText);
    $currentActions = '';
    if(isset($item['status']) && $item['status'] === 'active') {
        $currentActions .= '<button type="button" class="btn btn-default sreda-invite-resend">' . htmlspecialchars(ossn_print('sreda:invite:resend'), ENT_QUOTES, 'UTF-8') . '</button>';
    }
    if(isset($item['status']) && in_array($item['status'], array('active', 'reserved'), true)) {
        $currentActions .= '<button type="button" class="btn btn-danger sreda-invite-revoke">' . htmlspecialchars(ossn_print('sreda:invite:revoke'), ENT_QUOTES, 'UTF-8') . '</button>';
    }
    return '<section class="sreda-invite-current" data-invite-id="' . (int) $item['id'] . '">'
        . '<div class="sreda-invite-current-heading"><strong>' . htmlspecialchars(ossn_print('sreda:invite:current'), ENT_QUOTES, 'UTF-8') . '</strong><span>' . $email . '</span></div>'
        . '<label class="sreda-invite-field-label" for="sreda-invite-url">' . htmlspecialchars(ossn_print('sreda:invite:link'), ENT_QUOTES, 'UTF-8') . '</label>'
        . '<div class="sreda-invite-link-row"><input id="sreda-invite-url" class="sreda-invite-url" type="text" readonly value="' . $url . '" aria-label="' . htmlspecialchars(ossn_print('sreda:invite:link'), ENT_QUOTES, 'UTF-8') . '" /><button type="button" class="btn btn-primary sreda-invite-copy">' . htmlspecialchars(ossn_print('sreda:invite:copy'), ENT_QUOTES, 'UTF-8') . '</button></div>'
        . '<div class="sreda-invite-actions" data-invite-id="' . (int) $item['id'] . '" data-invite-token="' . $token . '">' . $currentActions . $share . '</div>'
        . '<div class="sreda-invite-current-status" role="status" aria-live="polite"></div></section>';
}
?>
<div class="title">
    <?php echo htmlspecialchars(ossn_print('sreda:invite:title'), ENT_QUOTES, 'UTF-8'); ?>
    <div class="close-box" onclick="Ossn.MessageBoxClose();"><i class="fa fa-times"></i></div>
</div>
<div class="contents">
    <div class="ossn-box-inner">
        <div class="sreda-invite-dialog">
            <p class="sreda-invite-description"><?php echo htmlspecialchars(ossn_print('sreda:invite:description'), ENT_QUOTES, 'UTF-8'); ?></p>

            <label class="sreda-invite-field-label" for="sreda-invite-email"><?php echo htmlspecialchars(ossn_print('sreda:invite:email'), ENT_QUOTES, 'UTF-8'); ?></label>
            <div class="sreda-invite-email-row">
                <input id="sreda-invite-email" class="sreda-invite-email" type="email" maxlength="320" placeholder="name@example.com" autocomplete="email" />
                <button type="button" class="btn btn-primary sreda-invite-send"><?php echo htmlspecialchars(ossn_print('sreda:invite:send'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
            <p class="sreda-invite-email-hint"><?php echo htmlspecialchars(ossn_print('sreda:invite:email:hint'), ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="sreda-invite-status" role="status" aria-live="polite"></div>

            <div class="sreda-invite-current-wrap"><?php echo sreda_invite_dialog_current($invite, $shareTitle, $shareText); ?></div>

            <label class="sreda-invite-checkbox">
                <input type="checkbox" class="sreda-invite-only" <?php echo $onlyEnabled ? 'checked' : ''; ?> />
                <span><?php echo htmlspecialchars(ossn_print('sreda:invite:only'), ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
            <p class="sreda-invite-only-hint"><?php echo htmlspecialchars(ossn_print('sreda:invite:only:hint'), ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="sreda-invite-history">
                <h4><?php echo htmlspecialchars(ossn_print('sreda:invite:recent'), ENT_QUOTES, 'UTF-8'); ?></h4>
                <?php if(empty($invites)) { ?>
                    <p class="sreda-invite-empty"><?php echo htmlspecialchars(ossn_print('sreda:invite:recent:empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } else { ?>
                    <div class="sreda-invite-history-table-wrap">
                    <table class="sreda-invite-history-table">
                        <thead>
                            <tr>
                                <th><?php echo htmlspecialchars(ossn_print('sreda:invite:table:email'), ENT_QUOTES, 'UTF-8'); ?></th>
                                <th><?php echo htmlspecialchars(ossn_print('sreda:invite:table:status'), ENT_QUOTES, 'UTF-8'); ?></th>
                                <th><?php echo htmlspecialchars(ossn_print('sreda:invite:table:date'), ENT_QUOTES, 'UTF-8'); ?></th>
                                <th><?php echo htmlspecialchars(ossn_print('sreda:invite:table:actions'), ENT_QUOTES, 'UTF-8'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php foreach($invites as $item) {
                        $itemToken = !empty($item['token']) ? htmlspecialchars($item['token'], ENT_QUOTES, 'UTF-8') : '';
                        $itemEmail = htmlspecialchars($item['invited_email'], ENT_QUOTES, 'UTF-8');
                        $itemStatus = sreda_invite_dialog_status((string) $item['status']);
                        $itemDate = sreda_invite_dialog_date($item);
                        $itemId = (int) $item['id'];
                    ?>
                        <tr class="sreda-invite-history-item sreda-invite-status-<?php echo htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8'); ?>" data-invite-id="<?php echo $itemId; ?>" data-invite-token="<?php echo $itemToken; ?>" data-status="<?php echo htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td data-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:table:email'), ENT_QUOTES, 'UTF-8'); ?>" class="sreda-invite-history-email"><?php echo $itemEmail; ?></td>
                            <td data-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:table:status'), ENT_QUOTES, 'UTF-8'); ?>"><span class="sreda-invite-status-badge"><?php echo $itemStatus; ?></span></td>
                            <td data-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:table:date'), ENT_QUOTES, 'UTF-8'); ?>"><span class="sreda-invite-history-date"><?php echo $itemDate['date']; ?></span><small class="sreda-invite-history-date-label"><?php echo $itemDate['label']; ?></small></td>
                            <td data-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:table:actions'), ENT_QUOTES, 'UTF-8'); ?>" class="sreda-invite-history-action-cell">
                                <?php if(in_array($item['status'], array('active', 'reserved'), true)) { ?>
                                    <div class="sreda-invite-history-actions">
                                    <?php if($item['status'] === 'active' && $itemToken !== '') { ?>
                                        <button type="button" class="btn btn-default btn-sm sreda-invite-history-resend"><?php echo htmlspecialchars(ossn_print('sreda:invite:resend'), ENT_QUOTES, 'UTF-8'); ?></button>
                                    <?php } elseif($itemToken === '') { ?>
                                        <button type="button" class="btn btn-default btn-sm sreda-invite-history-rotate"><?php echo htmlspecialchars(ossn_print('sreda:invite:rotate'), ENT_QUOTES, 'UTF-8'); ?></button>
                                    <?php } ?>
                                    <button type="button" class="btn btn-danger btn-sm sreda-invite-history-revoke"><?php echo htmlspecialchars(ossn_print('sreda:invite:revoke'), ENT_QUOTES, 'UTF-8'); ?></button>
                                    </div>
                                <?php } else { ?>
                                    <span class="sreda-invite-no-action">—</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                        </tbody>
                    </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="control">
    <div class="controls">
        <a href="javascript:void(0);" onclick="Ossn.MessageBoxClose();" class="btn btn-default btn-sm"><?php echo htmlspecialchars(ossn_print('sreda:invite:close'), ENT_QUOTES, 'UTF-8'); ?></a>
    </div>
</div>
