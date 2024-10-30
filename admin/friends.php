<?php

$mf= MinervaFriends::create();
if (count($_POST)>0)
{
	foreach ($_POST['mf'] as $url)
	{
		
		$mf->remove(urldecode($url));
		$mf->saveItems();
		$cmd= new MinervaTransport();
		$cmd->sendCommand(urldecode($url),'removedFriend',MinervaRegistry::getUrl());
	}
	wp_redirect($_SERVER['REQUEST_URI']);
	die();
}
?>
<h2>My Friends</h2>
<?php
$friends=$mf->getMine();
if (count($friends)>0) {
?>
<form method="post" action="">
<div>
<input class="button" type="submit" name="submit" value="Delete friends" />
</div>
<div>
<ul style="list-style-type: none;">
<?php

foreach ($friends as $url=>$name) {
?>
<li>
<input type="checkbox" name="mf[]" value="<?php echo urlencode($url); ?>" /> <?php echo MinervaTemplate::getRedirectLink($url,$name); ?>
</li>
<?php } ?>
</ul>
</div>
</form>
<?php } else { ?>
<em>No friends :( </em>
<?php } ?>
<h2>My Buddies</h2>
<ul style="list-style-type: none;">
<?php
$friends=$mf->getBuddies();
foreach ($friends as $url=>$name) {
?>
<li>
<?php echo MinervaTemplate::getRedirectLink($url,$name); ?>
</li>
<?php } ?>
</ul>
