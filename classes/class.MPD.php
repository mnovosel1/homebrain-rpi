<?php

class MPD {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function on() {
        LAN::SSH("KODI", "hbmpd on");
    }
    
    public static function off() {
        LAN::SSH("KODI", "hbmpd off");
    }

    public static function play() {
		if ( $_POST["param1"] != "" ) {
            self::stop();
            LAN::SSH("KODI", "hbmpd play " + $_POST["param1"]);
        }
        return self::playing();
    }

    public static function next() {
        LAN::SSH("KODI", "hbmpd next");
    }

    public static function prev() {
        LAN::SSH("KODI", "hbmpd prev");
    }

    public static function stop() {
        LAN::SSH("KODI", "hbmpd stop");
    }

    public static function playing() {

        $mpdplay = LAN::SSH("KODI", "hbmpd current");
        
        if ($mpdplay == "") {
            SQLITE::update("states", "active", 0, "`name`='MPD playing'");
            return null;
        } else {
            SQLITE::update("states", "active", 1, "`name`='MPD playing'");
            return $mpdplay;
        }
    }
}

?>