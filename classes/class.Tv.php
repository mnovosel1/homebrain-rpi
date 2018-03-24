<?php

class Tv {
     
    public static function power() {
        exec("sudo /usr/bin/nrf 1 irsony:0A90");
    }

    public static function input() {
        exec("sudo /usr/bin/nrf 1 irsony:0A50");
    }
}

?>