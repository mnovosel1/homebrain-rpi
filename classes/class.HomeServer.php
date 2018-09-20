<?php

class HomeServer {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }
	
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

			switch (true) {
				case self::dailyCronActive():
					hbrain_log(__FILE__, "HomeServer: DailyCron working..");
					$state = true;
				break;

				case self::gDriveSyncActive():
					hbrain_log(__FILE__, "HomeServer: gDriveSync in progress..");
					$state = true;
				break;

				case self::usersActive():
					hbrain_log(__FILE__, "HomeServer: User is logged on..");
					$state = true;
				break;

				case self::torrentActive():
					hbrain_log(__FILE__, "HomeServer: Torrenting to do..");
					$state = true;
				break;

				case ($waketime - time()) < 1800:
					hbrain_log(__FILE__, "HomeServer: It's WakeTime!");
					$state = true;
				break;
			}

			SQLITE::update("states", "active", (int)$state, "`name`='HomeServer busy'");
			return $state ? "true" : "false";
		}
	}

	public static function wake($reason = "") {		
		if ( !self::isOn() && LAN::WOL(Configs::getMAC("HomeServer")) ) {
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "!";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is waking HomeServer".$reason);
			hbrain_log(__FILE__, "HomeBrain is waking HomeServer".$reason);
			return null;
		}
		return "false";
	}
	
	public static function shut($reason = "") {
		if ( self::isOn() ) {
			LAN::SSH("HomeServer", "shutdown");
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is shutting down HomeServer".$reason);
			hbrain_log(__FILE__, "HomeBrain is shutting down HomeServer".$reason);
			return null;
		}
		return "false";
	}
	
	public static function reboot($reason = "") {
		if ( Auth::allowedIP() && self::isOn() ) {
			LAN::SSH("HomeServer", "reboot");
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is rebooting HomeServer".$reason);
			hbrain_log(__FILE__, "HomeBrain is rebooting HomeServer".$reason);
		}
		return "false";
	}

	public static function isOn() {
		if ( LAN::ping("HomeServer") ) {
			SQLITE::update("states", "active", 1, "`name`='HomeServer'");
			return "true";
		}

		else {
			SQLITE::update("states", "active", 0, "`name`='HomeServer'");
			return "false";
		}
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
		return ($dailyCron > 0 || $dailyCronWorker > 0) ? "true" : "false";
	}

	public static function gDriveSyncActive() {
		$gDriveSync 		= (int)LAN::SSH("HomeServer", "pgrep -x 'gDriveSync.sh'");
		return ($gDriveSync > 0) ? "true" : "false";
	}
	
	public static function usersActive() {
		$users = (int)LAN::SSH("HomeServer", "who | wc -l");
		return ($users > 0) ? "true" : "false";
	}
	
	public static function torrentActive() {
		$torrenting = (int)LAN::SSH("HomeServer", "transmission-remote --list | sed '1d;\$d' | grep -v Done | wc -l");
		return ($torrenting > 0) ? "true" : "false";
	}

	public static function getWakeTime() {
		if ( self::isOn() ) {
			$waketime = (int)LAN::SSH("HomeServer", "getWaketime");
			exec('echo '.$waketime.' > '. DIR .'/var/srvWakeTime.log');
		}
		else $waketime = exec('cat '.DIR.'/var/srvWakeTime.log');
		
		return $waketime;
	}

	public static function timeToWake() {
		
		$waketime = self::getWakeTime();

		$howlong = '';
		$seconds = $waketime - time(); 
		$minutes = (int)($seconds / 60);
		$hours = (int)($minutes / 60);
		$days = (int)($hours / 24);
		if (abs($days) >= 1) {
		  $howlong = $days . ' day' . ($days != 1 ? 's' : '');
		} else if (abs($hours) >= 1) {
		  $howlong = $hours . ' hour' . ($hours != 1 ? 's' : '');
		} else if (abs($minutes) >= 1) {
		  $howlong = $minutes . ' min' . ($minutes != 1 ? 's' : '');
		} else {
		  $howlong = $seconds . ' sec' . ($seconds != 1 ? 's' : '');
		}

		return "Waking HomeServer at ".date("H:i d.m.y.", $waketime)." - ".$howlong." left.";
	}
}

?>