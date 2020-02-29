<?php

class HomeServer {

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

	public static function keepOn() {

		exec('echo 1 > '.DIR.'/var/serverKeepOn');
	}

	public static function auto() {

		exec('echo 0 > '.DIR.'/var/serverKeepOn');
	}

	public static function busy() {

		$state = false;
		$isOn = false;

		if ( HomeServer::isOn() == "true" ) {
			$isOn = true;

			think("I'll check if HomeServer is busy now.");

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

			if (HomeServer::tvRecActive() == "true") {
				think("HomeServer is busy recording something on TV.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: TV is recording..");
				$state = true;
			}

			if (($waketime - time()) < 1800 ) {
				think("It's time to wake HomeServer.");
				hbrain_log(__METHOD__.":".__LINE__, "HomeServer: It's WakeTime!");
				$state = true;
			}

			if (!$state) {
				if ($isOn) {
					think("HomeServer is not doing much. Maybe I should shut him down. Any users active?");
				}
			}

			SQLITE::update("states", "active", (int)$state, "name='HomeServer busy'");
			return $state ? "true" : "false";
		}

		think("HomeServer is off, why am I checking him ?!");
	}

	public static function wake($reason = "") {
		think("Im waking up HomeServer because: ". $reason);
		// if ( Auth::allowedIP() && HomeServer::isOn() == "false" && LAN::WOL(Configs::getMAC("HomeServer")) ) {
		if ( LAN::WOL(Configs::getMAC("HomeServer")) ) {
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

			if ( trim($reason) == "null" || trim($reason) == "" ) {
				if ( isset($_POST["param1"]) && (trim($_POST["param1"] != "null") && trim($_POST["param1"] != "")) ) $reason = ": ".$_POST["param1"];
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
		if ( LAN::ping("HomeServer") ) {
			debug_log(__METHOD__.":".__LINE__, "HomeServer is live..");
			think("HomeServer is live.");
			SQLITE::update("states", "active", 1, "name='HomeServer'");
			return "true";
		}

		else {
			debug_log(__METHOD__.":".__LINE__, "HomeServer is not live..");
			think("HomeServer is not live. He's not dead actually, just off.");
			SQLITE::update("states", "active", 0, "name='HomeServer'");
			return "false";
		}
	}

	public static function setbusy($busy) {
		if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;

		if ( HomeServer::isOn() ) {
			return SQLITE::update("states", "active", $busy, "name='HomeServer busy'");
		}
		else
			return SQLITE::update("states", "active", 0, "name='HomeServer busy'");

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

	public static function tvRecActive() {
		$tvRecActive = (int)LAN::SSH("HomeServer", "curl -s http://bubul:passich@localhost:9981/api/dvr/entry/grid_upcoming | grep -q '\"sched_status\":\"recording\",'; if [ \"$?\" == \"0\" ]; then echo 1; else echo 0; fi;");
		if (abs(HomeServer::getWakeTime() - time()) < 900) $tvRecActive = 1;
		return ($tvRecActive > 0) ? "true" : "false";
	}

	public static function getWakeTime() {

		$wakeTimeLog = exec('cat '.DIR.'/var/srvWakeTime.log');
		$hServerIsOn = HomeServer::isOn();

		if ( $hServerIsOn == "true" ) {
			debug_log(__METHOD__.":".__LINE__, "HomeServer live, requesting waketime..");
			$waketime = (int)LAN::SSH("HomeServer", "/home/hbrain/getWakeTime");
		}
		else $waketime = $wakeTimeLog;

		if ($hServerIsOn == "true" && $waketime != 0) {
			$timeToWakeDiff = time() - $waketime;
			debug_log(__METHOD__.":".__LINE__, 'time() - $waketime = '. $timeToWakeDiff);

			if ($timeToWakeDiff > 0 && $timeToWakeDiff < 900) {
				think("HomeServer has something to do at: ". date("d.m.Y. H:i", $waketime) .". That's now, I'll keep him busy..");
			}
			else {
				think("HomeServer has something to do at: ". date("d.m.Y. H:i", $waketime) .". I'll do my best to wake him on time.");
			}
			debug_log(__METHOD__.":".__LINE__, "WakeTime for TV recording (?) : ". date("d.m.Y. H:i", $waketime));
		}

		else if ($waketime == 0 || $waketime - time() > (60*60*24)) {
			if ( date('U') <  date("U", strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"))) ) {
				$waketime = strtotime("today ". Configs::get("HomeServer", "DAILY_WAKE"));
				debug_log(__METHOD__.":".__LINE__, "WakeTime is TODAY: ". date("d.m.Y. H:i", $waketime));
			}

			else {
				$waketime = strtotime("tomorrow ". Configs::get("HomeServer", "DAILY_WAKE"));
				debug_log(__METHOD__.":".__LINE__, "WakeTime is TOMORROW: ". date("d.m.Y. H:i:s", $waketime));
			}
		}

		think("Next HomeServer waketime will be at: ". date("d.m.Y. H:i.", $waketime));
	
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
