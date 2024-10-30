<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */

class UserInfo
{
	private $items;
	
	function __construct($name)
	{
		$this->items['name']=$name;
	}
	
	/**
	 * Display name
	 *
	 * @return string
	 */
	function getName()
	{
		return $this->items['name'];
	}
	
	/**
	 * User info as an array
	 *
	 * @return array array(name)
	 */
	function toArray()
	{
		return $this->items;
	}
}

class SiteInfo
{
	private $items;
	
	function __construct($name,$description,$host)
	{
		$this->items['name']=$name;
		//$this->items['description']=$description;
		//$this->items['host']=$host;
		$this->items['token']=MinervaRegistry::getSystemUtils()->getToken();
	}
	
	/**
	 * @return string
	 *
	 */
	function getName()
	{
		return $this->items['name'];
	}
	
	/**
	 * @return string
	 *
	 */
	/*function getDescription()
	{
		return $this->items['description'];
	}*/
	
	/**
	 * Gets the host application name i.e: Wordpress 2.5.1
	 * @return string
	 *
	 */
	/*function getHost()
	{
		return $this->items['host'];
	}*/
	
	
	function toArray()
	{
		return $this->items;
	}
}

class MinervaNetworks
{
	private $prov;

	
	private $nets=array('mine'=>array(),'part_of'=>array());
	
	private function __construct(IMinervaNetworksProvider $p)
	{		$this->prov=$p;
		
		$n=$this->prov->loadItems();
		if ($n)
		{
			$this->nets=&$n;
		}
	}
	
	static function joinNetwork($name)
	{
		 $myn= MinervaNetworks::create();
		 if (!$myn->networkExists($name)) return false;
		 $cmd= new MinervaTransport();
		 $url= MinervaRegistry::getCookie()->getUrl();
		 if (!$url) return false;
		 
		 $info=$cmd->sendCommand($url,'infoJoinNetwork',$name,MinervaRegistry::getCookie()->getToken(),MinervaRegistry::getUrl());
		 if (!$info) return false;
		 $myn->addMember($name,$url,$info['name'],$info['token']);
		 
		 //notify central
		 $cmd->sendCommand(MinervaRegistry::MinervaCentral,'updateNetworkMembers',$name,MinervaRegistry::getUrl(),MinervaRegistry::getSystemUtils()->getToken(),$myn->countMembers($name));
		 return true;
		 
	}
	
	function getMyNetworks()
	{
		return $this->nets['mine'];
	}
	
	/**
	 *
	 * @return MinervaNetworks
	 */
	static function create()
	{
		$n=MinervaUtils::loadProvider('networks');
	
		$m= new MinervaNetworks($n);
		return $m;
		
	}
	
	function getDetails($net)
	{
		if (!$this->networkExists($net)) return false;
		$r=	$this->nets['mine'][$net];
		unset($r['users']);
		return $r;
	}
	
	/**
	 *
	 * @param string $network
	 * @return bool
	 */
	function isMemberOf($network)
	{
		if (!MinervaRegistry::getCookie()) return false;
		if (!isset($this->nets['mine'][$network])) return false;
		foreach ($this->nets['mine'][$network]['users'] as $user)
		{
			if ($user['url']==MinervaRegistry::getCookie()->getUrl()) return true;
		}
		
		return false;
	}
	
		
	function isPartOf($net,$url)
	{
		return isset($this->nets['part_of'][$url]);
	}
	
	private function networkExists($net)
	{
		return isset($this->nets['mine'][$net]);
	}
	
	function addMember($network,$url,$name,$token)
	{
		if ($this->isOwner($url) || !$this->networkExists($network)) return false;
		$user['name']=$name;
		$user['url']=$url;
		$user['token']=$token;
		$user['registered_on']=date('Y-M-d');
		$user['owner']=false;
		$this->nets['mine'][$network]['users'][]=$user;
		$this->nets['mine'][$network]['no_users']++;
		return true;
	}
	
	function countMembers($network)
	{
		if (!$this->networkExists($network)) return false;
		return $this->nets['mine'][$network]['no_users'];
	}
	
	function removeMember($network,$url)
	{
		if ($this->isOwner($url)) return;
		$key=null;
		foreach ($this->nets['mine'][$network]['users'] as $k=>$user)
		{
			if ($user['url']==$url)
			{
				$key=$k;
				break;
			}
		}	
		if ($key)
		{
			unset($this->nets['mine'][$network]['users'][$key]);
			$this->nets['mine'][$network]['no_users']--;
		}
	}
	
	/**
	 *
	 * @return array(url=>name)
	 */
	function getJoinedNetworks()
	{
		return $this->nets['part_of'];
	}
	
	function joinedNetwork($name,$owner)
	{
		
		$this->nets['part_of'][$owner]=$name;
	}
	
	function leftNetwork($name,$owner)
	{
		unset($this->nets['part_of'][$owner]);
	}
	
	/**
	 * @return bool
	 */
	function isOwner($url)
	{
		
		return $url==MinervaRegistry::getUrl();
	}
	
	function add($name,$descr)
	{
		if (isset($this->nets['mine'][$name])) return false;
		if (empty($name)) return false;
		$nets['descr']=$descr;
		$nets['name']=$name;
		$si= MinervaRegistry::getSystemUtils()->getSiteInfo();
		$nets['no_users']=1;
		$nets['users'][0]['name']=$si->getName();
		$nets['users'][0]['token']=MinervaRegistry::getSystemUtils()->getToken();
		$nets['users'][0]['url']=MinervaRegistry::getUrl();
		$nets['users'][0]['registered_on']=date('Y-M-d ');
		$nets['users'][0]['owner']=true;
		$nets['created_on']=date('Y-M-d');
		$this->nets['mine'][$name]=$nets;
		return true;
	}
	
	function update($old,$name,$descr)
	{
		
		if (($old!=$name) && isset($this->nets['mine'][$name])) return false;
		if (empty($old) || empty($name)) return false;
		$net=$this->nets['mine'][$old];
		unset($this->nets['mine'][$old]);
		$net['descr']=$descr;
		$net['name']=$name;
		$this->nets['mine'][$name]=$net;
		return true;
	}
	
	function delete($name)
	{
		unset($this->nets['mine'][$name]);
	}
	
	function getUsers($name)
	{
		if (!isset($this->nets['mine'][$name])) return false;
		return $this->nets['mine'][$name]['users'];
	}
	
	function saveChanges()
	{
		$this->prov->saveItems($this->nets);
	}
	
	function __destruct()
	{
		$this->saveChanges();
	}
	
}

/*class MinervaNetwork
{
	private $name;
	private $description;
	
	private $new=true;
	
	*//**
	 * @return string
	 *//*
	public function getDescription() {
		return $this->description;
	}
	
	*//**
	 * @return string
	 *//*
	public function getName() {
		return $this->name;
	}
	
	*//**
	 * @param string $description
	 *//*
	public function setDescription($description) {
		$this->description = $description;
	}
	
	*//**
	 * @param string $name
	 *//*
	public function setName($name) {
		$this->name = $name;
	}
	
	private $users=array();
	function __construct($data)
	{
		if (is_array($data))
		{
			$this->name=$data['name'];
			$this->description=$data['descr'];
			$this->users=$data['members'];
			$this->new=false;
		}
	}
	
		
	function getMembers()
	{
		return $this->users;
	}
}*/

class MinervaToken
{
	/**
	 * Enter description here...
	 *
	 * @var IMinervaTokenProvider
	 */
	private $prov;
	
	function __construct(IMinervaTokenProvider $p)
	{
		$this->prov=$p;
	}
	
	/**
	 * 
	 *@return MinervaToken
	 */
	static function create()
	{
		$mt= new MinervaToken(MinervaUtils::loadProvider('token'));
		return $mt;
	}
	
	//save token + url
	function generate($url)
	{
		$utls= MinervaRegistry::getSystemUtils();
		$user=$utls->getCurrentUserId();
		$token=sha1(uniqid($url,true));
		$this->saveLocalToken($user,$token,$url);
		return $token;
	}
	
	protected function saveLocalToken($user,$token,$url)
	{
		
		$tokens=$this->prov->loadItems();
		
		$tokens[$user][$url]=$token;
		
		$this->prov->saveItems($tokens);
	}
	
	function getUser($token,$url)
	{
		
		$tokens=$this->prov->loadItems();
		foreach($tokens as $user=>$data)
		{
			if (isset($data[$url]) && $data[$url]==$token) return $user;
		}
		return false;
	}
	
	/**
	 * 
	 *
	 * @param string $url
	 * Remote url
	 * @param int $user
	 * Local user id
	 * @return false
	 */
	function getTokenForUrl($url,$user)
	{
		
		$tokens=$this->prov->loadItems();
		if (!isset($tokens[$user][$url])) return false;
		return $tokens[$user][$url];
		
		
	}
	
	function cleanUser($user)
	{
		$tokens=$this->prov->loadItems();
		unset($tokens[$user]);
		$this->prov->saveItems($tokens);
	}
	
	function saveVisitor($token,$url)
	{
		
		$tokens=$this->prov->loadItems();
		$tokens[$token]=$url;
		$this->prov->saveItems($tokens);
	}
	
	function getVisitor($token)
	{
		$tokens=$this->prov->loadItems();
		if (isset($tokens[$token])) return $tokens[$token];
		return false; 
	}
	
	function cleanVisitor($token)
	{
		$tokens=$this->prov->loadItems();
		unset($tokens[$token]);
		$this->prov->saveItems($tokens);
	}	

	function authorize($token)
	{
		$rez=false;
		
		$url=$this->getVisitor($token);
		
		if ($url)
		{
			$cmd= new MinervaTransport();
			$info=$cmd->sendCommand($url,'userInfo',$token,MinervaRegistry::getUrl());
					
			if (is_array($info))
			{
				$this->cookie= new MinervaCookie($info['name'],$url,$token);
				$this->cookie->set();
				$this->cleanVisitor($token);
				$rez=true;
			}
			else
			{
				$rez=false;
			}
			
			
			
		}
		return $rez;
	}
}	


class MinervaFriends
{
	protected  $items;
	/**
	 *
	 * @var IMinervaFriendsProvider
	 */
	private $provider;
	
	private function __construct(IMinervaFriendsProvider $p)
	{
		$this->provider=$p;
		$this->items=$this->provider->loadItems();
	}
	
	/**
	 * @return MinervaFriends
	 *
	 */
	static function create()
	{
		$mf= new MinervaFriends(MinervaUtils::loadProvider('friends'));
		return $mf;
	}
	
	function canAdd(MinervaCookie $ck)
	{
		if ($ck->isLocal()) return false;
		return !isset($this->items['them'][$ck->getUrl()]);
	}
	
	function add($url,$name)
	{
		$this->items['me'][$url]=$name;	
	}
	
	function removeBuddy($url)
	{
		unset($this->items['them'][$url]);
	}
	
	function remove($url)
	{
		unset($this->items['me'][$url]);
	}
	
	function wasAdded($url,$name)
	{
		$this->items['them'][$url]=$name;
	}
	
	/**
	 * 
	 *
	 * @param string $url
	 * @return bool
	 */
	function isFriend($url)
	{
		return isset($this->items['me'][$url]);
	}
	
	function amIFriendOf($url)
	{
		return isset($this->items['them'][$url]);
	}
	
	function getMine()
	{
		return $this->items['me'];
	}
	
	function getBuddies()
	{
		return $this->items['them'];
	}

	function saveItems()
	{
		$this->provider->saveItems($this->items);
	}
	
	function __destruct()
	{
		$this->provider->saveItems($this->items);
	}
	
	static function addFriend()
	{
		$cmd= new MinervaTransport();	
			$utils= MinervaRegistry::getSystemUtils()->getSiteInfo();
			$rez=$cmd->sendCommand(MinervaRegistry::getCookie()->getUrl(),'addFriend',MinervaRegistry::getCookie()->getToken(),MinervaRegistry::getUrl(),$utils->getName());
				if ($rez)
				{
					$local= MinervaFriends::create();
					$local->wasAdded(MinervaRegistry::getCookie()->getUrl(),$rez);
					unset($local);
					return true;
				}
				return false;
	}
}

?>