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
		TV::kodi();
		HomeBrain::wakecheck();
	}

	public static function isOn() {

			if ( exec("ssh ". Configs::getIP("KODI") ." 'if pgrep -x \'kodi\' >/dev/null; then echo '\on\'; else echo \'off\'; fi'") == "on" ) {
				SQLITE::update("states", "active", 1, "name='KODI'");
				return "true";
			}

			else {
				SQLITE::update("states", "active", 0, "name='KODI'");
				return "false";
			}
	}

	public static function on() {
		exec("ssh kodi sudo /bin/systemctl restart kodi");
		exec("ssh kodi 'echo kodi > /home/hbrain/remote/mode'");
		TV::kodi();
		Amp::on();
		Amp::kodi();
		return "true";
    }

	public static function off() {
		//TV::off();
		exec("ssh kodi sudo /bin/systemctl stop kodi");
                //LAN::SSH("KODI", "sudo /bin/systemctl stop kodi &");
		//exec("ssh ". Configs::getIP("KODI") ." '/usr/bin/kodi-send --action=\'Quit\' &'");
		//LAN::SSH("KODI", "/usr/bin/kodi-send --action='Quit' &");
		return "true";
    }

}

?>
