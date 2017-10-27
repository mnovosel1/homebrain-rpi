<?php
///////////////////////////////////////////////////////////////////
/* WORKING DIR constant *//////////////////////////////////////////

define('DIR', str_replace('/www/api', '', dirname(__FILE__)));

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
/* CLASS definition *//////////////////////////////////////////////

require_once DIR . "/www/api/class.api.php";

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
/* METHOD definitions *////////////////////////////////////////////

require_once DIR . "/www/api/inc.getinfo.php";
require_once DIR . "/www/api/inc.update.php";
require_once DIR . "/www/api/inc.fcm.php";
require_once DIR . "/www/api/inc.hsrv.php";
require_once DIR . "/www/api/inc.amp.php";
require_once DIR . "/www/api/inc.kodi.php";
require_once DIR . "/www/api/inc.mpd.php";

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
/* USER CLASS definition */////////////////////////////////////////

class MyAPI extends API
{
    public function __construct($request, $origin)
	{	
        parent::__construct($request);
    }
	
    public function __call($name, $arguments)
	{
/*
		ob_start();
		
		echo "name: " . PHP_EOL;
		var_dump($name);
		
		echo "verb: " . PHP_EOL;
		var_dump($this->verb);

		echo "POST: " . PHP_EOL;
		var_dump($_POST);

		$out = ob_get_clean();
		file_put_contents('newapiDbg.txt', $out . PHP_EOL . PHP_EOL, FILE_APPEND);
*/

        if ( function_exists( $name ) )
			return $name($this->verb, $arguments);
		else
			return false;
    }	
}

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
/* Requests from the same server don't have a HTTP_ORIGIN header */

if (!array_key_exists('HTTP_ORIGIN', $_SERVER))
{
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

///////////////////////////////////////////////////////////////////


try
{
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	echo $API->processAPI();
}
catch (Exception $e)
{
	echo json_encode(Array('error' => $e->getMessage()));
}

?>