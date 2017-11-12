<?php

class MyAPI extends API {

    // Only callable methods are available from HomeBrain CLI
    private static $callable = array (
        "HomeBrain::wakecheck",
        "HomeBrain::user",
        "HomeServer::power",
        "HomeServer::wake",
        "HomeServer::shut",
        "HomeServer::reboot",
        "Notifier::notify",
        "Notifier::appupdate",
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
        
        if ( false && DEBUG ) { ////////////////////////////////////////////////////////////////////////
            
            ob_start();
            
            echo PHP_EOL . date("H:i:s");
            echo PHP_EOL . "IP " . $_SERVER["REMOTE_ADDR"];
            echo PHP_EOL . "AUTH " . Auth::OK() . PHP_EOL;
            
            echo PHP_EOL . "NAME: ";
            var_dump($name);
            
            echo PHP_EOL . "VERB: ";
            var_dump($verb);
        
            echo PHP_EOL . "POST" . PHP_EOL;
            var_dump($_POST);

            $out = ob_get_clean();
            file_put_contents(DIR.'/'.Configs::get("DEBUG_LOG"), $out.PHP_EOL);

        } /////////////////////////////////////////////////////////////////////////////////////

        if ( Auth::OK() ) {            
            $ret = "";
            if ( array_search($name.'::'.$verb, self::$callable) === false ) $ret .= $name.'::'.$verb." not callable ";
            if ( !class_exists($name)  ) $ret .= "no class: ".$name." ";
            if ( !method_exists($name, $verb) ) $ret .= "no method: ".$verb." ";
            if ( $ret == "" ) return $name::$verb();
            
            if ( DEBUG ) return $ret;
            else return null;
        }
    }
}

?>