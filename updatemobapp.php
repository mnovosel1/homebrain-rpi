#!/usr/bin/php
<?php

define('DIR', str_replace('/www/app', '', dirname(__FILE__)));
define('FILES_DAT', 'files.dat');

require_once(DIR . "/classes/class.Configs.php");
function debug_log($what) {
	ob_start();
	var_dump($what);
	$out = ob_get_clean();
	file_put_contents(DIR.'/'.Configs::get("DEBUG_LOG"), $out.PHP_EOL, FILE_APPEND);
}


global $file_info; // All the file paths will be pushed here
$file_info = array();

/**
 * 
 * @function recursive_scan
 * @description Recursively scans a folder and its child folders
 * @param $path :: Path of the folder/file
 * 
 * */

function recursive_scan ($path) {
    global $file_info;
    $path = rtrim($path, '/');

    if ( !is_dir($path) ) {
        if ( strpos($path, FILES_DAT) === false ) $file_info[] = str_replace (DIR . "/www/app/", "", $path);
    }

    else {
        $files = scandir($path);
        foreach ($files as $file) if($file != '.' && $file != '..') recursive_scan($path . '/' . $file);
    }
}

recursive_scan(DIR . '/www/app');

$newContent = "";
$thisFile = "";
$filesOldState = file_get_contents(DIR . "/www/app/" . FILES_DAT);
$filesNewState = "";
$filesUpdated = null;

$i = 0;
foreach ($file_info as $file) {
    $thisFile = $file . " " . filemtime (DIR . "/www/app/" . $file) . PHP_EOL;
    $i++;  
    if ( strpos($filesOldState, $thisFile) === false ) { 
        $filesUpdated[] = trim($file);
        echo $file . PHP_EOL;
    }
    if ( $filesUpdated !== null && $i%10 == 0 ) {
        $command = DIR . "/homebrain homebrain mobAppUpdate '{\"appupdates\":".json_encode($filesUpdated, JSON_UNESCAPED_SLASHES)."}'";
        exec($command);
        $filesUpdated = null;
        sleep(2);
    }
    $filesNewState .= $thisFile;
}
if ( $filesUpdated !== null ) {
    $command = DIR . "/homebrain homebrain mobAppUpdate '{\"appupdates\":'".json_encode($filesUpdated, JSON_UNESCAPED_SLASHES)."'}'";
    exec($command);
    $filesUpdated = null;
}

file_put_contents(DIR . "/www/app/" . FILES_DAT, $filesNewState);

?>