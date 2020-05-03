<?php

class KODI {
    public static $debug = false;

    public static function h() {
        return HomeBrain::help(KODI::class);
    }

    public static function help() {
        return HomeBrain::help(KODI::class);
    }

    public static function status() {
		return KODI::isOn();
	}

	public static function watch() {
		Amp::on();
		Amp::kodi();
		KODI::on();
		TV::kodi();
		HomeBrain::wakecheck();
	}

	public static function isOn() {
        $states = include(DIR ."/var/objStates.php");
        return ($states["kodi"] != 'off');
	}

	public static function on() {
		exec("ssh kodi sudo /bin/systemctl restart kodi");
		if (IPTV::isOn()) IPTV::sendKey("KEY_POWER");
		TV::kodi();
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
