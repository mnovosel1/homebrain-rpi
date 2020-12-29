#!/usr/bin/php

<?php
require_once "functions.php";

///////////////////////////////////////////////////////////////////////////////////////////////////
//* MQTT */////////////////////////////////////////////////////////////////////////////////////////
require(DIR ."/helpers/phpMQTT.php");

$server = "10.10.10.13";
$port = 1883;
$username = "hassio";
$password = "sonopass";
$client_id = "phpMQTT-hbrain";

$mqtt = new phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, $username, $password)) {
    echo "Connect to $server mqtt unsucessful.";
	exit(1);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* autoload CLASS definitions *///////////////////////////////////////////////////////////////////
spl_autoload_register(function ($class_name) {
	include_once DIR . "/classes/class.".$class_name.".php";
});

$topics['hbrain/#'] = array("qos" => 0, "function" => "procmsg");
$mqtt->subscribe($topics, 0);

while($mqtt->proc()) {
}

$mqtt->close();

function procmsg($topic, $msg) {

    if (strstr($topic, "hbrain/stat")) {
        status(explode("/", $topic, 4)[2], $msg);
    }

    else if (strstr($topic, "hbrain/cmnd")) {
        cmnd(explode("/", $topic, 4)[2], $msg);
    }

    else {
        echo "Unhandled topic: ". $topic .PHP_EOL;
    }
}

function status($obj, $stat) {

    return;
    
    $states = include(DIR ."/var/objStates.php");
    $states[$obj] = $stat;

    debug_log(__FILE__.":".__LINE__, "Status: ". $obj ." ". $stat);

    echo "Status: ". $obj ." ". $stat .PHP_EOL;

    $statesWrite = '<?php'. PHP_EOL .'return ['. PHP_EOL;
    foreach ($states as $key => $state) {
        $statesWrite .= "    '". $key ."' => '". $state ."', ". PHP_EOL;
    }
    $statesWrite = substr($statesWrite, 0, -3);
    $statesWrite .= PHP_EOL .'];'. PHP_EOL .'?>'. PHP_EOL;

    if (count($states) > 1) file_put_contents(DIR ."/var/objStates.php", $statesWrite);
}

function cmnd($obj, $cmnd) {

    $cmnd = explode(" ", $cmnd);
    $name = getClassName($obj);
    $verb = $cmnd[0];

    if ( !class_exists($name) ) {        
        write_log("MQTT", $topic ." '". implode(" ", $cmnd) ."' No Class named '". $name ."'", "ERROR");
    }
    
    else if ( !method_exists($name, $verb) ) {
        write_log("MQTT", $topic ." '". implode(" ", $cmnd) ."' No Method '". $name ."::". $verb ."()'", "ERROR");
    }

    else {
        switch (count($cmnd)) {

            case 1:
                $logentry = $name ."::". $verb ."()";
                $name::$verb();
            break;

            case 2:
                $logentry = $name ."::". $verb ."('".$cmnd[1]."')";
                $name::$verb($cmnd[1]);
            break;

            case 3:
                $logentry = $name ."::". $verb ."('".$cmnd[1]."','".$cmnd[2]."')";
                $name::$verb($cmnd[1], $cmnd[2]);
            break;

            case 4:
                $logentry = $name ."::". $verb ."('".$cmnd[1]."','".$cmnd[2]."','".$cmnd[3]."')";
                $name::$verb($cmnd[1], $cmnd[2], $cmnd[3]);
            break;

            default:
                $logentry = " NOK ";
        }
        $logentry = $topic ." '". implode(" ", $cmnd) ."' => ". $logentry;
        write_log("MQTT", $logentry, "INFO");
    }
}
