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
	    $sound      = SQLITE::query("SELECT AVG(sound) 
                                        FROM datalog 
                                        WHERE DATETIME(timestamp) >= DATETIME('now', '-10 minutes')"
                                    )[0]["AVG(sound)"];

        $soundMax   = SQLITE::query("SELECT round((light/1500), 2)*(20)-40 AS maxsound
                                         FROM datalog
                                         ORDER BY timestamp DESC
                                         LIMIT 1"
                                    )[0]["maxsound"];

        if ($sound > $soundMax) {
            HomeBrain::notify(date("H:i") ." Sound: ". round($sound, 1) ." max: ". round($soundMax, 1));
            Amp::volDown(1);
            return true;
        }

	    return false;
    }
}

?>
