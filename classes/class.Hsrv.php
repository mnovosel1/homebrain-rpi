<?php

require_once DIR . "/classes/protected/class.Configs.php";
require_once DIR . "/classes/protected/class.SQLITE.php";
require_once DIR . "/classes/protected/class.LAN.php";

class Hsrv {
	
	public static function power() {
		if ( $_POST["param1"] == "" ) {
			return self::islive();
		}

		else if ( $_POST["param1"] == "1" ) {
			return self::wake();
		}

		else if ( $_POST["param1"] == "0" ) {
			return self::shut();
		}
	}

	public static function busy() {
		if ( $_POST["param1"] == "1" || $_POST["param1"] == "0" ) {
			return self::setbusy();
		}
		else if ( self::islive() ) {
			LAN::SSH("HomeServer", "chkserver");
			return (bool)SQLITE::fetchone("states", "active", "`name`='HomeServer busy'");
		} else return false;
	}

	public static function wake() {
		if ( !Auth::allowedIP() ) return false;
		if ( !self::islive() && LAN::WOL(Configs::getMAC("HomeServer")) ) return "OK";
		return false;
	}
	
	public static function shut() {
		if ( !Auth::allowedIP() ) return false;
		if ( self::islive() ) return LAN::SSH("HomeServer", "shutdown");
		return false;
	}

	private static function islive() {
		$live = exec("ping -c1 ".Configs::getIP("HomeServer")." | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
		if ($live > 0) return true;		
		return false;
	}

	private static function setbusy() {
		if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;
		if ( self::islive() )
			return SQLITE::update("states", "active", $_POST["param1"], "`name`='HomeServer busy'");
		else
			return SQLITE::update("states", "active", "0", "`name`='HomeServer busy'");
	}
}

?>