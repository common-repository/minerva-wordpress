<?php
if (count($_POST)>0)
{
	require_once 'network.php';
	
}
else {
	$cmd= new MinervaTransport();
	$nets=$cmd->sendCommand(MinervaRegistry::MinervaCentral,'listNetworks');
	if ($nets)
	{?>
	
  <script type="text/javascript">
  function Submit(id)
  {
  	
  	jQuery("#net"+id).submit();
  	return false;
  }
  </script>
  <p>
  <?php echo MinervaTemplate::getRedirectLink('http://minerva.sapiensworks.com/blog/','Visit Minerva Blog'); ?>
  </p>
		<table cellpadding="5px" cellspacing="0" border="1px">
		<thead>
  <tr>
    <th>Network Name</th>
    <th>Description</th>
    <th>Users</th>
    <th>Owner</th>
    <th>Created on</th>
    <th>Status</th>
    
  </tr>
  </thead>
  <tbody>

   	<?php
$mynet= MinervaNetworks::create();
   	foreach($nets as $net)
		{ ?>
		<tr>
		<td><a href="<?php echo $_SERVER['REQUEST_URI']; ?>" onclick="return Submit(<?php echo $net['id']; ?>)"><?php echo htmlentities($net['name']); ?></a></td>
		<td><?php echo htmlentities($net['description']); ?></td>
		<td><?php echo $net['users'] ?></td>
		<td>
		<form id="net<?php echo $net['id']; ?>" action="" method="post">
		<div>
		<input type="hidden" name="name" value="<?php echo urlencode($net['name']); ?>" />
		<input type="hidden" name="url" value="<?php echo urlencode($net['url']); ?>" />
		<?php 
		if (!$net['url'])
		{
			echo 'Minerva';
		}
		elseif($net['url']==MinervaRegistry::getUrl())
		{
			echo $net['owner_name'];
		}
		else
		{
		echo MinervaTemplate::getRedirectLink($net['url'],$net['owner_name']);
		}
		 ?>
		 
		 </div>
		</form>
		<td><?php echo date('d M Y',strtotime($net['created_on'])); ?></td>	
		<td>
		<?php
		$nurl=$net['url'];
		if (empty($nurl)) $nurl=MinervaRegistry::MinervaCentral;
	if ($mynet->isPartOf($net['name'],$nurl) || $mynet->isOwner($net['url']))
	{
		echo '<em style="color:#57AF56">Joined</em>';
	}
	else 
	{
		
	}
		?>
		</td>
		</tr>
		<?php
		}
		?>
		</tbody>
		</table>
		
	<?php }
}
