<?php

class IR {
    public static $debug = false;

    public static function amp($command, $count = 1) {        
        self::irsend("irnec", $command, $count);
    }
    
    public static function tv($command, $count = 1) {        
        self::irsend("irsony", $command, $count);
    }

    private static function irsend($command, $parameter, $count = 1) {
        for ($i = 0; $i < $count; $i++)
            exec("sudo /usr/bin/nrf 1 ".$command.":".$parameter);

        debug_log(__FILE__, "sudo /usr/bin/nrf 1 ".$command.":".$parameter);
    }
}

?>