<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//* CONSTANTs *////////////////////////////////////////////////////////////////////////////////////
define('DIR', str_replace('/classes', '', dirname(__FILE__)));

///////////////////////////////////////////////////////////////////////////////////////////////////
//* DEBUGGING stuff *//////////////////////////////////////////////////////////////////////////////

function debug_log($where, $what) {

        if (strpos($where, '::') !== false)
                $class = explode('::', $where)[0];
        else
                $class = explode('.', $where)[1];

	if ( !$class::$debug ) return;
	hbrain_log($where, $what, "DEBUG");
}

function hbrain_log($where, $what, $logLevel = "LOG") {

        if (strpos($where, '::') !== false)
                $class = explode('::', $where)[0];
        else
                $class = explode('.', $where)[1];

	ob_start();
	echo date("[d.m.y. H:i:s] ");
	echo $_SERVER['REMOTE_ADDR'];
	echo " [".$logLevel."] ";
	echo $where." > ";
	var_dump($what);
	$out = ob_get_clean();

	file_put_contents(DIR.'/var/'.Configs::get("LOG"), $out, FILE_APPEND);
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
	$ret = trim($API->processAPI());
	
	// if ( !$ret ) header('HTTP/1.0 403 Forbidden');
	if ( $ret != "null") echo $ret . PHP_EOL;
}

catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
///////////////////////////////////////////////////////////////////////////////////////////////////

?>
