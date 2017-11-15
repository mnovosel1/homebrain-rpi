<?php

class HomeServer {
	
	public static function power() {
		if ( $_POST["param1"] == "" ) {
			return self::isOn();
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
			return self::setbusy($_POST["param1"]);
		}

		else if ( self::isOn() ) {
			$state = false;
			$waketime = self::getWakeTime();

			if (
				self::dailyCronActive() ||
				self::usersActive() ||
				self::torrentActive() ||
				($waketime - time()) < 1800
			)
			$state = true;

			return $state;
		}
	}

	public static function wake($reason = "") {
		if ( !Auth::allowedIP() ) return false;
		if ( !self::isOn() && LAN::WOL(Configs::getMAC("HomeServer")) ) {
			if ( $reason == "" ) $reason = "!";
			else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "waking HomeServer".$reason);
			return null;
		}
		return false;
	}
	
	public static function shut($reason = "") {
		if ( !Auth::allowedIP() ) return false;
		if ( self::isOn() ) {
			LAN::SSH("HomeServer", "shutdown");
			if ( $reason == "" ) $reason = "..";
			else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "shutting down HomeServer".$reason);
			return null;
		}
		return false;
	}
	
	public static function reboot($reason = "") {
		if ( !Auth::allowedIP() ) return false;
		if ( self::isOn() ) {
			LAN::SSH("HomeServer", "reboot");
			if ( $reason == "" ) $reason = "..";
			else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "Rebooting HomeServer".$reason);
		}
		return false;
	}

	public static function isOn() {
		return LAN::ping("HomeServer");
	}

	public static function setbusy($busy) {
		if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;

		if ( self::isOn() ) {
			return SQLITE::update("states", "active", $busy, "`name`='HomeServer busy'");
		}
		else
			return SQLITE::update("states", "active", 0, "`name`='HomeServer busy'");
		
		return null;
	}

	public static function dailyCronActive() {
		$dailyCron 			= (int)LAN::SSH("HomeServer", "pgrep -x 'dailyCron'");
		$dailyCronWorker 	= (int)LAN::SSH("HomeServer", "pgrep -x 'dailyCronWorker'");
		return ($dailyCron > 0 || $dailyCronWorker > 0) ? true : false;
	}
	
	public static function usersActive() {
		$users = (int)LAN::SSH("HomeServer", "who | wc -l");
		return ($users > 0) ? true : false;
	}
	
	public static function torrentActive() {
		$torrenting = (int)LAN::SSH("HomeServer", "transmission-remote --list | sed '1d;\$d' | grep -v Done | wc -l");
		return ($torrenting > 0) ? true : false;
	}

	public static function getWakeTime() {
		if ( self::isOn() ) {
			$waketime = (int)LAN::SSH("HomeServer", "getWaketime");
			exec('echo '.$waketime.' > '. DIR .'/var/srvWakeTime.log');
		}
		else $waketime = exec('cat '.DIR.'/var/srvWakeTime.log');
		
		return $waketime;
	}
}

?>