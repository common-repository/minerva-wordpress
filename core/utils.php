<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */
class MinervaUtils
{
	/**
	 * Enter description here...
	 *
	 * @param string $url
	 * @return bool
	 */
	static function isUrl($url='')
	{
		require_once 'Zend/Uri.php';
		return Zend_Uri::check($url);
	}
	
	static function loadProvider($name)
	{
	$di=MinervaRegistry::get('di');
		$class=$di[$name];
		if (!$class) throw new InvalidProviderException("No $name provider");
		$class= new ReflectionClass($class);
		return $class->newInstance();	
	}
}
?>