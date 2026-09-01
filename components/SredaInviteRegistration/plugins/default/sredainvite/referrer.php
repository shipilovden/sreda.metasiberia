<?php
$inviteToken = input('invite', true);
if($inviteToken !== false && $inviteToken !== '') {
?>
<meta name="referrer" content="no-referrer" />
<?php
}
