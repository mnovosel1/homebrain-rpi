<?php

require_once DIR . "/www/api/classSQLITE.php";

class Hsrv {

	public static function serverBusy() {

		// allowed IPs
		$allowedIPs = array("127.0.0.1", "10.10.10.100");
		if ( array_search($_SERVER["REMOTE_ADDR"], $allowedIPs) === false) return false;

		//SQLITE::update ($table, $attr, $value, $condition);
		return SQLITE::update ("states", "active", $_POST["param1"], "name='HomeServer busy'");
	}
}

?>