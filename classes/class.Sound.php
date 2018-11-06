<?php

class Sound {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function isOn() {
    	return Sound::isLoud();
    }

    public static function isLoud() {
	return true;
    }
}

?>
