<?php

require_once "helpers/functions.php";

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
        "HomeBrain::clock",
        "HomeServer::power",
        "HomeServer::wake",
        "HomeServer::shut",
        "HomeServer::reboot",
        "HomeServer::busy",
        "HomeServer::ison",
        "HomeServer::timetowake",
        "HomeServer::keepon",
        "HomeServer::auto",
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
        "Amp::mute",
        "Amp::voldown",
        "Amp::tv",
        "Amp::kodi",
        "Amp::mpd",
        "Amp::aux",
        "Amp::movie",
        "Amp::dolby",
        "Amp::music",
        "IPTV::on",
        "IPTV::off",
        "IPTV::ison",
        "IPTV::sendkey",
        "TV::on",
        "TV::off",
        "TV::watch",
        "TV::power",
        "TV::iptv",
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
        "Notifier::notifyclock1",
        "Sound::isloud",
        "Sound::ison",
        "Light::on",
        "Light::off",
        "Person::setstate"
    );

    public function __construct($request, $origin) {
        parent::__construct($request);
    }

    public function __call($name, $args) {
        $name = getClassName($name);
        $verb = strtolower((string)$this->verb);

        $ret = "";
        $cliMethodName = "";

        if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
        if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";

        if ( $ret == "" )
        {
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

    public static function help($class) {

        $methods = get_class_methods($class);

        $ret = $class .': ';
        foreach ($methods as $method) {
            $ret .= $method .", ";
        }

        return substr($ret, 0, strlen($ret)-2);
    }
}

?>
