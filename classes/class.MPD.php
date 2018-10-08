<?php

class MPD {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(MPD::class);
    }

    public static function help() {
        return MyAPI::help(MPD::class);
    }

    public static function on() {
        
        Amp::on();

        if (MPD::playing() == "false") {
            LAN::SSH("KODI", "/usr/bin/mpc clear");
            LAN::SSH("KODI", "/usr/bin/mpc repeat on");
            LAN::SSH("KODI", "/usr/bin/mpc random on");
            LAN::SSH("KODI", "/usr/bin/mpc single off");
            LAN::SSH("KODI", "/usr/bin/mpc consume off");
            LAN::SSH("KODI", "/usr/bin/mpc load radio");
            LAN::SSH("KODI", "/usr/bin/mpc play 1");
        }
        Amp::mpd();
    }
    
    public static function off() {
        LAN::SSH("KODI", "/usr/bin/mpc clear");
        Amp::off();
    }

    public static function play() {
		if ( $_POST["param1"] != "" ) {
            MPD::stop();
            LAN::SSH("KODI", "/usr/bin/mpc play " + $_POST["param1"]);
        }
        return MPD::playing();
    }

    public static function next() {
        LAN::SSH("KODI", "/usr/bin/mpc next");
    }

    public static function prev() {
        LAN::SSH("KODI", "/usr/bin/mpc prev");
    }

    public static function stop() {
        LAN::SSH("KODI", "/usr/bin/mpc stop");
    }

    public static function playing() {

        debug_log(__METHOD__, "line: ". __LINE__);

        $mpdplay = LAN::SSH("KODI", "/usr/bin/mpc current");
        
        if ($mpdplay == "") {
            SQLITE::update("states", "active", 0, "`name`='MPD playing'");
            return "false";
        } else {
            SQLITE::update("states", "active", 1, "`name`='MPD playing'");
            return $mpdplay;
        }
    }
}

?>