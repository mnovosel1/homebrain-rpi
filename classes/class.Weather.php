<?php

class Weather {
    public static $debug = true;
    
    public static function get() {
        echo exec("/usr/bin/php ". DIR ."/classes/helpers/getweather.php");
    }
}

?>