#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if ( !$msg ) {
  $msg    = $argv[1];
}

exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"HomeBrain","message":"'. $msg .'"},"id":1}\' http://10.10.10.20:80/jsonrpc 2>/dev/null');
?>