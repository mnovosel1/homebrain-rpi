<?php

abstract class HomeBrain {

    public static function debug() {
        return MyAPI::debug(self::class);
    }

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }
}

?>