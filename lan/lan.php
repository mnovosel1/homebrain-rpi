#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

exit();
//////////////////////////////////////////////////////

$path = str_replace('/lan', '', dirname(__FILE__));

exec("/usr/bin/ssh 10.10.10.100 -p 9022 \"/root/chkforwake.sh\" > /srv/housebrain/var/srvWakeTime.log &");

$db = new SQLite3($path .'/var/lan.db');
$sql = "SELECT mac, name, allwaysOn FROM lanDevices;";
$result = $db->query($sql);

while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $lanDevices[$row['mac']]['name']        = $row['name'];
  $lanDevices[$row['mac']]['live']        = false;
  $lanDevices[$row['mac']]['recent']      = false;
  $lanDevices[$row['mac']]['allwaysOn']   = (bool)($row['allwaysOn']*1);
  
}

$result = $db->query("SELECT mac, ip, (stop IS NULL) AS live 
                        FROM lanLog WHERE
                          (stop IS NULL OR stop > datetime(datetime('now', 'localtime'), '-15 minutes'));");
while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $lanDevices[$row['mac']]['ip'] = $row['ip'];
  $nowLive = (bool)exec("ping -c1 ". $row['ip'] ." | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");


  switch (true)
  {
    case ($nowLive && $row['live']):
        $lanDevices[$row['mac']]['live'] = true;
    break;

    case ($nowLive && !$row['live']):
        $db->query("UPDATE lanLog
                              SET stop=NULL, ip='". $liveDevices[$row['mac']]['ip'] ."'
                              WHERE mac='". $row['mac'] ."' 
                                AND stop > datetime(datetime('now', 'localtime'), '-15 minutes');");
        $lanDevices[$row['mac']]['live'] = true;
    break;

    case (!$nowLive):
      $db->query("UPDATE lanLog SET stop=datetime('now', 'localtime') WHERE mac='". $row['mac'] ."' AND stop IS NULL;");
      $lanDevices[$row['mac']]['recent']  = true;
    break;
  }
}


exec("/usr/bin/arp-scan -l -I eth0", $returned);  

foreach ($returned as $line) {
  $line = explode("\t", $line);
  if ( filter_var($line[0], FILTER_VALIDATE_IP) ) {    
    $liveDevices[$line[1]]['ip'] = $line[0];
    $liveDevices[$line[1]]['name'] = $line[2];
    
    if ( !$lanDevices[$line[1]] )
      $db->query("INSERT INTO 
                    lanDevices (mac, name) 
                    VALUES ('". $line[1] ."', '". str_replace("'", "", $line[2]) ."');");

      if ( !$lanDevices[$line[1]]['live'] && !$lanDevices[$line[1]]['recent'] )
      $db->query("INSERT INTO 
                    lanLog (mac, ip, start) 
                    VALUES ('". $line[1] ."', '". $liveDevices[$line[1]]['ip'] ."', datetime('now', 'localtime'));");

  }
}


?>