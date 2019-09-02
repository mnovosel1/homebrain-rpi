<?php

class Amp {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }
    
    public static function on() {        
        // SYSTEM_POWER
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1B847 &");
    }
    
    public static function off() {
        // STANDBY
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA17887 &");
    }

    public static function volUp($count = 1) {
        // VOLUME_UP
        debug_log(__METHOD__.":".__LINE__, "Amp::volUp(".$count.")");

        for ($i = 0; $i < $count; $i++)
            exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA158A7 &");
    }

    public static function mute() {
        // MUTE
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA138C7 &");
    }

    public static function volDown($count = 1) {
        // VOLUME_DOWN
        debug_log(__METHOD__.":".__LINE__, "Amp::volDown(".$count.")");

        for ($i = 0; $i < $count; $i++)
            exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1D827 &");
    }

    public static function mpd() {
        // MD_CDR_INPUT
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1936C &");
    }

    public static function tv() {
        // D-TV_CBL_INPUT
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA12AD5 &");
        self::dolby();
    }

    public static function kodi() {
        self::tv();
    }

    public static function aux() {
        // V-AUX_INPUT
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1AA55 &");
    }

    public static function movie() {
        // MOVIE_THEATRE_1
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1718E &");
    }
        
    public static function dolby() {
        // DOLBY_DIGITAL_OR_DTS
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA109F6 &");
    }
            
    public static function music() {
        // ROCK_CONCERT
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA151AE &");
    }
}

?>