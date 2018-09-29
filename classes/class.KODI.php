<?php

class KODI {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function status() {
		return isOn();
	}

	public static function watch() {
		Amp::on();
		KODI::on();
		TV::on();
		Amp::kodi();
	}

	public static function isOn() {

			if ( LAN::SSH("KODI", 
					"if pgrep -x 'kodi' >/dev/null; then echo 'on'; else echo 'off'; fi") == "on" ) {
				SQLITE::update("states", "active", 1, "`name`='KODI'");
				return "true";
			}
	
			else {
				SQLITE::update("states", "active", 0, "`name`='KODI'");
				return "false";
			}

		/*
		$res = SQLITE::fetch("states", ["active"], "name = 'KODI'");
		$res = $res[0]["active"];
		
		if ($res > 0) return true;
		else return false;
		*/
	}
	
	public static function on() {
		LAN::SSH("KODI", "kodi > /dev/null &");
		SQLITE::update("states", "active", 1, "`name`='KODI'");
		HomeBrain::wakecheck();
    }
	
	public static function off() {
		LAN::SSH("KODI", "kodi-send --action='Quit' > /dev/null &");
		SQLITE::update("states", "active", 0, "`name`='KODI'");
    }
    
}

?>