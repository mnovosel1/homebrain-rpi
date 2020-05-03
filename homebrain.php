#!/usr/bin/php

<?php
require_once "functions.php";
echo DIR ."/classes/class.";
///////////////////////////////////////////////////////////////////////////////////////////////////
//* autoload CLASS definitions *///////////////////////////////////////////////////////////////////
spl_autoload_register(function ($class_name) {
	require_once DIR ."/classes/class.".$class_name.".php";
});


$name = getClassName($argv[1]);
$verb = strtolower($argv[2]);

if (count($argv) >= 3) {

	error_log("TEST error logging..1");
	if ( file_exists(DIR ."/classes/class.".$name.".php")  ) {

		error_log("TEST error logging..2");
		if ( method_exists($name, $verb) ) {

			error_log("TEST error logging..3");
			if (count($argv) == 3) {
				error_log("TEST error logging..3a");
				echo $name ."::". $verb ."()";
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."()");
				$ret = $name::$verb();
			}

			elseif (count($argv) == 4) {
				error_log("TEST error logging..3b");
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."(".$argv[3].")");
				$ret = $name::$verb(trim($argv[3]));
			}

			elseif (count($argv) == 5) {
				error_log("TEST error logging..3c");
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."(".$argv[3].", ".$argv[4].")");
				$ret = $name::$verb(trim($argv[3]), trim($argv[4]));
			}

			elseif (count($argv) == 6) {
				error_log("TEST error logging..3d");
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."(".$argv[3].", ".$argv[4].", ".$argv[5].")");
				$ret = $name::$verb(trim($argv[3]), trim($argv[4]), trim($argv[5]));
			}

			else {
				$ret = "RTFM";
			}
		} else {
			$ret = $name ."::". $verb ." doesn't exist.";
		}

	} else {
		$ret = $name ." class doesn't exist.";
	}

} else {
	$ret = "RTFM";
}

echo $ret .PHP_EOL;
?>