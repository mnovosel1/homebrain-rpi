<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//* CONSTANTs *////////////////////////////////////////////////////////////////////////////////////
define('DEBUG', true);
define('DIR', str_replace('/www/api', '', dirname(__FILE__)));

///////////////////////////////////////////////////////////////////////////////////////////////////
//* DEBUGGING stuff *//////////////////////////////////////////////////////////////////////////////
if ( DEBUG ) error_reporting(E_ALL);
else error_reporting(E_ALL & ~E_NOTICE);

function debug_log($what) {
	if ( !DEBUG ) return;
	ob_start();
	var_dump($what);
	$out = ob_get_clean();
	file_put_contents(DIR.'/'.Configs::get("DEBUG_LOG"), $out.PHP_EOL, FILE_APPEND);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* autoload CLASS definitions *///////////////////////////////////////////////////////////////////
spl_autoload_register(function ($class_name) {
	require_once DIR . "/classes/class.".$class_name.".php";
});


///////////////////////////////////////////////////////////////////////////////////////////////////
//* Requests from the same server don't have a HTTP_ORIGIN header *////////////////////////////////
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}


///////////////////////////////////////////////////////////////////////////////////////////////////
//* RUN *//////////////////////////////////////////////////////////////////////////////////////////
try {
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	$ret = $API->processAPI();
	if ( $ret != "null") echo $ret . PHP_EOL;
}

catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
///////////////////////////////////////////////////////////////////////////////////////////////////

?>