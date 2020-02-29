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
		if (trim(exec("cat /srv/HomeBrain/remote/mode")) == "iptv") IPTV::sendKey("KEY_POWER");
		exec("echo kodi > /srv/HomeBrain/remote/mode");
		TV::kodi();
		Amp::on();
		Amp::kodi();
        MQTTclient::publish("hbrain/stat/kodi/", "on", true);
		return "true";
    }

	public static function off() {
		//TV::off();
		exec("ssh kodi sudo /bin/systemctl stop kodi");
        //LAN::SSH("KODI", "sudo /bin/systemctl stop kodi &");
		//exec("ssh ". Configs::getIP("KODI") ." '/usr/bin/kodi-send --action=\'Quit\' &'");
		//LAN::SSH("KODI", "/usr/bin/kodi-send --action='Quit' &");
        MQTTclient::publish("hbrain/stat/kodi/", "off", true);
		return "true";
    }

}

?>
