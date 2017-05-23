#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/lan', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');


// LAN //////////////////////////////////////////////////////////////////////////////////////
$output = '';
exec('sqlite3 '. $path .'/var/lan.db \'.dump "lanDevices"\' | grep \'^INSERT\'', $output);

$sqldump = "
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE lanDevices
(
  mac TEXT,
  name TEXT,
  allwaysOn BOOL,
  heating BOOL,
  turnOn INT,
  PRIMARY KEY (mac)
);

";

foreach ( $output as $line ) {
  $sqldump .= $line . "\n";
}

$sqldump .= "
CREATE TABLE turnOn
(
  ifon TEXT,
  thenon TEXT,
  PRIMARY KEY (ifon, thenon)
);

CREATE TABLE lanLog
(
  mac TEXT,
  ip TEXT,
  start DATETIME,
  stop DATETIME,
  PRIMARY KEY (mac, start)
);
COMMIT;
";

file_put_contents($path .'/lan/lan.sql', $sqldump);
/////////////////////////////////////////////////////////////////////////////////////////////

?>