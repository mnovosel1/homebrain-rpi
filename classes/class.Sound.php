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
			SQLITE::update("states", "active", 1, "name='Sound'");
			return "true";
	}

        else {
               SQLITE::update("states", "active", 0, "name='Sound'");
               return "false";
        }
    }

    public static function isLoud() {
	$sound = SQLITE::query("SELECT AVG(sound) FROM datalog WHERE DATETIME(timestamp) >= DATETIME('now', '-5 minutes')")[0]["AVG(sound)"];
        $soundMax = SQLITE::query("SELECT round((light/1500), 2)*(15)-40 AS maxsound FROM datalog ORDER BY timestamp DESC LIMIT 1")[0]["maxsound"];

        if ($sound > $soundMax) {
            HomeBrain::notify(date("H:i") ." Sound: ". $sound);
            Amp::volDown(3);
            return true;
        }

	return false;
    }
}

?>
