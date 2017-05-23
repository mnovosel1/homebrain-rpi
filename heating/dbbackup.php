#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/heating', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');


$output = '';
exec('sqlite3 '. $path .'/var/heating.db \'.dump tempLog\' | grep \'^INSERT\'', $output);

$sql = '';
foreach ( $output as $line )
  $sql .= $line . "\n";
$mysqli = new mysqli("bubulescu.org", "bubul_mn", "5tNjxtteikhqVito6Yv5", "bubul_housebrain");
$mysqli->multi_query(str_replace('INSERT INTO "tempLog"', 'REPLACE INTO tempLog', $sql));
$mysqli->close();

// HEATING //////////////////////////////////////////////////////////////////////////////////
$output = '';
exec('sqlite3 '. $path .'/var/heating.db \'.dump "tempConf"\' | grep \'^INSERT\'', $output);

$sqlPre = "
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE tempConf
(
    wday INT,
    hour INT,
    temp INT,
    PRIMARY KEY (wday, hour)
);

";

$sql = '';
foreach ( $output as $line )
  $sql .= $line . "\n";
//$mysqli->multi_query(str_replace('INSERT INTO "tempConf"', 'REPLACE INTO tempConf', $sql));
//var_dump(str_replace('INSERT INTO "tempConf"', 'REPLACE INTO tempConf', $sql));

$sqlPost .= "
CREATE TABLE tempLog
(
    timestamp DATETIME,
    tempSet REAL,
    tempIn REAL,
    tempOut REAL,
    heatingOn INT,
    humidIn REAL,
    PRIMARY KEY (timestamp)
);
COMMIT;
";

file_put_contents($path .'/heating/heating.sql', $sqlPre . $sql . $sqlPost);
/////////////////////////////////////////////////////////////////////////////////////////////

$db = new SQLite3($path .'/var/heating.db'); 
$db->query("DELETE FROM tempLog WHERE timestamp < datetime(datetime('now', 'localtime'), '-30 DAYS')");

?>