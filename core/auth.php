<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */
class MinervaAuth
{
	static function validateSession(MinervaCookie $ck)
	{
		if ($ck->isLocal()) return true;
		
		$cmd= new MinervaTransport();
		$f=	$cmd->sendCommand($ck->getUrl(),'verifyToken',$ck->getToken(),MinervaRegistry::getUrl());
		return $f;		
		
	}
	
	static function Redirect($url)
	{
		
		if (!MinervaUtils::isUrl($url)) return;

		if (MinervaRegistry::getUrl()==$url) 
		{
			header('Location: '.$_SERVER['SCRIPT_NAME']);
			die();
		}
		$token= MinervaToken::create();
		
		
		//get token for the url
		$tk=$token->generate($url);
		$cmd= new MinervaTransport();
		
		//send it to the destination url
		$rez=$cmd->sendCommand($url,'sendToken',$tk,MinervaRegistry::getUrl());
		if ($rez===true)
		{
		//redirect to destination
		$url=$url.'?minerva='.$tk; 
		header('Location: '.$url);
		die();
		}
		
		
	}
	
	/**
	 * Second part of authentication for visitor from network
	 * Generates cookie for the visitor (who's already logged in on his blog)
	 * @param string $token
	 * 
	 */
	static function Authorize($token)
	{
		if (!MinervaRegistry::getCookie())
		{
		//get visitor token (issued by his blog) from db
		$tk=  MinervaToken::create();
		if(!$tk->authorize($token))
		{
			return;
		}
		}
		header('Location: '.MinervaRegistry::getUrl());
		die();
	}
}



class MinervaCookie
{
	
	private $name;
	private $url;
	private $token;
	private $expires;
	private $valid=false;
	private $local=false;
	
	function __construct($name=null,$url=null,$token=null,$expires=null)
	{
		$this->name=$name;
		$this->url=$url;
		$this->token=$token;
		
		if (is_null($expires))
		{
			$this->expires=time()+172800;
		}
		else
		{
			$this->expires=$expires;
		}
		$this->valid=($expires>time());
		if ($url==MinervaRegistry::getUrl()) $this->local=true; 
	}
	
	/**
	 * @return string
	 *
	 */
	function encrypt()
	{
		$ar=implode('|',array($this->name,$this->url,$this->token,$this->expires));
		return base64_encode($ar.'`'.self::createHmac($ar));
	}
	
	
	private static function createHmac($text)
	{
		$utils= MinervaRegistry::getSystemUtils();
		return hash_hmac('sha1',$text,$utils->getSalt());
	}
	
	/**
	 * 
	 *
	 * @param string $cookie
	 * @return MinervaCookie | false
	 */
	static function decrypt($cookie=null)
	{
		$temp=base64_decode($cookie);
		if ($temp===false) return false;
		list($data,$hmac)=explode('`',$temp);
		if (self::createHmac($data)!=$hmac) return false;
		list($n,$u,$t,$e)=explode('|',$data);
		return new MinervaCookie($n,$u,$t,$e);
	}
	
	static function delete()
	{
		$utils=MinervaRegistry::getSystemUtils();
		$utils->setCookie('minerva',null,time()-31536000); 
	}
	
	/**
	 * Gets the unique user token valid for the session
	 * @return string
	 */
	function getToken()
	{
		return $this->token;
	}
	
	/**
	 * Gets the url of the user
	 * @example http://www.myblog.com
	 *@return string
	 */
	function getUrl()
	{
		return $this->url;
	}
	
	/**
	 * Gets the nice name of the user
	 *@return string
	 */
	function getName()
	{
		return $this->name;
	}
	
	/**
	 * Is cookie valid
	 *
	 * @return bool
	 */
	function isValid()
	{
		return $this->valid;
	}
	
	/**
	 * @return bool
	 *
	 */
	function isLocal()
	{
		return $this->local;
	}
	
	
	
	/**
	 * Sets the browser cookie
	 *
	 */
	function set()
	{
		$utils= MinervaRegistry::getSystemUtils();
		
		$utils->setCookie('minerva',$this->encrypt(),$this->expires);
	}
}
?>