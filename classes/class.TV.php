<?php

class TV {
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
    
    public static function isOn() {        
        if ( LAN::SSH("KODI", "hbtv status") == "on" ) {
            SQLITE::update("states", "active", 1, "`name`='TV'");
            return "true";
        }

        SQLITE::update("states", "active", 0, "`name`='TV'");
        return "false";
    }

    public static function on() {
        LAN::SSH("KODI", "hbtv on");
    }

    public static function off() {
        LAN::SSH("KODI", "hbtv off");
    }
     
    public static function power() {
        exec("sudo /usr/bin/nrf 1 irsony:0A90 &");
    }

    public static function input() {
        exec("sudo /usr/bin/nrf 1 irsony:0A50 &");
    }
}

?>