#!/usr/bin/php
<?php
//ČĆŽŠĐčćžšđ
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$fromName   = "HomeBrain";
$fromEmail  = "notifier@bubulescu.org";
$recipient  = "marijo@bubulescu.org";
if ( isset($argv[3]) ) $recipient = $argv[3];
$header     = "From: ". $fromName . " <" . $fromEmail . ">\r\n";
$header     .= "Content-Type: text/html; charset=UTF-8" . ">\r\n";

if ( !$subject ) {
  $subject    = $argv[1];
  $message    = $argv[2];
}

mail($recipient, $subject, $message, $header);

?>