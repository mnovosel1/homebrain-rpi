<?php

function getClassName($rawName) {
    switch (strtolower($rawName)) {
        case "hsrv":
        case "hserv":
        case "homeserver":
            $name = "HomeServer";
        break;

        case "hbr":
        case "hbrain":
        case "homebrain":
            $name = "HomeBrain";
        break;

        case "heat":
            $name = "Heating";
        break;

        case "kodi":
            $name = "KODI";
        break;

        case "mpd":
            $name = "MPD";
        break;

        case "iptv":
            $name = "IPTV";
        break;

        case "tv":
            $name = "TV";
        break;

        case "lan":
           $name = "LAN";
        break;

        case "finman":
            $name = "FinMan";
        break;

        default:
            $name = ucfirst(strtolower($rawName));
    }

    return $name;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* tsinking *//////////////////////////////////////////////////////////////////////////////////////
function think($what) {
	$what = trim($what);
	$lastLine = exec("tail -n 1 ". DIR ."/". Configs::get("HOMEBRAIN", "THINK"));
    exec("tail -n 4 ". DIR ."/". Configs::get("HOMEBRAIN", "THINK"), $lastFewLines);


	if (strpos($lastLine, $what) !== false) {
		$what = "I'm doing the same thing over and over again... :-/";
	}
	else if (strpos(implode(" ", $lastFewLines), $what) !== false) {
		if (strpos(implode(" ", $lastFewLines), "That's news, right") === false) {
			$what = $what ." That's news, right.";
		}
	}

	file_put_contents(DIR ."/". Configs::get("HOMEBRAIN", "THINK"), date("H:i") ." ". $what .PHP_EOL, FILE_APPEND);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//* DEBUGGING stuff *//////////////////////////////////////////////////////////////////////////////

function debug_log($where, $what) {
	if ( Configs::get("DEBUG") == "true" ) {
		write_log($where, $what, "DEBUG");
	}
}

function hbrain_log($where, $what) {
        write_log($where, $what);
}

function write_log ($where, $what, $whichLog = "INFO") {
    if (strpos($where, '::') !== false)
        $class = explode('::', $where)[0];
    else if (strpos($where, '.') !== false)
        $class = explode('.', $where)[1];
    else
        $class = $where;

	ob_start();
	echo date("[d.m.y. H:i:s] ");
	echo $_SERVER['REMOTE_ADDR'];
	echo " [".$whichLog."] ";
	echo $where." > ";
	var_dump($what);
	$out = ob_get_clean();

	file_put_contents(DIR ."/". Configs::get("HOMEBRAIN", "LOG"), $out, FILE_APPEND);
}

?>