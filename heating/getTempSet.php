#!/usr/bin/php
<?php
$path = str_replace('/heating', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');

$db         = new SQLite3($path .'/var/heating.db');
$wday       = ((date("w")-1) < 0) ? 6 : (date("w")-1);

$sql = "SELECT COUNT() AS weight, 
               AVG(tempIn) AS tempInAvg,
               STRFTIME('%H', timestamp)*1 AS hour,
               STRFTIME('%w', timestamp)*1 AS wday
            FROM tempLog 
              WHERE wday = $wday
               AND timestamp > DATETIME(DATETIME('now', 'localtime'), '-14 DAYS')
             GROUP BY wday, hour;";

$result = $db->query($sql);

$mysqlSQL = '';

while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $floatPart = $row['tempInAvg'] - floor($row['tempInAvg']);
  $tempSet = $row['tempInAvg'] - $floatPart;
  
  switch (true)
  {    
    case ( $floatPart >= 0.25 && $floatPart < 0.75 ):
      $tempSet += 0.5;
    break;
    
    case ( $floatPart >= 0.75 ):
      $tempSet += 1;
    break;
  }
  $tempSet = ($tempSet > $configs["TEMPSET_MAX"]) ? $configs["TEMPSET_MAX"] : $tempSet;
  $tempSet = ($tempSet < $configs["TEMPSET_MIN"]) ? $configs["TEMPSET_MIN"] : $tempSet;
  
  $sql = "REPLACE INTO `tempConf` (wday, hour, temp) VALUES (". $row['wday'] .", ". $row['hour'] .", $tempSet);";
  $mysqlSQL .= $sql ."\n";
  $db->query($sql);
}

$db->query("UPDATE tempConf SET temp=". $configs['TEMPSET_NIGHT'] ." WHERE hour<6;");

$mysqlSQL .= "UPDATE tempConf SET temp=". $configs['TEMPSET_NIGHT'] ." WHERE hour<6;";
$mysqli = new mysqli("bubulescu.org", "bubul_mn", "WNl)rf&6.UhB", "bubul_homebrain");
$mysqli->multi_query($mysqlSQL);
$mysqli->close();

?>