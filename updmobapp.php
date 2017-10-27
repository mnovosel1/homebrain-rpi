#!/usr/bin/php
<?php

define('DIR', str_replace('/www/app', '', dirname(__FILE__)));
define('FILES_DAT', 'files.dat');

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
$filesUpdated = false;

foreach ($file_info as $file) {

    $thisFile = $file . " " . filemtime (DIR . "/www/app/" . $file) . PHP_EOL;    
    if ( strpos($filesOldState, $thisFile) === false ) $filesUpdated[] = trim($file);
    $filesNewState .= $thisFile;
}

if ( !$filesUpdated )
    echo "No updates.." . PHP_EOL;
else {

    echo "Updating:" . PHP_EOL;
    foreach ($filesUpdated as $fileU) {
        echo "   " . $fileU . " ";
    }
    echo PHP_EOL . PHP_EOL;

    $command = DIR . "/notify/fcmupdateapp.php " . '\'{"updates":' . json_encode($filesUpdated) . "}'";
    exec($command);

    //var_dump(exec($command));
}

file_put_contents(DIR . "/www/app/" . FILES_DAT, $filesNewState);

?>