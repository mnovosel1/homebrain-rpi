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

        $miData = explode("|", exec("cat ". DIR ."/var/mitemps.log"));
        //debug_log(__METHOD__.":".__LINE__, implode("|", $miData));
        
        exec(DIR ."/classes/helpers/mitemp/getmitemps.py &");

        $oldData = SQLITE::query("SELECT tempin, humidin, light, sound
                                    FROM datalog
                                     WHERE tempin != 'NULL'
                                      AND humidin != 'NULL'
                                      AND light != 'NULL'
                                      AND sound != 'NULL'
                                     ORDER BY timestamp DESC LIMIT 1");
        //MQTTclient::publish("hassio/sens1/", '{ "temperature": '. $oldData[0]["tempin"] .', "humidity": '. $oldData[0]["humidin"] .', "light": '. (int)$oldData[0]["light"] .', "sound": '. $oldData[0]["sound"] .' }');

        $newData = exec("sudo ". DIR ."/bin/nrf 1 sens");
        $newData = explode(":", $newData);

        //debug_log(__METHOD__.":".__LINE__, $oldData);
        //debug_log(__METHOD__.":".__LINE__, $newData);

        $tempIn = abs($oldData[0]["tempin"] - $newData[0]) > 15 ? $oldData[0]["tempin"] : $newData[0];
        $humidIn = abs($oldData[0]["humidin"] - $newData[1]) > 25 ? $oldData[0]["humidin"] : $newData[1];
        $light = $newData[2] == 0 || abs($oldData[0]["light"] - $newData[2]) > 750 ? (int)$oldData[0]["light"] : (int)$newData[2];
        $sound = abs($oldData[0]["sound"] - $newData[3]) > 50 ? $oldData[0]["sound"] : $newData[3];
        //$sound = $newData[3];

        MQTTclient::publish("hassio/sens1/", '{ "temperature": '. $tempIn .', "humidity": '. $humidIn .', "light": '. $light .', "sound": '. $sound .' }');

        $tempLivingRoom = empty($miData[4]) ? 'NULL' : $miData[4];
        $humidLivingRoom = empty($miData[5]) ? 'NULL' : $miData[5];
        $tempBahtRoom = empty($miData[8]) ? 'NULL' : $miData[8];
        $humidBathRoom = empty($miData[9]) ? 'NULL' : $miData[9];

        $ret = ($tempIn - 1) .":". $humidIn .":". $light .":". $sound .":". $tempLivingRoom .":". $humidLivingRoom .":". $tempBahtRoom .":". $humidBathRoom;
        //debug_log(__METHOD__.":".__LINE__, $ret);

        return $ret;
    }

    public static function heatIndex($temperature, $humidity) {
        $tempF = 1.80 * $temperature + 32.0;
        $heatIndex = -42.379 + 2.04901523 * $tempF + 10.14333127 * $humidity - 0.22475541 * $tempF * $humidity - 6.83783 * (pow(10, -3)) * (pow($tempF, 2)) - 5.481717 * (pow(10, -2)) * (pow($humidity, 2)) + 1.22874 * (pow(10, -3)) * (pow($tempF, 2)) * $humidity + 8.5282 * (pow(10, -4)) * $tempF * (pow($humidity, 2)) - 1.99 * (pow(10, -6)) * (pow($tempF, 2)) * (pow($humidity,2));
        $heatIndex = round(($heatIndex - 32) * 0.556, 1);

        return $heatIndex;
    }

    public static function tempOut($timestamp = null) {
        require DIR ."/classes/helpers/simple_html_dom.php";
        if ($timestamp === null) $timestamp = date("Y-m-d H:i:00");

        $html = file_get_html('http://meteo.hr/podaci.php?section=podaci_vrijeme&prikaz=abc');

        if ($html !== false) {
            foreach ($html->find('tr') as $row) {
                if (trim($row->children(0)->innertext) == "Zagreb-Maksimir") {
                    $wind = $row->children(1);
                    $temp = $row->children(2);
                    $humid = $row->children(3);
                }
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
