<?php

class MPD {

    public static function play() {
		if ( $_POST["param1"] == "" ) {
            return self::playing();
        }
        else return null;
    }

    public static function playing() {
        $mpdplay = exec("mpc status | grep playing");        
        return ($mpdplay == "") ? false : true;
    }
}

?>