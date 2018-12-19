<?php

class Sound {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function isOn() {
	$soundIsLoud = Sound::isLoud();

	if ($soundIsLoud == 'true') {
		SQLITE::update("states", "active", 1, "name='Sound'");
	}

	else {
		SQLITE::update("states", "active", 0, "name='Sound'");
	}

	return $soundIsLoud;
    }

    public static function isLoud() {
	    $soundAvg      = SQLITE::query("SELECT AVG(sound)
                                        FROM datalog
                                        WHERE DATETIME(timestamp) >= DATETIME('now', '-10 minutes')"
                                    )[0]["AVG(sound)"];

        $soundMax   = SQLITE::query("SELECT round((light/1500), 2)*(20)-40 AS maxsound
                                         FROM datalog
                                         ORDER BY timestamp DESC
                                         LIMIT 1"
                                    )[0]["maxsound"];

        $soundLast   = SQLITE::query("SELECT sound
                                         FROM datalog
                                         ORDER BY timestamp DESC
                                         LIMIT 1"
                                    )[0]["sound"];

	$soundDiff = round($soundLast - $soundAvg, 1);

        if ($soundDiff > (0.5)) {
            HomeBrain::notify(date("H:i") ." Sound: ". round($soundLast, 1) ." avg: ". round($soundAvg, 1) ." diff: ". $soundDiff ." max: " . round($soundMax, 1));

	   if ($soundLast - $soundMax > (0.5)) {
		Amp::volDown(2);
	   }

            return "true";
        }

	    return "false";
    }
}

?>
