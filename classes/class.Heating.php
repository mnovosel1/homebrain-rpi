<?php

class Heating {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(Heating::class);
    }

    public static function help() {
        return MyAPI::help(Heating::class);
    }

    public static function getTemps() {
        return explode("|", file_get_contents(DIR ."/var/lastTemp.dat"));
    }

    public static function getSetTemp() {
        return (float) 17.5;
    }
    
    public static function getInTemp() {
        return (float) Heating::getTemps()[1];
    }
    
    public static function getInHumid() {
        return (float) Heating::getTemps()[2];
    }

    public static function getOutTemp() {
        return (float) Heating::getTemps()[3];
    }

    public static function getOutHumid() {
        return (float) Heating::getTemps()[4];
    }

    public static function set() {
        
    }

    public static function auto() {

    }

    public static function isOn() {
        return 0;
    }

    public static function updateMob() {
        $tmp = Heating::getTemps();

        $data["tempSet"]    = sprintf("%01.1f", Heating::getSetTemp());
        $data["tempIn"]     = sprintf("%01.1f", $tmp[1]);
        $data["humidIn"]    = sprintf("%01.1f", $tmp[2]);
        $data["tempOut"]    = sprintf("%01.1f", $tmp[3]);
        $data["humidOut"]   = sprintf("%01.1f", $tmp[4]);

        HomeBrain::mobHeatUpdate(json_encode($data));
    }

}

?>