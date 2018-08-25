<?php

class MyAPI extends API {
    public static $debug = true;

    // Only callable methods are available from HomeBrain CLI (and Web API)
    private static $callable = array (
        "Reg::register",
        "Reg::verify",
        "HomeBrain::dbbackup",
        "HomeBrain::dbrestore",
        "HomeBrain::notify",
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
        "MPD::play",
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
        "KODI::on",
        "KODI::off",
        "Heating::gettemps",
        "Heating::getintemp",
        "Heating::getinhumid",
        "Heating::getouttemp",
        "Heating::getouthumid",
        "Heating::updatemob",
        "Medvedi::check",
        "Medvedi::show",
        "Medvedi::notify",
        "Weather::get"
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

            case "Tv":
            case "tV":
            case "tv":
                $name = "TV";
            break;

            default:
                $name = ucfirst(strtolower($name));
        }

        $verb = strtolower((string)$this->verb);

        

        if ( Auth::OK() ) {            
            $ret = "";
            if ( array_search($name.'::'.$verb, self::$callable) === false ) $ret .= $name.'::'.$verb." not callable ";
            if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
            if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";

            if ( $ret == "" ) {
                if (trim($_POST["param1"]) != "" && trim($_POST["param1"]) != "null") {
                    hbrain_log(__FILE__, $name."::".$this->verb."('".$_POST["param1"]."');");
                    return $name::$verb(trim($_POST["param1"]));
                } else {
                    hbrain_log(__FILE__, $name."::".$this->verb."();");
                    return $name::$verb();
                }
            }
            else hbrain_log(__FILE__, $ret);
        }
        
        else hbrain_log(__FILE__, "AUTH not OK.");

        return false;
    }
}

?>