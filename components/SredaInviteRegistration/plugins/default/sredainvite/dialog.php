<?php
$invite      = $params['invite'];
$inviteAvailable = is_array($invite) && !empty($invite['invite_url']);
$inviteUrl   = $inviteAvailable ? htmlspecialchars($invite['invite_url'], ENT_QUOTES, 'UTF-8') : '';
$shareTitle  = htmlspecialchars(ossn_print('sreda:invite:share:title'), ENT_QUOTES, 'UTF-8');
$shareText   = htmlspecialchars(ossn_print('sreda:invite:share:text'), ENT_QUOTES, 'UTF-8');
$onlyEnabled = !empty($params['invite_only']);
$inviteError = is_array($invite) && isset($invite['error']) ? $invite['error'] : 'error';
?>
<div class="title">
    <?php echo htmlspecialchars(ossn_print('sreda:invite:title'), ENT_QUOTES, 'UTF-8'); ?>
    <div class="close-box" onclick="Ossn.MessageBoxClose();"><i class="fa fa-times"></i></div>
</div>
<div class="contents">
    <div class="ossn-box-inner">
        <div class="sreda-invite-dialog">
            <?php if(!$inviteAvailable) { ?>
            <p class="sreda-invite-description sreda-invite-warning"><?php echo htmlspecialchars(ossn_print($inviteError === 'missing_token' ? 'sreda:invite:missing_token' : 'sreda:invite:error'), ENT_QUOTES, 'UTF-8'); ?></p>
            <button type="button" class="btn btn-primary sreda-invite-new"><?php echo htmlspecialchars(ossn_print('sreda:invite:new'), ENT_QUOTES, 'UTF-8'); ?></button>
            <div class="sreda-invite-status" role="status" aria-live="polite"></div>
            <?php } else { ?>
            <p class="sreda-invite-description"><?php echo htmlspecialchars(ossn_print('sreda:invite:description'), ENT_QUOTES, 'UTF-8'); ?></p>

            <label class="sreda-invite-field-label" for="sreda-invite-url"><?php echo htmlspecialchars(ossn_print('sreda:invite:link'), ENT_QUOTES, 'UTF-8'); ?></label>
            <div class="sreda-invite-link-row">
                <input id="sreda-invite-url" class="sreda-invite-url" type="text" readonly value="<?php echo $inviteUrl; ?>" aria-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:link'), ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="button" class="btn btn-primary sreda-invite-copy"><?php echo htmlspecialchars(ossn_print('sreda:invite:copy'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>

            <div class="sreda-invite-share-row">
                <span class="sreda-invite-field-label"><?php echo htmlspecialchars(ossn_print('sreda:invite:share'), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="ossn-wall-share-dropdown">
                    <a href="javascript:void(0);" class="sreda-invite-share-toggle ossn-wall-share-toggle" data-share-url="<?php echo $inviteUrl; ?>" data-share-title="<?php echo $shareTitle; ?>" data-share-text="<?php echo $shareText; ?>" data-share-image="" aria-haspopup="true" aria-expanded="false" aria-label="<?php echo htmlspecialchars(ossn_print('sreda:invite:share'), ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(ossn_print('sreda:invite:share'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-share-alt" aria-hidden="true"></i><span><?php echo htmlspecialchars(ossn_print('sreda:invite:share:button'), ENT_QUOTES, 'UTF-8'); ?></span></a>
                </span>
            </div>

            <label class="sreda-invite-checkbox">
                <input type="checkbox" class="sreda-invite-only" <?php echo $onlyEnabled ? 'checked' : ''; ?> />
                <span><?php echo htmlspecialchars(ossn_print('sreda:invite:only'), ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
            <p class="sreda-invite-only-hint"><?php echo htmlspecialchars(ossn_print('sreda:invite:only:hint'), ENT_QUOTES, 'UTF-8'); ?></p>

            <button type="button" class="btn btn-default sreda-invite-new"><?php echo htmlspecialchars(ossn_print('sreda:invite:new'), ENT_QUOTES, 'UTF-8'); ?></button>
            <div class="sreda-invite-status" role="status" aria-live="polite"></div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="control">
    <div class="controls">
        <a href="javascript:void(0);" onclick="Ossn.MessageBoxClose();" class="btn btn-default btn-sm"><?php echo htmlspecialchars(ossn_print('cancel'), ENT_QUOTES, 'UTF-8'); ?></a>
    </div>
</div>
