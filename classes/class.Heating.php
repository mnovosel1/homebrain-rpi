<?php

class Heating {
    public static function h() {
        return MyAPI::help(Heating::class);
    }

    public static function help() {
        return MyAPI::help(Heating::class);
    }

    public static function on() {
        exec(DIR ."/bin/green 2");
        exec("/usr/bin/gpio mode 5 out");
        SQLITE::update("states", "active", 1, "name='Heating'");
    }

    public static function off() {
        exec(DIR ."/bin/green 0");
        exec("/usr/bin/gpio mode 5 in");
        SQLITE::update("states", "active", 0, "name='Heating'");
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
        $light      = SQLITE::query("SELECT light FROM datalog ORDER BY timestamp DESC LIMIT 1")[0]["light"];
        $temps      = Heating::getTemps();
        $tempSet    = Heating::getSetTemp() + 1.0;

        $logMsg     = "Heating is ";
        $logMsg     .= $isOn > 0 ? "on.": "off. ";
        $logMsg     .= "tempIn: ".$temps[1]." tempOut: ".$temps[3]." tempSet: ".$tempSet.". ";

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
                hbrain_log(__METHOD__.":".__LINE__, "Heating is  is on...");
                if ($temps[1] > Configs::get("TEMP", "MAX")) {
                    $logMsg .= "Temp over the MAX, switching off. ";
                    Heating::off();
                }
                else if ($temps[1] >= $tempSet + Configs::get("TEMP", "HYST")) {
                    $logMsg .= "TempSet reached, switching off. ";
                    Heating::off();
                }
                else $logMsg .= "And it stays on. ";
            }

            else {// Heating is off
                if ($temps[1] <= $tempSet) {
                    $logMsg .= "TempIn close to tempSet, switching on. ";
                    Heating::on();
                }
                else $logMsg .= "And it stays off. ";
            }
        }

        $isOn = SQLITE::query("SELECT active FROM states WHERE name = 'Heating'")[0]["active"];

        hbrain_log(__METHOD__.":".__LINE__, trim($logMsg));

        $red    = 0;
        $green  = 0;
        $blue   = 0;

        if ($light > Configs::get("LIGHT", "MIN") && !HomeBrain::isSilentTime())  {

            $red = round($temps[1] - 20, 0, PHP_ROUND_HALF_UP) + round($light/100)-1;
            $red = $red < 0 ? 0 : $red;
            $red = $red > 255 ? 255 : $red;

            $green = $isOn > 0 ? 2 + round($light/100)-1 : 0;

            $blue = round($temps[2] - 54, 0, PHP_ROUND_HALF_UP) + round($light/100)-1;
            $blue = $blue < 0 ? 0 : $blue;
            $blue = $blue > 255 ? 255 : $blue;

            if ($green > 0) {
                $green = $red > $green ? $red : $green;
                $green = $blue > $green ? $blue : $green;
            }
        }

	Notifier::rgb($red, $green, $blue);

        return $isOn;
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
