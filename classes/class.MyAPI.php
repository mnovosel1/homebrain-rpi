<?php

class MyAPI extends API {
    public static $debug = true;

    // Only callable methods are available from HomeBrain CLI (and Web API)
    private static $callable = array (
        "Reg::register",
        "Reg::verify",
        "HomeBrain::todo",
        "HomeBrain::alloff",
        "HomeBrain::getinfo",
        "HomeBrain::isonline",
        "HomeBrain::speedtest",
        "HomeBrain::dbbackup",
        "HomeBrain::dbrestore",
        "HomeBrain::notify",
        "HomeBrain::alert",
        "HomeBrain::speak",
        "HomeBrain::gettemps",
        "HomeBrain::wakecheck",
        "HomeBrain::mobappupdate",
        "HomeBrain::user",
        "HomeBrain::mobappconfig",
        "HomeServer::power",
        "HomeServer::wake",
        "HomeServer::shut",
        "HomeServer::reboot",
        "HomeServer::busy",
        "HomeServer::ison",
        "HomeServer::timetowake",
        "MPD::on",
        "MPD::off",
        "MPD::play",
        "MPD::next",
        "MPD::prev",
        "MPD::playing",
        "MPD::stop",
        "Amp::on",
        "Amp::off",
        "Amp::volup",
        "Amp::volup2",
        "Amp::volup1",
        "Amp::mute",
        "Amp::voldown",
        "Amp::voldown1",
        "Amp::voldown2",
        "Amp::kodi",
        "Amp::mpd",
        "Amp::aux",
        "Amp::movie",
        "Amp::dolby",
        "Amp::music",
        "TV::on",
        "TV::off",
        "TV::power",
        "TV::input",
        "TV::ison",
        "TV::status",
        "KODI::ison",
        "KODI::watch",
        "KODI::on",
        "KODI::off",
        "Medvedi::check",
        "Medvedi::show",
        "Medvedi::notify",
        "Medvedi::timetogame",
        "LAN::checknetwork"
    );

    public function __construct($request, $origin) {
        parent::__construct($request);
    }

    public function __call($name, $args) {
        switch (strtolower($name)) {
            case "hsrv":
            case "hserv":
            case "homeserver":
                $name = "HomeServer";
            break;

            case "hbr":
            case "hbrain":
            case "homebrain":
                $name = "HomeBrain";
            break;

            case "heat":
                $name = "Heating";
            break;

            case "kodi":
                $name = "KODI";
            break;

            case "mpd":
                $name = "MPD";
            break;

            case "tv":
                $name = "TV";
            break;

            case "lan":
               $name = "LAN";
            break;

            default:
                $name = ucfirst(strtolower($name));
        }

        $verb = strtolower((string)$this->verb);


        if ( Auth::OK() ) {
            $ret = "";
            if ( ($verb != 'h' && $verb != 'help') && !self::isCallable($name, $verb) ) {
                $ret .= $name.'::'.$verb." not callable ";
                hbrain_log(__METHOD__, $name.'::'.$verb .' is not callable.');
            }

            if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
            if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";

            if ( $ret == "" ) {
                if (trim($_POST["param1"]) != "" && trim($_POST["param1"]) != "null") {
                    hbrain_log(__METHOD__, $name."::".$this->verb."('".$_POST["param1"]."');");
                    return trim($name::$verb(trim($_POST["param1"])));
                } else {
                    hbrain_log(__METHOD__, $name."::".$this->verb."();");
                    return trim($name::$verb());
                }
            }
            else hbrain_log(__METHOD__, $ret);
        }

        else hbrain_log(__METHOD__, "AUTH not OK.");

        return false;
    }

    public static function isCallable($class, $method) {
        return array_search($class.'::'.$method, self::$callable);
    }

    public static function help($class) {

        $methods = get_class_methods($class);

        $ret = $class .': ';
        foreach ($methods as $method) {
            if (MyApi::isCallable($class, strtolower($method)))
                $ret .= $method .", ";
        }

        return substr($ret, 0, strlen($ret)-2);
    }
}

?>
