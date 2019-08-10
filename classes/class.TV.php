<?php

class TV {
    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function status() {
		return isOn();
    }

    public static function isOn() {
        if ( LAN::SSH("KODI", "echo 'pow 0' | /usr/bin/cec-client -s | grep 'power status:' | sed 's/power status: //'") == "on" ) {
            return "true";
        }
        return "false";
    }

    public static function on() {
        LAN::SSH("KODI", "echo 'on 0' | /usr/bin/cec-client -s >> /dev/null &");
    }

    public static function off() {
        LAN::SSH("KODI", "echo 'standby 0' | /usr/bin/cec-client -s >> /dev/null &");
    }

    public static function power() {
        exec("sudo ". DIR ."/bin/nrf 1 irsony:0A90 &");
    }

    public static function input() {
        exec("sudo ". DIR ."/bin/nrf 1 irsony:0A50 &");
    }

    public static function kodi() {
	    LAN::SSH("KODI", "echo \"as\" | /usr/bin/cec-client -s >> /dev/null &");
    }
}

?>
