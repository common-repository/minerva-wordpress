<?php
/**
 * Here we implement all the abstract stuff from the Core
 *
 */


class MinervaSystemUtils implements IMinervaSystemUtils 
{
	function setCookie($name,$value=null,$expire=null)
	{
		setcookie($name,$value,$expire,COOKIEPATH,COOKIE_DOMAIN);
	}
	
	/**
	 * Local Salt - must be the same everytime
	 *
	 * @return string
	 */
	function getSalt()
	{
		return wp_salt();
	}
	
	private $user;
	
	/**
	 * Gets the user id authenticated on host application
	 * Returns false if user not logged in
	 *
	 * @return int | false
	 */
	function getCurrentUserId()
	{
		if (is_null($this->user))
		{
			$u= wp_get_current_user();
			$this->user=$u->ID;
		}
		return $this->user;
	}
	
	/**
	 * @param int $user
	 * User id
	 * @return UserInfo
	 */
	function getUserInfo($user)
	{
		$b=get_userdata($user);
		if (!$b) return false;
		$ui= new UserInfo($b->display_name);
		return $ui;
	}
	
	
	private $si;
	
	function getSiteInfo()
	{
		if (!$this->si)
		{
			$this->si= new SiteInfo(get_bloginfo('name'),get_bloginfo('description'),'Wordpress '.get_bloginfo('version'));
		}
		
		return $this->si;
	}
	
	function getToken()
	{
		return get_option ( 'minerva_id' );
	}
}

class MinervaFriendsProvider implements IMinervaFriendsProvider  
{
	
	function loadItems()
	{
		$items=get_option('minerva_friends');
		if (!$items) $items=array('me'=>array(),'them'=>array());
		return $items;	
	}
	
	
	function saveItems($items)
	{
		update_option('minerva_friends',$items);
	}
	
}

class MinervaTokenProvider implements IMinervaTokenProvider   
{
	function loadItems()
	{
		return get_option('minerva_tokens');
	}
	 
	function saveItems($items)
	{
		update_option('minerva_tokens',$items);
	}
}

class MinervaNetworksProvider implements IMinervaNetworksProvider 
{
function loadItems()
	{
		return get_option('minerva_networks');
	}
	 
	function saveItems($items)
	{
		update_option('minerva_networks',$items);
	}
}
?>