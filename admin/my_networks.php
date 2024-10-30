<?php
$myn=MinervaNetworks::create();
$nets=$myn->getMyNetworks();
if ($nets)
{
	$curnet=current($nets);
}

if (count($_POST)>0)
{
	$name= urldecode($_POST['select_net']);
	$cmd= new MinervaTransport();
	$token=MinervaRegistry::getSystemUtils()->getToken();
	if (isset($_POST['del_members']))
	{
		$toremove=$_POST['rm'];
		if (count($toremove)>0)
		{
			foreach($toremove as $t1)
			{
				$t2=urldecode($t1);
				$myn->removeMember($name,$t2);
				//notify member
				$cmd->sendCommand($t2,'kickedFromNetwork',$name,MinervaRegistry::getUrl(),$token);
			}
			$det=$myn->getDetails($name);
			$cmd->sendCommand(MinervaRegistry::MinervaCentral,'updateNetworkMembers',$name,MinervaRegistry::getUrl(),$token,$det['no_users']);
		}
	}
	
	if (isset($_POST['delete']))
	{
		
		
		//notify central
		if ($cmd->sendCommand(MinervaRegistry::MinervaCentral,'deleteNetwork',$name,MinervaRegistry::getUrl(),$token))
		{
			$myn->delete($name);
			
		wp_redirect($_SERVER['REQUEST_URI']);
		}
		else 
		{
			echo' Could not notify the central server. Please try again later.';
		}
		die();
	}
	
	elseif (isset($_POST['create']) || isset($_POST['update']))
	{
		$name=substr(stripslashes_deep($_POST['name']),0,100);
		$descr=substr(stripslashes_deep($_POST['descr']),0,500);
		if (empty($descr)) $descr=(string)null;
		if (isset($_POST['create']))
		{
		if (!$myn->add($name,$descr))
		{
		wp_redirect($_SERVER['REQUEST_URI']);
		die();
		}
		
		//notify central
		if (empty($descr)) $descr=(string)null;
		if (!$cmd->sendCommand(MinervaRegistry::MinervaCentral,'addNetwork',$name,$descr,MinervaRegistry::getUrl(),$token))
		{
			$myn->delete($name);
			echo 'Could not notify the central server. Please try again later';
		}

		}
		else 
		{
			$old=stripslashes_deep($_POST['old']);
		if (!$myn->update($old,$name,$descr))
		{
		wp_redirect($_SERVER['REQUEST_URI']);
		die();
		}
		//notify central
		$cmd->sendCommand(MinervaRegistry::MinervaCentral,'updateNetwork',$old,$name,$descr,MinervaRegistry::getUrl(),$token);
		}
		
	}
		$nets=$myn->getMyNetworks();	
	$curnet=$myn->getDetails($name);
}

$det=$myn->getDetails($curnet['name']);
if ($det)
{
	extract($det);
}

?>
<div class="m_container" style="padding:1em">
<form action="" method="post" id="myform">
<p>
<script type="text/javascript">
function changeNet()
{
	jQuery("#myform").submit();
	return false;
}
</script>
<select id="select_net" name="select_net" onchange="return changeNet();"<?php if (count($nets)==0) echo ' disabled="disabled"' ?>>
<?php foreach($nets as $net_name){ ?>
<option<?php if ($net_name['name']==$curnet['name']) echo ' selected="selected"'; ?> value="<?php echo urlencode($net_name['name']); ?>"><?php echo htmlentities($net_name['name']); ?></option>
<?php } ?>
</select>
</p>
<?php
$members=$myn->getUsers($curnet['name']);
if ($members){ ?>
<fieldset>
<legend>Members</legend>
<div>
<input type="submit" name="del_members" value="Remove selected members" class="button" />
</div>

	<table cellpadding="5px" cellspacing="0" border="1px">
		<thead>
  <tr>
    <th>Site name</th>
    
    <th>Joined on</th>
  </tr>
  </thead>
  <tbody>
  	<?php foreach($members as $user)
		{ ?>
		<tr>
		<td>
		<?php if(!$user['owner']){ ?>
		<input type="checkbox" name="rm[]" value="<?php echo urlencode($user['url']); ?>" />
		<?php } ?>
		
		<?php 
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

</fieldset>
<?php } ?>
<fieldset>
<legend>Network Properties</legend>
<div>
<label for="name">Name</label><br />
<input type="hidden"  name="old" value="<?php echo htmlentities($name); ?>" /><br />
<input size="70" type="text" maxlength="100" name="name" id="name" value="<?php echo htmlentities($name); ?>" /><br />
<label for="descr">Description</label><br />
<textarea id="descr" name="descr" rows="5" cols="68"><?php echo htmlentities($descr); ?></textarea>
<div>
<input class="button" type="submit" name="create" value="Create new" />
<input class="button" type="submit" name="update" value="Update current" />
<input class="button" type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this network and all its members?')" />
</div>
</div>

</fieldset>

</form>
</div>