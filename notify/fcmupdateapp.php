#!/usr/bin/php
<?php
/* WORKING DIR constant */
define('DIR', str_replace('/notify', '', dirname(__FILE__)));

$sqlite = new SQLite3(DIR .'/var/hbrain.db');
$sqliteres = $sqlite->query("SELECT token FROM fcm WHERE approved = 'false'");

while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC)) {
    
    $command = DIR . "/homebrain notifier update '" . $argv[1] . "'";
    exec(DIR . "/homebrain notifier update '" . $argv[1] . "'");
    //echo $command . PHP_EOL;
}

?>