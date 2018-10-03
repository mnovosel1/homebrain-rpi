<?php

class HomeServer {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(HomeServer::class);
    }

    public static function help() {
        return MyAPI::help(HomeServer::class);
    }

	public static function power() {
		if ( $_POST["param1"] == "" ) {
			return HomeServer::isOn();
		}

		else if ( $_POST["param1"] == "1" ) {
			return HomeServer::wake();
		}

		else if ( $_POST["param1"] == "0" ) {
			return HomeServer::shut();
		}
	}

	public static function busy() {
		if ( $_POST["param1"] == "1" || $_POST["param1"] == "0" ) {
			return HomeServer::setbusy($_POST["param1"]);
		}

		if ( HomeServer::isOn() ) {
			$state = false;
			$waketime = HomeServer::getWakeTime();

			if (HomeServer::dailyCronActive() == "true") {
				hbrain_log(__METHOD__, "HomeServer: DailyCron working..");
				$state = true;
			}

			if (HomeServer::gDriveSyncActive() == "true") {
				hbrain_log(__METHOD__, "HomeServer: gDriveSync in progress..");
				$state = true;
			}

			if (HomeServer::usersActive() == "true") {
				hbrain_log(__METHOD__, "HomeServer: User is logged on..");
				$state = true;
			}

			if (HomeServer::torrentActive() == "true") {
				hbrain_log(__METHOD__, "HomeServer: Torrenting to do..");
				$state = true;
			}

			if (($waketime - time()) < 1800 ) {
					hbrain_log(__METHOD__, "HomeServer: It's WakeTime!");
					$state = true;
			}

			SQLITE::update("states", "active", (int)$state, "`name`='HomeServer busy'");
			return $state ? "true" : "false";
		}
	}

	public static function wake($reason = "") {
		if ( HomeServer::isOn() == "false" && LAN::WOL(Configs::getMAC("HomeServer")) ) {
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "!";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is waking HomeServer".$reason);
			hbrain_log(__METHOD__, "HomeBrain is waking HomeServer".$reason);
			return null;
		}
		return "false";
	}

	public static function shut($reason = "") {
		if ( HomeServer::isOn() ) {
			LAN::SSH("HomeServer", "shutdown");
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is shutting down HomeServer".$reason);
			hbrain_log(__METHOD__, "HomeBrain is shutting down HomeServer".$reason);
			return null;
		}
		return "false";
	}

	public static function reboot($reason = "") {
		if ( Auth::allowedIP() && HomeServer::isOn() ) {
			LAN::SSH("HomeServer", "reboot");
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is rebooting HomeServer".$reason);
			hbrain_log(__METHOD__, "HomeBrain is rebooting HomeServer".$reason);
		}
		return "false";
	}

	public static function isOn() {
		if ( (bool)LAN::ping("HomeServer") ) {
			debug_log(__METHOD__, "HomeServer is live..");
			SQLITE::update("states", "active", 1, "`name`='HomeServer'");
			return "true";
		}

		else {
			debug_log(__METHOD__, "HomeServer is not live..");
			SQLITE::update("states", "active", 0, "`name`='HomeServer'");
			return "false";
		}
	}

	public static function setbusy($busy) {
		if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;

		if ( HomeServer::isOn() ) {
			return SQLITE::update("states", "active", $busy, "`name`='HomeServer busy'");
		}
		else
			return SQLITE::update("states", "active", 0, "`name`='HomeServer busy'");

		return null;
	}

	public static function dailyCronActive() {
		$dailyCron = (int)LAN::SSH("HomeServer", 
									"if [ -d /tmp/dailyCron.lock ]; then echo 1; else echo 0; fi");
		return ($dailyCron > 0) ? "true" : "false";
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

		$waketimeLog = exec('cat '.DIR.'/var/srvWakeTime.log');

		if ( HomeServer::isOn() == "true" ) {
			debug_log(__METHOD__, "Requesting waketime from HomeServer..");
			$waketime = (int)LAN::SSH("HomeServer", "/home/hbrain/getWakeTime");
			if ($waketime <= 0) {
				if ( date('H') <  date("G", strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"))) ) {
					$waketime = strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"));
				}
				else {
					$waketime = strtotime("tomorrow ". Configs::get("HomeServer", "DAILY_WAKE"));
				}
			}
			if ($waketime < $waketimeLog || $waketimeLog == 0) {
				exec('echo '.$waketime.' > '. DIR .'/var/srvWakeTime.log');
			}
		}
		else {
			$waketime = $waketimeLog;
		}

		debug_log(__METHOD__, "Waketime: ". $waketime);

		return $waketime;
	}

	public static function timeToWake() {

		$waketime = HomeServer::getWakeTime();

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
