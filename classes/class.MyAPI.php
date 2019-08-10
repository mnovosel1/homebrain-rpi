<?php

class MyAPI extends API {
    public static $debug = true;

    // Only callable methods are available from HomeBrain CLI (and Web API)
    private static $callable = array (
        "Reg::verify",
        "Reg::register",
        "HomeBrain::alarm",
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
        "HomeBrain::temps",
        "HomeBrain::wakecheck",
        "HomeBrain::mobappupdate",
        "HomeBrain::user",
        "HomeBrain::mobappconfig",
        "HomeBrain::uploaddata",
        "HomeBrain::email",
        "HomeBrain::wifi",
        "HomeBrain::debug",
        "HomeBrain::killinet",
        "HomeBrain::allowinet",
        "HomeServer::power",
        "HomeServer::wake",
        "HomeServer::shut",
        "HomeServer::reboot",
        "HomeServer::busy",
        "HomeServer::ison",
        "HomeServer::timetowake",
        "HomeServer::keepon",
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
        "TV::kodi",
        "TV::ison",
        "TV::status",
        "KODI::ison",
        "KODI::watch",
        "KODI::on",
        "KODI::off",
        "Medvedi::check",
        "Medvedi::show",
        "Medvedi::timetogame",
        "LAN::checknetwork",
        "LAN::killinet",
        "LAN::allowinet",
        "FinMan::add",
        "Notifier::kodi",
        "Notifier::rgb",
	"Sound::isloud",
	"Sound::ison"
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

            case "finman":
                $name = "FinMan";
            break;

            default:
                $name = ucfirst(strtolower($name));
        }

        $verb = strtolower((string)$this->verb);


        if ( Auth::OK() ) {
            $ret = "";
            $cliMethodName = "";

            if ( ($verb != 'h' && $verb != 'help') && !self::isCallable($name, $verb) ) {
                $ret .= $name.'::'.$verb." not callable ";
                hbrain_log(__METHOD__.":".__LINE__, $name.'::'.$verb .' is not callable.');
            }

            if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
            if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";

            if ( $ret == "" ) {
                if (trim($_POST["param2"]) != "" && trim($_POST["param2"]) != "null") {
                    $cliMethodName = $name."::".$this->verb."('".$_POST["param1"]."', '".$_POST["param2"]."')";
                    $ret = $name::$verb(trim($_POST["param1"]), trim($_POST["param2"]));
                }
                else if (trim($_POST["param1"]) != "" && trim($_POST["param1"]) != "null") {
                    $cliMethodName = $name."::".$this->verb."('".$_POST["param1"]."')";
                    $ret = $name::$verb(trim($_POST["param1"]));
                } else {
                    $cliMethodName = $name."::".$this->verb."()";
                    $ret = $name::$verb();
                }
            }

            if ( is_array($ret) ) $ret = export_var($ret, true);
	    if ( is_bool($ret) ) $ret = $ret ? "true" : "false";
            $ret = trim($ret);

            hbrain_log("API ". $cliMethodName." MyAPI:".__LINE__, '$ret='. substr($ret, 0, 100));

            if ($ret != "") {
                return $ret;
            }
        }

        else hbrain_log(__METHOD__.":".__LINE__, "AUTH not OK.");

        return false;
    }

    public static function isCallable($class, $method) {
	    debug_log(__METHOD__.":".__LINE__, $class.'::'.$method);
        return (array_search($class.'::'.$method, MyAPI::$callable) !== false);
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
