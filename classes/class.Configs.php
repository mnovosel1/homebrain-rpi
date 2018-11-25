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
	$mac = SQLITE::query("SELECT mac FROM lan WHERE name = '". strtolower($host) ."'")[0]["mac"];
	debug_log(__METHOD__.":".__LINE__, $mac);
	return $mac;

//        return Configs::get($host, "MAC");
    }

    public static function getIP($host) {
	return SQLITE::query("SELECT ip FROM lan WHERE name = '". strtolower($host) ."'")[0]["ip"];
//        return Configs::get($host, "IP");
    }

    public static function getFCM($config) {
        return Configs::get("FCM", $config);
    }

    public static function debug($class) {
        $debug = Configs::get("DEBUG");
        return in_array($class, $debug);
    }

    private static function getAll() {
        return parse_ini_file(DIR.'/'.INI_FILE);
    }

    public static function set($key, $value) {
        $configs = Configs::getAll();
        $key = strtoupper($key);

        if (!array_key_exists($key, $configs)) {
            debug_log(__METHOD__.":".__LINE__, $key ."=>". $value ." doesn't exist..");
            return false;
        }

        $configs[$key] = $value;

        $out = ";<?php". PHP_EOL .";die();". PHP_EOL .";/*". PHP_EOL;
        $last_k = "";

        foreach ($configs as $k => $v) {
            $out .= ($k != $last_k) ? PHP_EOL : "";
            $last_k = $k;

            if(!is_array($v)) {
                $out .= "$k=\"$v\"". PHP_EOL;
            }

            else {
                foreach ($v as $k2 => $v2) {
                    $out .= $k ."[".$k2."]=\"".$v2."\"".PHP_EOL;
                }
            }

        }
        $out .= PHP_EOL .";*/". PHP_EOL .";?>";
        file_put_contents(DIR.'/'.INI_FILE, $out);
    }
}

?>
