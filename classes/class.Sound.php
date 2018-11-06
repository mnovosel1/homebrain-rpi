<?php

class Sound {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function isOn() {
	if ( Sound::isLoud() ) {
		debug_log(__METHOD__.":".__LINE__, "Sound is loud..");
			SQLITE::update("states", "active", 1, "`name`='Sound'");
			return "true";
	}

        else {
               SQLITE::update("states", "active", 0, "`name`='Sound'");
               return "false";
        }
    }

    public static function isLoud() {
	$res = SQLITE::query("SELECT AVG(sound) FROM datalog WHERE DATETIME(timestamp) >= DATETIME('now', '-30 minutes')");
	return $res[0]["AVG(sound)"] > Configs::get("SOUND", "MAX");
    }
}

?>
