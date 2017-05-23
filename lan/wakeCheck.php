#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/lan', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');

$db         = new SQLite3($path .'/var/lan.db');

$sql = "SELECT mac FROM lanLog WHERE stop IS NULL;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  var_dump($row);
}
?>