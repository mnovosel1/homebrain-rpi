<?php

$reqtime = floor($_SERVER["REQUEST_TIME"]/20);

/*
ob_start();
echo $_SERVER[REQUEST_URI] . PHP_EOL . PHP_EOL;

var_dump($_POST);
echo 'H' . $reqtime . ": " . md5('H' . $reqtime) . PHP_EOL;
echo 'o' . $reqtime . ": " . md5('o' . $reqtime) . PHP_EOL;
echo 'm' . $reqtime . ": " . md5('m' . $reqtime) . PHP_EOL;
echo 'e' . $reqtime . ": " . md5('e' . $reqtime) . PHP_EOL;
echo 'B' . $reqtime . ": " . md5('B' . $reqtime) . PHP_EOL;
echo 'r' . $reqtime . ": " . md5('r' . $reqtime) . PHP_EOL;
echo 'a' . $reqtime . ": " . md5('a' . $reqtime) . PHP_EOL;
echo 'i' . $reqtime . ": " . md5('i' . $reqtime) . PHP_EOL;
echo 'n' . $reqtime . ": " . md5('n' . $reqtime) . PHP_EOL;
$out = ob_get_clean();
file_put_contents('debug.txt', $out . PHP_EOL . PHP_EOL, FILE_APPEND);


if ( !isset($_POST["secToken"]) ) header('HTTP/1.0 403 Forbidden');

switch (true) 
{
	case ( $_POST['secToken'] == md5('H' . $reqtime) ):
	case ( $_POST['secToken'] == md5('o' . $reqtime) ):
	case ( $_POST['secToken'] == md5('m' . $reqtime) ):
	case ( $_POST['secToken'] == md5('e' . $reqtime) ):
	case ( $_POST['secToken'] == md5('B' . $reqtime) ):
	case ( $_POST['secToken'] == md5('r' . $reqtime) ):
	case ( $_POST['secToken'] == md5('a' . $reqtime) ):
	case ( $_POST['secToken'] == md5('i' . $reqtime) ):
	case ( $_POST['secToken'] == md5('n' . $reqtime) ):
	break;
	
	default: header('HTTP/1.0 403 Forbidden');
}
*/

?>