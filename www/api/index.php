<?php

require('config.php');

if ( strpos($_SERVER['REMOTE_ADDR'], ALLOWED_SUBNET) === false )
{
	header('HTTP/1.0 403 Forbidden');
	exit();
}


echo "OK!";

