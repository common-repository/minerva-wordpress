<?php
/**
 	This source file is part of Minerva Core
    Copyright (C) 2008  Mihai Mogosanu (aka Mike T) http://minerva.sapiensworks.com

    This source file is subject to the GPL license that is bundled
    with this package in the file license.txt.

    You should have received a copy of the GNU General Public License
    along with it.  If not, see <http://www.gnu.org/licenses/>.
 
 */
require_once 'Zend/XmlRpc/Client.php';
require_once 'webservice.php';

class MinervaTransport 
{
	
	function sendCommand($destination,$command,$params=array())
	{
		require_once 'Zend/XmlRpc/Client.php';

		$destination=rtrim($destination,'/').'/';
		$client= new Zend_XmlRpc_Client($destination);
		$client->getHttpClient()->setHeaders('Minerva-Transport','default');
		if (func_num_args()>3)
		{
			$params=array_slice(func_get_args(),2);
			
		}
		elseif (!is_array($params))
		{
			$params=array($params);
		}
		if (stripos($command,'.')===false)
		{
			$command='minerva.'.$command;
		}
		$rez=NULL;
		try
		{
			
		$rez=$client->call($command,$params);
	
		}
		catch (Zend_XmlRpc_Client_FaultException  $ex)
		{
			if (MinervaDebug)
			{
				echo '<pre>';
				var_export($client->getHttpClient()->getLastResponse()->getBody());
				var_export($ex);
				echo '</pre>';
				
			}
		
		}
		return $rez; 
		
	}
	function receive()
	{
		require_once 'Zend/XmlRpc/Server.php';
		$server= new Zend_XmlRpc_Server();
		$server->setClass('MinervaXMLRpc','minerva');
		echo $server->handle();	
	}
}
	


?>