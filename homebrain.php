#!/usr/bin/php

<?php
require_once "functions.php";

///////////////////////////////////////////////////////////////////////////////////////////////////
//* autoload CLASS definitions *///////////////////////////////////////////////////////////////////
spl_autoload_register(function ($class_name) {
	error_log("Autoloading: "."/classes/class.".$class_name.".php");
	require_once DIR ."/classes/class.".$class_name.".php";
});


$name = getClassName($argv[1]);
$verb = strtolower($argv[2]);

if (count($argv) >= 3) {

	if ( file_exists(DIR ."/classes/class.".$name.".php")  ) {
		if ( method_exists($name, $verb) ) {

			if (count($argv) == 3) {
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."()");
				$ret = $name::$verb();
			}

			elseif (count($argv) == 4) {
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."(".$argv[3].")");
				$ret = $name::$verb(trim($argv[3]));
			}

			elseif (count($argv) == 5) {
				hbrain_log(__FILE__.":".__LINE__, $name ."::". $verb ."(".$argv[3].", ".$argv[4].")");
				$ret = $name::$verb(trim($argv[3]), trim($argv[4]));
			}

			elseif (count($argv) == 6) {
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