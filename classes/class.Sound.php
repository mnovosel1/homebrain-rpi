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
        $soundAvg      = SQLITE::query("SELECT AVG(sound) AS sound
                                        FROM datalog
                                        WHERE DATETIME(timestamp) >= DATETIME('now', '-10 minutes')"
                                    )[0]["sound"];

        $soundMax      = SQLITE::query("SELECT MAX(sound) AS sound
                                        FROM datalog
                                        WHERE DATETIME(timestamp) >= DATETIME('now', '-10 minutes')"
                                    )[0]["sound"];

        $soundLimit   = SQLITE::query("SELECT (round((light/1500), 2)*(20)-40) AS maxsound
                                            FROM datalog
                                            ORDER BY timestamp DESC
                                            LIMIT 1"
                                    )[0]["maxsound"];

        $soundLast   = SQLITE::query("SELECT sound
                                            FROM datalog
                                            ORDER BY timestamp DESC
                                            LIMIT 1"
                                    )[0]["sound"];


        if ($soundMax - $soundAvg > 5) {

            think("Sound numbers for the last 10 minutes (boring stuff): "
                                ."now: ".       round($soundLast, 1) .", "
                                ."avg: ".       round($soundAvg, 1) .", "
                                ."max: ".       round($soundMax, 1) .","
                                ."limit: ".     round($soundLimit, 1) ."."
                            );

            if ($soundLast - $soundLimit > 15) {
                think("It is too loud in here! I'm turning down Amp volume..");
                //Amp::volDown(2);
            }
            else {
                think("It is loud in here, but I'm tolerating it this time..");
            }

            SQLITE::update("states", "active", 1, "name='Sound'");

            return "true";
        }

        think("Silent..");
        SQLITE::update("states", "active", 0, "name='Sound'");

	    return "false";
    }
}

?>
