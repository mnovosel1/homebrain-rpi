<?php

class Weather {

    public static function h() {
        return MyAPI::help(Weather::class);
    }

    public static function help() {
        return MyAPI::help(Weather::class);
    }

    public static function tempIn($timestamp = null) {
        if ($timestamp === null) $timestamp = date("Y-m-d H:i:00");

        $oldData = SQLITE::query("SELECT tempin, humidin, light, sound 
                                    FROM datalog WHERE tempin != 'NULL'
                                     AND humidin != 'NULL' 
                                     ORDER BY timestamp DESC LIMIT 1");

        $newData = exec("sudo ". DIR ."/bin/nrf 1 sens");
        $newData = explode(":", $newData);

        $tempIn = abs($oldData[0]["tempin"] - $newData[0]) > 10 ? $oldData[0]["tempin"] : $newData[0];
        $humidIn = abs($oldData[0]["humidin"] - $newData[1]) > 20 ? $oldData[0]["humidin"] : $newData[1];
        $light = abs($oldData[0]["light"] - $newData[2]) > 1000 ? $oldData[0]["light"] : $newData[2];
        $sound = abs($oldData[0]["sound"] - $newData[3]) > 40 ? $oldData[0]["sound"] : $newData[3];
/*
        if (!HomeBrain::isSilentTime() && $sound > Configs::get("SOUND", "MAX")) {
            HomeBrain::notify(date("H:i") ." Sound: ". $sound);
            Notifier::alert(5);
        } else if (HomeBrain::isSilentTime() && $sound > Configs::get("SOUND", "MAX_WHEN_SILENT_TIME")) {
            HomeBrain::notify(date("H:i") ." SilentTime sound: ". $sound);
        }
*/
        $soundMax = SQLITE::query("SELECT round((light/1500), 2)*(10)-40 AS maxsound FROM datalog ORDER BY timestamp DESC LIMIT 1")[0]["maxsound"];

        if ($sound > $soundMax) {
            HomeBrain::notify(date("H:i") ." Sound: ". $sound);
            Amp::volDown(5);
        }

        return $tempIn .":". $humidIn .":". $light .":". $sound;
    }

    public static function tempOut($timestamp = null) {
        require DIR ."/classes/helpers/simple_html_dom.php";
        if ($timestamp === null) $timestamp = date("Y-m-d H:i:00");

        $html = file_get_html('http://meteo.hr/podaci.php?section=podaci_vrijeme&prikaz=abc');

        foreach ($html->find('tr') as $row) {
            if (trim($row->children(0)->innertext) == "Zagreb-Maksimir") {
                $wind = $row->children(1);
                $temp = $row->children(2);
                $humid = $row->children(3);
            }
        }

        $tempOut = $temp->innertext;
        $humidOut = $humid->innertext;
        $wind1 = $wind->children(0)->innertext;
        $wind2 = $wind->children(1)->innertext;

        $oldData = SQLITE::query("SELECT tempout, humidout
                                    FROM datalog WHERE tempout != 'NULL'
                                     AND humidout != 'NULL'
                                     ORDER BY timestamp DESC
                                     LIMIT 1");

        $tempOut = abs($tempOut - $oldData[0]["tempout"]) > 5 ? $oldData[0]["tempout"] : $tempOut;
        $humidOut = abs($humidOut - $oldData[0]["humidout"]) > 5 ? $oldData[0]["humidout"] : $humidOut;

        return $tempOut .":". $humidOut .":". $wind1 .":". $wind2;
    }
}

?>
