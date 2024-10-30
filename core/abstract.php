<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */
interface IMinervaSystemUtils
{
	/**
	 * Sets a browser cookie
	 *
	 * @param string $name
	 * @param string $value
	 * @param integer $expire
	 */ 
	function setCookie($name,$value=null,$expire=null);
	 
	/**
	 * Gets the site's specific salt 
	 * @return string
	 */
	function getSalt(); 
	 
	  /**
	  * 
	  * @return int
	  */
	 function getCurrentUserId();
	 /**
	  * In the future more user info might be added
	  *
	  * @param int $user
	  * @return UserInfo
	  */
	 function getUserInfo($user);
	 
	 /**
	 * Site's unique token, used to identify the site 
	 *
	 * @return string
	 */
	public function  getToken();	
	/**
	 * @return SiteInfo
	 *
	 */
	function getSiteInfo();
}

interface IMinervaProvider
{
	function loadItems();
	function saveItems($items);
}

interface IMinervaFriendsProvider extends IMinervaProvider 
{
	
}

interface IMinervaTokenProvider extends IMinervaProvider 
{
	
}

interface IMinervaNetworksProvider extends IMinervaProvider 
{
	
}

	

?>