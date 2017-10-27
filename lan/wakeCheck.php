#!/usr/bin/php
<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('DIR', str_replace('/lan', '', dirname(__FILE__)));

$configs = parse_ini_file(DIR .'/config.ini');

$db      = new SQLite3(DIR .'/var/hbrain.db');

$sql = "SELECT name, active FROM states;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $table[$row['name']] = $row['active'];
}

// funkcija koja šalje notifikacije //////////////
function notify ($msg, $title = "HomeBrain")
{
	$db = new SQLite3(DIR .'/var/log.db');
	$db->query("INSERT INTO events VALUES(datetime('now', 'localtime'),'".$msg."'");
	
	exec(DIR . '/notify/fcm.php "' . $title . '" "' . $msg . '" &');
	exec(DIR . '/notify/kodi.php "' . $msg . '" &');
}
//////////////////////////////////////////////////

// HomeServer /////////////////////////////////////////////////////////////////////////////////////////
$serverlive = exec("ping -c1 10.10.10.100 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");

if ( $serverlive != $table["HomeServer"] )
{
	$db->query("UPDATE states SET active=".$serverlive." WHERE name='HomeServer'");
	$status = ($serverlive > 0) ? 'upaljen' : 'ugašen';
	notify('HomeServer je ' . $status . '.');	
	
	if ( $serverlive < 1 )
		$db->query("UPDATE states SET active=0 WHERE name='HomeServer busy'");
}

if ( $serverlive > 0 )
{
	$srvWakeTime = exec('/usr/bin/ssh server@10.10.10.100 "/home/server/chkServer"');
	exec('echo '.$srvWakeTime.' > '. DIR .'/var/srvWakeTime.log');
}
else
{
	$srvWakeTime = exec('cat '.DIR.'/var/srvWakeTime.log');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// KODI ///////////////////////////////////////////////////////////////////////////////////////////////
$kodilive = exec("ping -c1 10.10.10.20 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");

if ( $kodilive != $table["KODI"] )
{
	$db->query("UPDATE states SET active=".$kodilive." WHERE name='KODI'");
	
	$status = ($kodilive > 0) ? 'upaljen' : 'ugašen';
	notify('KODI je ' . $status . '.');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// HomeBrain user /////////////////////////////////////////////////////////////////////////////////////
$hbrainuser = exec("who | wc -l");
$hbrainuser = ($hbrainuser > 0) ? 1 : 0;

if ( $hbrainuser != $table["HomeBrain user"] )
{	
	$db->query("UPDATE states SET active=".$hbrainuser." WHERE name='HomeBrain user'");
	
	$status = ($hbrainuser > 0) ? 'prijavljen' : 'odjavljen';
	notify('HomeBrain user je ' . $status . '.');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// MPD player /////////////////////////////////////////////////////////////////////////////////////////
$mpdplay = exec("mpc status | grep playing");
$mpdplay = ($mpdplay == "") ? 0 : 1;

if ( $mpdplay != $table["MPD playing"] )
{	
	$db->query("UPDATE states SET active=".$mpdplay." WHERE name='MPD playing'");
	
	$status = ($mpdplay > 0) ? 'svira' : 'je ugašen';
	notify('MPD ' . $status . '.', 'MPD');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////




///////////////////////////////////////////////////////////////////////////////////////////////////////
// AKCIJE /////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT name, active FROM states;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
  $table[$row['name']] = $row['active'];

// server je ugašen
if ( $serverlive < 1 )
{
	// budi server ako ..
	switch (true)
	{
		case ( ($srvWakeTime - time()) <= 1800 ): // .. je srvWakeTime za pola sata ili manje
		case ( $kodilive > 0 ): // .. je KODI upaljen
			exec(DIR . "/lan/srvWake.sh;");
			notify('Palim HomeServer.', 'HomeServer');
		
		default:
			break;
	}
}
// server je upaljen
else
{
	// ne gasi server ako ..
	switch (true)
	{
		case ( $kodilive > 0 ): // .. je Kodi upaljen
		case ( $table["HomeServer busy"] > 0 ): // .. je HomeServer busy
		case ( $hbrainuser > 0 ): // .. HomeBrain ima usera
			break;
		
		default:
			exec(DIR . "/lan/srvShut.sh;");
			notify('Gasim HomeServer.', 'HomeServer');
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////

?>