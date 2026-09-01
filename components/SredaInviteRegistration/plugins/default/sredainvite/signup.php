<?php
$inviteToken = input('invite', true);
if($inviteToken !== false && $inviteToken !== '') {
?>
<input type="hidden" name="invite_token" value="<?php echo htmlspecialchars($inviteToken, ENT_QUOTES, 'UTF-8'); ?>" />
<?php
		$inviteDetails = SredaInvite::getInviteForToken($inviteToken);
		if($inviteDetails && in_array($inviteDetails['status'], array('active', 'reserved'), true)) {
				$boundEmail = htmlspecialchars($inviteDetails['email'], ENT_QUOTES, 'UTF-8');
				?>
				<input type="hidden" class="sreda-invite-email-binding" value="<?php echo $boundEmail; ?>" />
				<p class="sreda-invite-email-hint"><?php echo htmlspecialchars(ossn_print('sreda:invite:registration:email:hint', array($inviteDetails['email'])), ENT_QUOTES, 'UTF-8'); ?></p>
				<?php
		}
}
