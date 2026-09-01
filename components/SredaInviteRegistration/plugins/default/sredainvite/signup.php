<?php
$inviteToken = input('invite', true);
if($inviteToken !== false && $inviteToken !== '') {
?>
<input type="hidden" name="invite_token" value="<?php echo htmlspecialchars($inviteToken, ENT_QUOTES, 'UTF-8'); ?>" />
<?php
}
