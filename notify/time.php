#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/heating', '', dirname(__FILE__));

$time = "It's ";

if ( date("i") > 0 ) {
	$time .= date("i")*1 . " minutes past ";
}

$time .= date("H") . " o clock";

exec($path .'/notify/speak "' . $time . '"');

?>
