<?php

class KODI {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(KODI::class);
    }

    public static function help() {
        return MyAPI::help(KODI::class);
    }

    public static function status() {
		return isOn();
	}

	public static function watch() {
		Amp::on();
		Amp::kodi();
		KODI::on();
		HomeBrain::wakecheck();
	}

	public static function isOn() {

			if ( exec("ssh ". Configs::get("KODI", "IP") ." 'if pgrep -x \'kodi\' >/dev/null; then echo '\on\'; else echo \'off\'; fi'") == "on" ) {
				SQLITE::update("states", "active", 1, "name='KODI'");
				return "true";
			}

			else {
				SQLITE::update("states", "active", 0, "name='KODI'");
				return "false";
			}
	}

	public static function on() {
		if (KODI::isOn()) return "true";

		TV::on();
		TV::kodi();
		sleep(3);

		exec("ssh ". Configs::get("KODI", "IP") ." '/usr/bin/kodi-standalone &'");
		//LAN::SSH("KODI", "/usr/bin/kodi-standalone &");
		HomeBrain::wakecheck();
		return "true";
    }

	public static function off() {
		TV::off();
		exec("ssh ". Configs::get("KODI", "IP") ." '/usr/bin/kodi-send --action=\'Quit\' &'");
		//LAN::SSH("KODI", "/usr/bin/kodi-send --action='Quit' &");
		return "true";
    }

}

?>
