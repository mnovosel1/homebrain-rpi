<?php

class FinMan {

    public static function h() {
        return HomeBrain::help(self::class);
    }

    public static function help() {
        return HomeBrain::help(self::class);
    }


    public static function add() {
        SQLITE::insert("finlog", $attributes, $values);
    }
}

?>