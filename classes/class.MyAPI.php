<?php

class MyAPI extends API {

    // Only callable methods are available from HomeBrain CLI (and Web API)
    private static $callable = array (
        "Reg::register",
        "Reg::verify",
        "HomeBrain::wakecheck",
        "HomeBrain::mobappupdate",
        "HomeBrain::user",
        "HomeServer::power",
        "HomeServer::wake",
        "HomeServer::shut",
        "HomeServer::reboot",
        "Notifier::notify",
        "HomeBrain::mobappconfig",
        "MPD::play"
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

            case "hbrain":
            case "homebrain":
                $name = "HomeBrain";
            break;

            case "mpd":
                $name = "MPD";
            break;

            default:
                $name = ucfirst(strtolower($name));
        }

        $verb = strtolower($this->verb);

        if ( Auth::OK() ) {            
            $ret = "";
            if ( array_search($name.'::'.$verb, self::$callable) === false ) $ret .= $name.'::'.$verb." not callable ";
            if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
            if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";
            if ( $ret == "" ) return $name::$verb();
            
            if ( DEBUG ) return $ret;
            else return null;
        }
        
        else {            
            if ( DEBUG ) debug_log("AUTH not OK.");
            header('HTTP/1.0 403 Forbidden');
        }
    }
}

?>