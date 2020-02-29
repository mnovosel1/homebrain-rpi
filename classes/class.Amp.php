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
        //MQTTclient::publish("hbrain/stat/amp/", "On", true);
    }
    
    public static function off() {
        // STANDBY
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA17887 &");
        MQTTclient::publish("hbrain/stat/amp/", "Off", true);
    }

    public static function volUp($count = 1) {
        // VOLUME_UP
        //debug_log(__METHOD__.":".__LINE__, "Amp::volUp(".$count.")");

        for ($i = 0; $i < $count; $i++)
            exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA158A7 &");
    }

    public static function mute() {
        // MUTE
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA138C7 &");
    }

    public static function volDown($count = 1) {
        // VOLUME_DOWN
        //debug_log(__METHOD__.":".__LINE__, "Amp::volDown(".$count.")");

        for ($i = 0; $i < $count; $i++)
            exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1D827 &");
    }

    public static function mpd() {
        // MD_CDR_INPUT
        Amp::on();
        MQTTclient::publish("hbrain/stat/amp/", "MPD", true);
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA1936C &");
    }

    public static function tv() {
        // D-TV_CBL_INPUT
        Amp::on();
        MQTTclient::publish("hbrain/stat/amp/", "TV", true);
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA12AD5 &");
        self::dolby();
    }

    public static function kodi() {
        // D-TV_CBL_INPUT
        Amp::on();
        MQTTclient::publish("hbrain/stat/amp/", "KODI", true);
        exec("sudo ". DIR ."/bin/nrf 1 irnec:5EA12AD5 &");
        self::dolby();
    }

    public static function aux() {
        // V-AUX_INPUT
        Amp::on();
        MQTTclient::publish("hbrain/stat/amp/", "AUX", true);
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

    public static function isOn() {
        $states = include(DIR ."/var/objStates.php");
        return ($states["amp"] != 'off');
    }
}

?>