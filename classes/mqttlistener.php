#!/usr/bin/php

<?php

require_once "helpers/functions.php";

///////////////////////////////////////////////////////////////////////////////////////////////////
//* MQTT */////////////////////////////////////////////////////////////////////////////////////////
require("helpers/phpMQTT.php");

$server = "hassio";
$port = 1883;
$username = "";
$password = "";
$client_id = "phpMQTT-hbrain";

$mqtt = new phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, $username, $password)) {
	exit(1);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* CONSTANTs *////////////////////////////////////////////////////////////////////////////////////
define('DIR', str_replace('/classes', '', dirname(__FILE__)));

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
    echo "Status: ". $obj ." ". $stat .PHP_EOL;
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
