<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//* CONSTANTs *////////////////////////////////////////////////////////////////////////////////////
define('DIR', str_replace('/classes', '', dirname(__FILE__)));

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
//* tsinking *//////////////////////////////////////////////////////////////////////////////////////
function think($what) {
	$what = trim($what);
	$lastLine = exec("tail -n 1 ". DIR ."/". Configs::get("HOMEBRAIN", "THINK"));
    exec("tail -n 4 ". DIR ."/". Configs::get("HOMEBRAIN", "THINK"), $lastFewLines);


	if (strpos($lastLine, $what) !== false) {
		$what = "I'm doing the same thing over and over again... :-/";
	}
	else if (strpos(implode(" ", $lastFewLines), $what) !== false) {
		if (strpos(implode(" ", $lastFewLines), "That's news, right") === false) {
			$what = $what ." That's news, right.";
		}
	}

	file_put_contents(DIR ."/". Configs::get("HOMEBRAIN", "THINK"), date("H:i") ." ". $what .PHP_EOL, FILE_APPEND);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* DEBUGGING stuff *//////////////////////////////////////////////////////////////////////////////

function debug_log($where, $what) {
	if ( Configs::get("DEBUG") == "true" ) {
		write_log($where, $what, "DEBUG");
	}
}

function hbrain_log($where, $what) {
        write_log($where, $what);
}

function write_log ($where, $what, $whichLog = "INFO") {
        if (strpos($where, '::') !== false)
                $class = explode('::', $where)[0];
        else
                $class = explode('.', $where)[1];

	ob_start();
	echo date("[d.m.y. H:i:s] ");
	echo $_SERVER['REMOTE_ADDR'];
	echo " [".$whichLog."] ";
	echo $where." > ";
	var_dump($what);
	$out = ob_get_clean();

	file_put_contents(DIR ."/". Configs::get("HOMEBRAIN", "LOG"), $out, FILE_APPEND);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* RUN *//////////////////////////////////////////////////////////////////////////////////////////
try {
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	$ret = trim($API->processAPI());

	// if ( !$ret ) header('HTTP/1.0 403 Forbidden');
	if ( !empty($ret) ) echo $ret . PHP_EOL;
}

catch (Exception $e) {
	echo var_export($e->getMessage(), true);
}
///////////////////////////////////////////////////////////////////////////////////////////////////

?>
