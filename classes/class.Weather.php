<?php

class Weather {
    public static $debug = true;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }
    
    public static function get() {
        exec("/usr/bin/php ". DIR ."/classes/helpers/getweather.php", $ret);
        return $ret;
    }
}

?>