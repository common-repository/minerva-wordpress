<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
require_once dirname(__FILE__).'/abstract.php';
require_once dirname(__FILE__).'/exceptions.php';
require_once dirname(__FILE__).'/utils.php';
require_once dirname(__FILE__).'/transport.php';
require_once dirname(__FILE__).'/auth.php';
require_once dirname(__FILE__).'/workers.php';

require_once 'Zend/Registry.php';

class MinervaRegistry extends Zend_Registry 
{
	/**
	 * Local Url
	 *
	 * @return  string
	 */
	static public function getUrl()
	{
		return self::get('url');
	}
	
	const MinervaCentral='http://minerva.sapiensworks.com/central/serve.php';
	
	/**
	 * Minerva Version
	 *
	 * @var string
	 */
	const Version='1.0';
	/**
	 * Minerva Core directory
	 *
	 * @return  string
	 */
	/*static public function  getDirectory()
	{
		return self::get('dir');
	}*/

	
	/**
	 * @return  MinervaCookie
	 */
	static public function getCookie()
	{
		if(!self::isRegistered('cookie'))
		{
			return false;
		}
		return self::get('cookie');
	}
	
	/**
	 * 
	 *
	 * @return IMinervaSystemUtils
	 */
	static function getSystemUtils()
	{
		return self::get('system_utils');
	}
	
}

?>