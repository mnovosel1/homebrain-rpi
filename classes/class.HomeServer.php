<?php

class HomeServer {
    public static $debug = true;

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
		think("I'll check if HomeServer is busy now.");
		if ( $_POST["param1"] == "1" || $_POST["param1"] == "0" ) {
			return HomeServer::setbusy($_POST["param1"]);
		}

		if ( HomeServer::isOn() ) {
			$state = false;
			$waketime = HomeServer::getWakeTime();

			if (HomeServer::rpiBackupActive() == "true") {
				think("HomeServer is busy backuping HomeBrain RPi.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: RpiBackup in progress..");
				$state = true;
			}

			if (HomeServer::dailyCronActive() == "true") {
                                think("HomeServer is busy because DailyCron is still working.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: DailyCron working..");
				$state = true;
			}

			if (HomeServer::gDriveSyncActive() == "true") {
                                think("HomeServer is busy syncing Google drive.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: gDriveSync in progress..");
				$state = true;
			}

			if (HomeServer::usersActive() == "true") {
                                think("HomeServer is considered busy because some user is still logged on.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: User is logged on..");
				$state = true;
			}

			if (HomeServer::torrentActive() == "true") {
				think("HomeServer has some torrenting to do.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: Torrenting to do..");
				$state = true;
			}

			if (($waketime - time()) < 1800 ) {
				think("It's time to wake HomeServer.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: It's WakeTime!");
				$state = true;
			}

			SQLITE::update("states", "active", (int)$state, "`name`='HomeServer busy'");
			return $state ? "true" : "false";
		}
	}

	public static function wake($reason = "") {
		think("Im waking up HomeServer because: ". $reason);
		if ( Auth::allowedIP() && HomeServer::isOn() == "false" && LAN::WOL(Configs::getMAC("HomeServer")) ) {
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) && $_POST["param1"] != "null" ) $reason = ": ".$_POST["param1"];
				else $reason = "!";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is waking HomeServer".$reason);
			hbrain_log(__METHOD__.":".__LINE__, "HomeBrain is waking HomeServer".$reason);
			return null;
		}
		return "false";
	}

	public static function shut($reason = "") {

		if ( Auth::allowedIP() && HomeServer::isOn() ) {
			LAN::SSH("HomeServer", "shutdown");

			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) && $_POST["param1"] != "null" ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;

			Notifier::fcmBcast("HomeBrain", "is shutting down HomeServer".$reason);
			hbrain_log(__METHOD__.":".__LINE__, "HomeBrain is shutting down HomeServer".$reason);

			return null;
		}
		return "false";
	}

	public static function reboot($reason = "") {
		if ( Auth::allowedIP() && HomeServer::isOn() ) {
			LAN::SSH("HomeServer", "reboot");
			if ( $reason == "" ) {
				if ( isset($_POST["param1"]) && $_POST["param1"] != "null" ) $reason = ": ".$_POST["param1"];
				else $reason = "..";
			} else $reason = ": ".$reason;
			Notifier::fcmBcast("HomeBrain", "is rebooting HomeServer".$reason);
			hbrain_log(__METHOD__.":".__LINE__, "HomeBrain is rebooting HomeServer".$reason);
		}
		return "false";
	}

	public static function isOn() {
		if ( (bool)LAN::ping("HomeServer") ) {
			debug_log(__METHOD__.":".__LINE__, "HomeServer is live..");
			SQLITE::update("states", "active", 1, "`name`='HomeServer'");
			return "true";
		}

		else {
			debug_log(__METHOD__.":".__LINE__, "HomeServer is not live..");
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

	public static function rpiBackupActive() {
		$dailyCron = (int)LAN::SSH("HomeServer", "/home/hbrain/rpiBackupLock.sh");
		return ($dailyCron > 0) ? "true" : "false";
	}

	public static function dailyCronActive() {
		$dailyCron = (int)LAN::SSH("HomeServer", "if [ -d /tmp/dailyCron.lock ]; then echo 1; else echo 0; fi");
		return ($dailyCron > 0) ? "true" : "false";
	}

	public static function gDriveSyncActive() {
		$gDriveSync = (int)LAN::SSH("HomeServer", "pgrep -x 'gDriveSync.sh'");
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

		$wakeTimeLog = exec('cat '.DIR.'/var/srvWakeTime.log');
		debug_log(__METHOD__.":".__LINE__, "WakeTime in log: ". date("d.m.Y. H:i:s", $wakeTimeLog));

		if ( HomeServer::isOn() == "true" ) {
			debug_log(__METHOD__.":".__LINE__, "HomeServer live, requesting waketime..");
			$waketime = (int)LAN::SSH("HomeServer", "/home/hbrain/getWakeTime");
		}
		else $waketime = $wakeTimeLog;

		if ($waketime == 0 || $waketime - time() > (60*60*24)) {
			if ( date('U') <  date("U", strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"))) ) {
				$waketime = strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"));
				debug_log(__METHOD__.":".__LINE__, "WakeTime is TODAY: ". date("d.m.Y. H:i:s", $waketime));
			}
			else {
				$waketime = strtotime("tomorrow ". Configs::get("HomeServer", "DAILY_WAKE"));
				debug_log(__METHOD__.":".__LINE__, "WakeTime is TOMORROW: ". date("d.m.Y. H:i:s", $waketime));
			}
		}

		if ($waketime < $wakeTimeLog || $wakeTimeLog < time() || $wakeTimeLog == 0) {
			exec('echo '.$waketime.' > '. DIR .'/var/srvWakeTime.log');
		}

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

		return "Waking HomeServer at ".date("H:i d.m.y.", $waketime)." (".$howlong." left)";
	}
}

?>
