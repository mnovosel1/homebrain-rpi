<?php

class MPD {
    public static $debug = true;

    public static function play() {
		if ( $_POST["param1"] != "" ) {
            self::stop();
            exec("/usr/bin/mpc repeat on");
            exec("/usr/bin/mpc random off");
            exec("/usr/bin/mpc single off");
            exec("/usr/bin/mpc consume off");
            exec("/usr/bin/mpc load ".$_POST["param1"]);
            exec("/usr/bin/mpc play 1");

            exec("/usr/bin/irsend SEND_ONCE Yamaha SYSTEM_POWER");
            exec("/bin/sleep 1;");
            exec("/usr/bin/irsend SEND_ONCE Yamaha D-TV_CBL_INPUT");            
        }
        return self::playing();
    }

    public static function stop() {        
            exec("/usr/bin/mpc clear");
            return self::playing();
    }

    public static function playing() {
        $mpdplay = exec("/usr/bin/mpc current");
        
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