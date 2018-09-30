<?php
define('INI_FILE', ".configs");

class Configs {

    public static function get($cfg1, $cfg2 = null) {
        $configs = Configs::getAll();
        if ( $cfg2 === null )
            return $configs[strtoupper($cfg1)];
        else
            return $configs[strtoupper($cfg1)][strtoupper($cfg2)];
        return false;
    }
    
    public static function getMAC($host) {
        return Configs::get($host, "MAC");
    }
    
    public static function getIP($host) {
        return Configs::get($host, "IP");
    }
        
    public static function getFCM($config) {
        return Configs::get("FCM", $config);
    }

    private static function getAll() {
        return parse_ini_file(DIR.'/'.INI_FILE);
    }
}

?>