#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//exit();

$path = str_replace('/heating', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');

$sendEmail  = false;
$updateDb   = false;

$db         = new SQLite3($path .'/var/lan.db');
$sql = "SELECT COUNT(DISTINCT lanDevices.name) AS num
        FROM lanDevices, lanLog 
        WHERE lanDevices.mac=lanlog.mac
          AND lanDevices.heating=1
          AND (lanLog.stop IS NULL OR lanLog.stop > datetime(datetime('now', 'localtime'), '-15 minutes'));";
$anybodyhome = ( date("H") >= 23 || date("H") <= 5 ) ? 1 : $db->querySingle($sql);

$heatingState       = array("ugašeno", "upaljeno");
$subjectTPL         = "Grijanje";

$last_temps     = file_get_contents($path .'/var/lastTemp.dat');
$last_temps     = explode('|', trim($last_temps));
$last_tempSet   = $last_temps[0]*1;
$last_tempIn    = $last_temps[1]*1;
$last_tempOut   = $last_temps[2]*1;
$last_humidIn   = $last_temps[3]*1;

$temps        = exec($path . "/heating/getTemp.php");
$temps        = explode("|", $temps);
$oldLog       = $temps[0]*1;
$tempSet      = $temps[1]*1;
$tempSet      = ($anybodyhome > 0) ? $tempSet : $configs["TEMPSET_EMPTY"];

$tempBoost = file_get_contents($path .'/var/tempBoost.dat');
$tempBoost = explode('|', $tempBoost);
if ( $tempBoost[1] > 0 ) {
  $tempSet = $tempBoost[0];
  file_put_contents($path .'/var/tempBoost.dat', $tempBoost[0].'|'.($tempBoost[1]-1));
}
$tempSet    = ($tempSet < $configs["TEMPSET_MIN"]) ? $configs["TEMPSET_MIN"] : $tempSet;

$tempIn     = ( abs(($temps[2]*1)-$last_tempIn) > 10 ) ? $last_tempIn : $temps[2]*1;
$tempOut    = ( abs(($temps[3]*1)-$last_tempOut) > 15 ) ? $last_tempOut : $temps[3]*1;
$humidIn    = ( abs(($temps[5]*1)-$last_humidIn) > 25 ) ? $last_humidIn : $temps[5]*1;
$heatingOn  = $temps[4]*1;

switch (TRUE) {

    // Heating on!
  case  ( $heatingOn == 0 ):
    switch (TRUE) {
      case ( $tempIn <= ($tempSet-$configs["HEAT_HYST"]) && ($tempOut + 3) < $tempSet ):
        $heatingOn = 1;
        $sendEmail  = true;
        $updateDb   = true;
        $subjectTPL = "Palim grijanje";
        exec($path."/heating/setHeating.sh on &");

        $msg = $subjectTPL .": ".$tempSet."°C/".$tempIn."°C/".$tempOut."°C..";
        include $path .'/notify/kodi.php';
      break;
    }
  break;

    // Heating off!
  case  ( $heatingOn == 1 ):
    switch (TRUE) {
      case ( $tempIn >= ($tempSet+$configs["HEAT_HYST"]) ):
      case ( ($tempOut + 3) >= $tempSet ):
        $sendEmail  = true;
        $updateDb   = true;
        $heatingOn = 0;
        $subjectTPL = ($anybodyhome > 0) ? "Gasim grijanje" : "Prazno pa gasim";
        exec($path."/heating/setHeating.sh off &");

        $msg = $subjectTPL .": ".$tempSet."°C/".$tempIn."°C/".$tempOut."°C..";
        include $path .'/notify/kodi.php';
      break;    
    }
  break;
  
  default;
    if ( date("i")%15 == 0 && $heatingOn == 0 ) exec($path."/heating/setHeating.sh ". $heatingOn ." &");
}

switch (TRUE) {  
  //case ( $oldLog == 1  ) :
  case ( $tempSet != $last_tempSet ):
  case ( $tempIn != $last_tempIn ):
  case ( $tempOut != $last_tempOut ):
  case ( $humidIn != $last_humidIn ):
    $updateDb   = true;
}

if ( $updateDb ) {

  $db = new SQLite3($path .'/var/heating.db'); 
  $sql = "INSERT INTO tempLog VALUES(datetime('now', 'localtime'), $tempSet, $tempIn, $tempOut, $heatingOn, $humidIn);"; 
  echo $sql;
  $db->query($sql);

  $db->query("UPDATE tempLog SET tempIn=null WHERE tempIn<0;");

  file_put_contents($path .'/var/lastTemp.dat', "$tempSet|$tempIn|$tempOut|$humidIn|". date("H:i:s"));
}


if ( $sendEmail ) {

  $subject = $subjectTPL .": ".$tempSet."°C/".$tempIn."°C/". $humidIn ."%/".$tempOut."°C..";
  $message = "Grijanje je ".$heatingState[$heatingOn]." i podešeno na ".$tempSet."°C.";
  $message .= "\rU kući je temp. ".$tempIn."°C i ". $humidIn ."% vlage, a vani ".$tempOut."°C..";

  include $path .'/notify/email.php';
}

?>