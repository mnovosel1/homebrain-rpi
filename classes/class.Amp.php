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
        exec("sudo /usr/bin/nrf 1 irnec:5EA1B847 &");
    }
    
    public static function off() {
        // STANDBY
        exec("sudo /usr/bin/nrf 1 irnec:5EA17887 &");
    }

    public static function volup($count = 1) {
        // VOLUME_UP
        for ($i = 0; $i < $count; $i++)
            exec("sudo /usr/bin/nrf 1 irnec:5EA158A7 &");
    }

    public static function volup1() {
        self::volup(1);
    }

    public static function volup2() {
        self::volup(2);
    }

    public static function mute() {
        // MUTE
        exec("sudo /usr/bin/nrf 1 irnec:5EA138C7 &");
    }

    public static function voldown($count = 1) {
        // VOLUME_DOWN
        for ($i = 0; $i < $count; $i++)
            exec("sudo /usr/bin/nrf 1 irnec:5EA1D827 &");
    }

    public static function voldown1() {
        self::voldown(1);
    }

    public static function voldown2() {
        self::voldown(2);
    }

    public static function mpd() {
        // MD_CDR_INPUT
        exec("sudo /usr/bin/nrf 1 irnec:5EA1936C &");
    }

    public static function kodi() {
        // D-TV_CBL_INPUT
        exec("sudo /usr/bin/nrf 1 irnec:5EA12AD5 &");
    }

    public static function aux() {
        // V-AUX_INPUT
        exec("sudo /usr/bin/nrf 1 irnec:5EA1AA55 &");
    }

    public static function movie() {
        // MOVIE_THEATRE_1
        exec("sudo /usr/bin/nrf 1 irnec:5EA1718E &");
    }
        
    public static function dolby() {
        // DOLBY_DIGITAL_OR_DTS
        exec("sudo /usr/bin/nrf 1 irnec:5EA109F6 &");
    }
            
    public static function music() {
        // ROCK_CONCERT
        exec("sudo /usr/bin/nrf 1 irnec:5EA151AE &");
    }
}

?>