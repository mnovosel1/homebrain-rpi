<?php

class Heating {

    public static function getTemps() {
        return explode("|", file_get_contents(DIR ."/var/lastTemp.dat"));
    }

    public static function getSetTemp() {
        return (float) 17.5;
    }
    
    public static function getInTemp() {
        return (float) self::getTemps()[1];
    }
    
    public static function getInHumid() {
        return (float) self::getTemps()[2];
    }

    public static function getOutTemp() {
        return (float) self::getTemps()[3];
    }

    public static function getOutHumid() {
        return (float) self::getTemps()[4];
    }

    public static function set() {
        
    }

    public static function auto() {

    }

    public static function isOn() {
        
    }

    public static function updateMob() {
        $tmp = self::getTemps();

        $data["tempSet"]    = sprintf("%01.1f", self::getSetTemp());
        $data["tempIn"]     = sprintf("%01.1f", $tmp[1]);
        $data["humidIn"]    = sprintf("%01.1f", $tmp[2]);
        $data["tempOut"]    = sprintf("%01.1f", $tmp[3]);
        $data["humidOut"]   = sprintf("%01.1f", $tmp[4]);

        HomeBrain::mobHeatUpdate(json_encode($data));
    }

}

?>