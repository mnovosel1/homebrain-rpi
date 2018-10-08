<?php

class FinMan {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }


    public static function add() {
        SQLITE::insert("finlog", $attributes, $values);
    }
}

?>