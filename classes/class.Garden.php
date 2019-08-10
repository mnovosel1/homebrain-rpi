<?php

class Garden {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(Garden::class);
    }

    public static function help() {
        return MyAPI::help(Garden::class);
    }
}

?>