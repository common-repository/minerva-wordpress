<?php
$net=urldecode($_POST['name']);
$url=urldecode($_POST['url']);
if (!$url)
{
	//Default minerva network
	$url=MinervaRegistry::MinervaCentral;
}

//list members
$cmd= new MinervaTransport();
$owner=false;
if ($url==MinervaRegistry::getUrl())
{
	$owner=true;
}
$my= MinervaNetworks::create();
$si= MinervaRegistry::getSystemUtils()->getSiteInfo();
if (isset($_POST['join']))
{
	$rez=$cmd->sendCommand($url,'joinNetwork',$net,$url,MinervaRegistry::getUrl(),$si->getName(),MinervaRegistry::getSystemUtils()->getToken());
	if ($rez)
	{
	$my->joinedNetwork($net,$url);	
	wp_redirect($_SERVER['REQUEST_URI']);
	die();
	}
	else {
		echo 'Could not join network. Are you already in this network?';
	}
}

elseif (isset($_POST['leave']))
{
	$rez=$cmd->sendCommand($url,'leaveNetwork',$net,$url,MinervaRegistry::getUrl(),MinervaRegistry::getSystemUtils()->getToken());
	if ($rez===true)
	{
	$my->leftNetwork($net,$url);
	wp_redirect($_SERVER['REQUEST_URI']);
	die();
	}
	else 
	{
		echo $rez;
	}
	
}


if ($owner)
{
	$nets=$my->getUsers($net);
}
else
{
	$nets=$cmd->sendCommand($url,'listMembers',$net);
	
}
?>
<div style="padding:2em">
<form action="" method="post">
<div>
<input type="hidden" name="name" value="<?php echo urlencode($net); ?>" />
<input type="hidden" name="url" value="<?php echo urlencode($url); ?>" />
<p>
<?php if (!$my->isPartOf($net,$url) && !$owner){ ?>
<button type="submit" name="join" class="button">Join Network</button>
<?php  }elseif (!$owner) { ?>
<button type="submit" name="leave" class="button" onclick="return confirm('Leave the network?')">Leave Network</button>
<?php } ?>
</p>
</div>
</form>
	<?php 
	if ($nets)
	{?>
	
		<table cellpadding="5px" cellspacing="0" border="1px">
		<thead>
  <tr>
    <th>Site name</th>
    
    <th>Joined on</th>
  </tr>
  </thead>
  <tbody>
  	<?php foreach($nets as $user)
		{ ?>
		<tr>
		<td><?php 
		if (MinervaRegistry::getUrl()!=$user['url'])
		echo MinervaTemplate::getRedirectLink($user['url'],$user['name']);
		else
		{
			echo '<em>',htmlentities($user['name']),'</em>'; 
		}
		?>
		</td>
		

	
		<td><?php echo date('d M Y',strtotime($user['registered_on'])); ?></td>	
		</tr>
		<?php
		}
		?>
		</tbody>
		</table>
	<?php }

?>
</div>