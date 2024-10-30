<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */

/**
 * XmlRpc Server
 * 
 */
class MinervaXMLRpc
{
	/**
	 * Minerva user adds a new friend
	 *
	 * @param string $token
	 * User token
	 * @param string $url
	 * Site url
	 * @param string $name
	 * Site name
	 * @return string|false Minerva user's site name
	 * 
	 */
	function addFriend($token,$url,$name)
	{
		$tk= MinervaToken::create();
		$user=$tk->getUser($token,$url);
		if (!$user) return false;
		$fr= MinervaFriends::create();
		$fr->add($url,$name);
		$utils=MinervaRegistry::getSystemUtils()->getSiteInfo();
		return $utils->getName();
	}
	
	/**
	 * 
	 *
	 * @param string $url
	 * @return bool
	 */
	function isFriend($url)
	{
		$mf=  MinervaFriends::create();
		return $mf->isFriend($url);
	}
	
	/**
	 * Validate site's token
	 *
	 * @param string $token
	 * @return bool
	 */
	function isYourToken($token)
	{
		return (MinervaRegistry::getSystemUtils()->getToken()===$token);
	}
	/**
	 * Enter description here...
	 *
	 * @param string $token
	 * User token specific for this site
	 * @param string $url
	 * Site url
	 * @return bool
	 */
	function verifyToken($token,$url)
	{
		$tk= MinervaToken::create();
		$rez= $tk->getUser($token,$url);
		return ($rez!=false);
	}
	
	/**
	 * Notifies a former friend that he was removed  
	 *
	 * @param string $url
	 * @return bool
	 */
	function removedFriend($url)
	{
		$cmd= new MinervaTransport();
		$rez=$cmd->sendCommand($url,'isFriend',MinervaRegistry::getUrl());
		
		if ($rez===false)
		{
			$mf= MinervaFriends::create();
			$mf->removeBuddy($url);
			return true;
		}
		
		return false;
	}
	
	/**
	 *
	 * @param string $token
	 * @param string $url
	 * @return bool
	 */
	function sendToken($token,$url)
	{
		
		$tk= MinervaToken::create();
		$tk->saveVisitor($token,$url);
		return true;
	}
	
	/**
	 * Gets the user info for the MinervaUser
	 *
	 * @param string $token
	 * Unique user token specific to the site 
	 * @param string $url
	 * Site url
	 * @return array
	 */
	function userInfo($token,$url)
	{
		
		$h=MinervaToken::create();
		$id=$h->getUser($token,$url);
		
		if (!$id) return false;
		$u= MinervaRegistry::getSystemUtils()->getUserInfo($id);
		return $u->toArray();
	}
	
	/**
	 * Gets site info
	 *
	 * @return struct 
	 */
	function siteInfo()
	{
		return MinervaRegistry::getSystemUtils()->getSiteInfo()->toArray();
	}
	
	/**
	 * Gets members list for a network
	 *
	 * @param string $name Network name
	
	 * @return mixed
	 */
	function listMembers($name)
	{
	
		$mn= MinervaNetworks::create();
		return $mn->getUsers($name);
	}
	
/**
	 *
	 * @param string $name
	 * Network name
	 * @param string $owner_url Ignored here
	 * @param string $url
	 * @param string $url_name
	 * @param string $token
	 * @return bool
	 */
	function joinNetwork($name,$owner_url,$url,$url_name,$token)
	{
		//check url and token
		$cmd= new MinervaTransport();
		if (!$cmd->sendCommand($url,'isYourToken',$token))
		{
			return false;
		}
		
		//all ok
		$mn= MinervaNetworks::create();
		
		if ($mn->addMember($name,$url,$url_name,$token))
		{
			
			$det=$mn->getDetails($name);
			if (!$det) return false;
			
			//notify central new member
			$cmd->sendCommand(MinervaRegistry::MinervaCentral,'updateNetworkMembers',$name,MinervaRegistry::getUrl(),MinervaRegistry::getSystemUtils()->getToken(),$det['no_users']);
			return true;
		}
		return false;
	}
	/**
	 * Sent by the owner of the network 
	 *
	 * @param string $net
	 * Network name
	 * @param string $token
	 * User token
	 * @param string $url
	 * Url of the sender
	 * @return mixed
	 */
	function infoJoinNetwork($net,$token,$url)
	{
		if (!MinervaToken::create()->getUser($token,$url))
		{
			return null;
		}
		$mf=  MinervaNetworks::create();
		$mf->joinedNetwork($net,$url);
		
		return MinervaRegistry::getSystemUtils()->getSiteInfo()->toArray();
	}
		
	/**
	 *
	 * @param string $name
	 * @param string $owner_url ignored here
	 * @param string $url
	 * @param string $token
	 * @return bool
	 */
	function leaveNetwork($name,$owner_url,$url,$token)
	{
	//check url and token
		$cmd= new MinervaTransport();
		if (!$cmd->sendCommand($url,'isYourToken',$token))
		{
			return false;
		}
		$mn= MinervaNetworks::create();
		$mn->removeMember($name,$url);
		$det=$mn->getDetails($name);
		//notify central 
		$cmd->sendCommand(MinervaRegistry::MinervaCentral,'updateNetworkMembers',$name,MinervaRegistry::getUrl(),MinervaRegistry::getSystemUtils()->getToken(),$det['no_users']);
		return true;
	}
	
	/**
	 *
	 * @param string $name
	 * @param string $url
	 * @param string $token
	 * @return bool
	 */
	function kickedFromNetwork($name,$url,$token)
	{
		$cmd= new MinervaTransport();
		if (!$cmd->sendCommand($url,'isYourToken',$token))
		{
			return false;
		}
		$mn= MinervaNetworks::create();
		$mn->leftNetwork($name,$url);
		return true;
	}
}
?>