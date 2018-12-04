<?php

class Heating {
    public static function h() {
        return MyAPI::help(Heating::class);
    }

    public static function help() {
        return MyAPI::help(Heating::class);
    }

    public static function on() {
        exec("/usr/bin/gpio mode 5 out");
    }

    public static function off() {
        exec("/usr/bin/gpio mode 5 in");
    }

    public static function getTemps() {
        return explode("|", file_get_contents(DIR ."/var/lastTemp.dat"));
    }

    public static function getSetTemp() {
        $res = SQLITE::query("SELECT tempinavg AS tempSet FROM tempconf
                        WHERE hour = STRFTIME('%H', DATETIME('now', 'localtime')) * 1
                        AND wday = STRFTIME('%w', DATETIME('now', 'localtime')) * 1");
        return (float) $res[0]["tempSet"];
    }

    public static function getInTemp() {
        return (float) Heating::getTemps()[1];
    }

    public static function getInHumid() {
        return (float) Heating::getTemps()[2];
    }

    public static function getOutTemp() {
        return (float) Heating::getTemps()[3];
    }

    public static function getOutHumid() {
        return (float) Heating::getTemps()[4];
    }

    public static function set() {

    }

    public static function auto() {

    }

    public static function isOn() {
        $isOn       = SQLITE::query("SELECT active FROM states WHERE name = 'Heating'")[0]["active"];
        $temps      = Heating::getTemps();
	    $tempInMax  = Configs::get("TEMP", "MAX");
        $tempSet    = Heating::getSetTemp() + 0.00;
	    $tempSetMax = Configs::get("TEMP", "MAXSET");
        $hyst = Configs::get("TEMP", "HYST");

	if ($tempSet > $tempSetMax) {
		hbrain_log(__METHOD__.":".__LINE__, "tempSet: ". $tempSet ." tempMaxSet: ". $tempSetMax .", correcting.");
		$tempSet = $tempSetMax;
	}

        $logMsg     = "Heating is ";
        $logMsg     .= $isOn > 0 ? "on, ": "off, ";
        $logMsg     .= "tempIn: ".$temps[1]." tempOut: ".$temps[3]." tempSet: ".$tempSet.", ";

        // Heating enabled only if tempIn - tempOut > Temp difference
        if ($temps[1] - $temps[3] <= Configs::get("TEMP", "DIFF")) {
            $logMsg .= "DIFF too low. ";
            if ($isOn > 0) {
                Heating::off();
                $logMsg .= "Switching off. ";
            }
        }

        // Heating enabled only if tempIn - tempOut > Temp difference
        else {
            if ($isOn > 0) { // Heating is on
                if ($temps[1] > $tempInMax) {
                    $logMsg .= "temp over the MAX, switching off. ";
                    Heating::off();
                    $isOn = 0;
                }
                else if (!Heating::isBoosting() && $temps[1] >= $tempSet + $hyst) {
                    $logMsg .= "tempSet reached, switching off. ";
                    Heating::off();
                    $isOn = 0;
                }
                else $logMsg .= "and it stays on. ";
            }

            else {// Heating is off
                if ((Heating::isBoosting() && $temps[1] <= $tempInMax)
			 || $temps[1] <= $tempSet - $hyst) {
                    $logMsg .= "tempIn <= tempSet, switching on. ";
                    Heating::on();
                    $isOn = 1;
                }
                else $logMsg .= "and it stays off. ";
            }
        }

        SQLITE::update("states", "active", $isOn, "name='Heating'");

        hbrain_log(__METHOD__.":".__LINE__, trim($logMsg));

        $red = round($temps[1] - 20, 0, PHP_ROUND_HALF_UP);
        $red = $red < 0 ? 0 : $red;

        $green = $isOn > 0 ? 2 : 0;

        $blue = round($temps[2] - 55, 0, PHP_ROUND_HALF_UP);
        $blue = $blue < 0 ? 0 : $blue;

        if ($green > 0) {
            $green = $red > $green ? $red : $green;
            $green = $blue > $green ? $blue : $green;
        }

	    Notifier::rgb($red, $green, $blue);

        return $isOn;
    }

    public static function isBoosting() {
        hbrain_log(__METHOD__.":".__LINE__, "Not boosting..");
        return false;
    }

    public static function updateMob() {
        $tmp = Heating::getTemps();

        $data["tempSet"]    = sprintf("%01.1f", Heating::getSetTemp());
        $data["tempIn"]     = sprintf("%01.1f", $tmp[1]);
        $data["humidIn"]    = sprintf("%01.1f", $tmp[2]);
        $data["tempOut"]    = sprintf("%01.1f", $tmp[3]);
        $data["humidOut"]   = sprintf("%01.1f", $tmp[4]);

        HomeBrain::mobHeatUpdate(json_encode($data));
    }

}

?>
