<?php
class MinervaTemplate
{
	/**
	 * create the minerva enabled <a> to another url
	 *
	 * @param string $url
	 * @param string $text
	 * If missing it will use the url
	 * @param array link attributes
	 * @return string
	 */
	static function getRedirectLink($url,$text=null,$attr=null)
	{
		$at=null;
		if (is_array($attr))
		{
			
			foreach ($attr as $key=>$v) $at=$at.$key.'="'.$v.'" ';
			
		}
		$ck=MinervaRegistry::getCookie();
		
		if (!$ck)  
		{
			return '<a href="'.$url.'" '.$at.'>'.htmlentities($text).'</a>';
		}
				
		$tag='<a '.$at.'href="'.$ck->getUrl().'?minerva_goto='.urlencode_deep($url).'">';
		if (!is_null($text)) $tag.= htmlentities($text);
		else $tag.=htmlentities($url);
		$tag.='</a>';
		return $tag;
	}
	
	/**
	 * @return bool
	 */
	static function isMinervaUser()
	{
		return (MinervaRegistry::getCookie()instanceof MinervaCookie) ;
	}
	
	/**
	 * True if a friend is visiting
	 * @return bool
	 */
	static function isFriend()
	{
		if (!MinervaTemplate::isMinervaUser()) return false;
		$fr= MinervaFriends::create();
		return $fr->isFriend(MinervaRegistry::getCookie()->getUrl());
	}
	
	static function getActionLink($text,$action,$param=null)
	{
		$ck=MinervaRegistry::getCookie();
		if (!$ck) return null;
		$tag='<a '.'href="?minerva_action='.urlencode_deep($action).'&minerva_param='.urlencode($param).'">'.htmlentities($text).'</a>';
		return $tag;
	}
	
	/**
	 * Gets the list of owned networks
	 *
	 * @return array(array(name,descr,no_users))
	 */
	static function getMyNetworks()
	{
		$myn= MinervaNetworks::create();
		$rez=array();
		foreach($myn->getMyNetworks() as $net)
		{
			
			$tr=$net;
			$tr['joined']=$myn->isMemberOf($net['name']);
			unset($tr['users']);
			$rez[]=$tr;
		}
		
		return $rez;
	}
	
	static function PanelConfig()
	{
		$options['title']='Minerva';
		$options['view_friends']=true;
		$options['view_buddies']=true;
		$options['view_mynets']=true;
		$options['view_partofnets']=true;
		$options1=get_option('minerva_widget');
		$options=array_merge($options,$options1);
		if ($_POST['minerva-submit'])
		{
			$options['title']=$_POST['minerva-title'];
			$options['view_friends']=($_POST['view_friends']!=null);
			$options['view_buddies']=($_POST['view_buddies']!=null);
			$options['view_mynets']= ($_POST['view_mynets']!=null);
			$options['view_partofnets']= ($_POST['view_partofnets']!=null);
			update_option('minerva_widget',$options);
		}
		$options['view_friends']?$friends=' checked="checked"':'';	
		$options['view_buddies']?$buddies=' checked="checked"':'';
		$options['view_mynets']?$mynets=' checked="checked"':'';
		$options['view_partofnets']?$partofnets=' checked="checked"':'';
		
	?>
		<p><label for="minerva-panel-title"><?php _e('Title:'); ?> <input class="widefat" id="meta-title" name="minerva-title" type="text" value="<?php echo $options['title']; ?>" /></label></p>
		<input type="hidden" name="minerva-submit" value="1" />
		<p>
		<label for="minerva-view_friends"><input id="minerva-view_friends" name="view_friends" type="checkbox"<?php echo $friends; ?> />
		Show friends list
		</label>
		</p>
		<p>
		<label for="minerva-view_buddies"><input id="minerva-view_buddies" name="view_buddies" type="checkbox"<?php echo $buddies; ?> />
		Show buddies list (people who added me as friend)
		</label>
		</p>
		<p>
		<label for="minerva-view_mynets"><input id="minerva-view_mynets" name="view_mynets" type="checkbox"<?php echo $mynets; ?> />
		Show networks I own
		</label>
		</p>
		<p>
		<label for="minerva-view_partofnets"><input id="minerva-view_partofnets" name="view_partofnets" type="checkbox"<?php echo $partofnets; ?> />
		Show other networks I joined
		</label>
		</p>
		<?php
	}
	
	static function Panel($args=null)
	{
		if (!$args)
		{
			$args['before_widget']=null;
			$args['after_widget']=null;
			$args['before_title']=null;
			$args['after_title']=null;
			
		}
	extract($args);
	$options = get_option('minerva_widget');
	$title = empty($options['title']) ? __('Minerva') : $options['title'];
	
?>
		<?php echo $before_widget; ?>
			<div id="minerva_panel">
		<?php echo $before_title . $title . $after_title; ?>
	
	<?php
	$visitor=!MinervaTemplate::isMinervaUser();
	if ($visitor) {
	?>	
	<p>This is a <a rel="external" href="http://minerva.sapiensworks.com">Minerva</a> enabled site. You have to be a Minerva user to add me to your friends list or to join my networks.</p>
	<?php }
	else {
	?><p style="padding-left:2em">
		Hi <strong><?php echo htmlentities(MinervaRegistry::getCookie()->getName()); ?></strong>!
		</p>
	<?php
	}
	//friends
	if ($options['view_friends'])
	{
		$fr=  MinervaFriends::create();
		?>
		<?php if (!$visitor && $fr->canAdd(MinervaRegistry::getCookie())){ ?>
		<div style="text-align:center;margin-top:1em;">
	<a href="?minerva_action=add_friend">Click to add me as friend</a>
	</div>
		<?php } ?>
		<h4>Friends</h4>
		<?php
		$friends=$fr->getMine();
		if (count($friends)>0)
		{
			echo '<ul>';
			foreach ($friends as $url=>$friend) { ?>
<li style="padding:5px 2px;">
<?php echo MinervaTemplate::getRedirectLink($url,$friend); ?>
</li>
<?php } 
			echo '</ul>';
		} else echo '<span>No friends</span>'
		?>	
	<?php 
	}
	
	//buddies
	if ($options['view_buddies'])
	{ ?>
	<h4>People who added me as friend</h4>
<?php 
$friends=$fr->getBuddies();
if (count($friends)>0){
	echo '<ul>';
foreach($friends as $url=>$friend) { ?>
<li style="padding:5px 2px;">
<?php
echo MinervaTemplate::getRedirectLink($url,$friend); ?>
</li>
<?php }
 echo '</ul>';
	
		}
		else echo '<span>Nobody :( ... yet</span>';
	}
	
	//my networks
	if ($options['view_mynets'])
	{?>
	<h4>Networks I've created</h4>
	<?php $nets=MinervaTemplate::getMyNetworks();
	if (count($nets)>0){
	?>
	<ul>
	<?php foreach($nets as $net) { ?>
	<li>
	<?php echo htmlentities($net['name']),'(',$net['no_users'],')'; ?> 
	<?php 
	if (!$net['joined'])
	echo MinervaTemplate::getActionLink('Join','join_network',$net['name']); ?>
	</li>
	<?php } ?>
	</ul>
	<?php
	}
	}
	//other nets
	if ($options['view_partofnets'])
	{
		$mn= MinervaNetworks::create();
		$joined=$mn->getJoinedNetworks();
		?>
		<h4>Other networks I've joined</h4>
		<?php if (count($joined)>0) {
		echo '<ul>';
		foreach($joined as $url=>$net){
			?>
		<li>
		<?php
		if ($url!=MinervaRegistry::MinervaCentral)
		{
			echo MinervaTemplate::getRedirectLink($url,$net);
		}
		else
		{
			echo htmlentities($net),' (default network)';
		}
		?>
		</li>	
		<?php
		}
		echo '</ul>';
		}?>
	<?php
	}
	?>
	</div>
	<?php
	 echo $after_widget; ?>
<?php
	}
}
?>