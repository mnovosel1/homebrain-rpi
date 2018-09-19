<?php

class Weather {
    public static $debug = true;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }
    
    public static function tempIn() {
        $timestamp = date("Y-m-d H:i:00");

        $oldData = SQLITE::fetch("datalog", ["tempin", "humidin", "light", "sound"], "tempin != 'NULL' AND humidin != 'NULL' ORDER BY timestamp DESC LIMIT 1");

        $newData = exec("sudo ". DIR ."/bin/nrf 1 sens");
        $newData = explode(":", $newData);

        $tempIn = abs($oldData[0]["tempin"] - $newData[0]) > 10 ? $oldData[0]["tempin"] : $newData[0];
        $humidIn = abs($oldData[0]["humidin"] - $newData[1]) > 20 ? $oldData[0]["humidin"] : $newData[1];
        $light = abs($oldData[0]["light"] - $newData[2]) > 1000 ? $oldData[0]["light"] : $newData[2];
        $sound = abs($oldData[0]["sound"] - $newData[3]) > 40 ? $oldData[0]["sound"] : $newData[3];

        SQLITE::insert("datalog",
                        ["timestamp",
                        "tempset",
                        "tempin",
                        "tempout",
                        "heatingon",
                        "humidin",
                        "humidout",
                        "light",
                        "sound"],
                        ["'". $timestamp ."'",
                        "(SELECT tempinavg FROM tempconf
                                    WHERE hour = STRFTIME('%H', DATETIME('now', 'localtime')) * 1
                                        AND wday = STRFTIME('%w', DATETIME('now', 'localtime')) * 1)",
                        $tempIn,
                        "(SELECT tempout FROM datalog WHERE timestamp = '". $timestamp ."')",
                        "(SELECT active FROM states WHERE name = 'Heating')",
                        $humidIn,
                        "(SELECT humidout FROM datalog WHERE timestamp = '". $timestamp ."')",
                        $light,
                        $sound],
                        true);

        if ($sound > -20) HomeBrain::notify("Buka !!");

        return $tempIn .":". $humidIn .":". $light .":". $sound;
    }

    public static function tempOut() {
        require DIR ."/classes/helpers/simple_html_dom.php";
        $timestamp = date("Y-m-d H:i:00");

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

        $oldData = SQLITE::fetch("datalog", ["tempout", "humidout"], "tempout != 'NULL' AND humidout != 'NULL' ORDER BY timestamp DESC LIMIT 1");

        $tempOut = abs($tempOut - $oldData[0]["tempout"]) > 5 ? $oldData[0]["tempout"] : $tempOut;
        $humidOut = abs($humidOut - $oldData[0]["humidout"]) > 5 ? $oldData[0]["humidout"] : $humidOut;

        SQLITE::insert("datalog",
                        ["timestamp",
                        "tempset",
                        "tempin",
                        "tempout",
                        "heatingon",
                        "humidin",
                        "humidout",
                        "light",
                        "sound"],
                        ["'". $timestamp ."'",
                        "(SELECT tempset FROM datalog WHERE timestamp = '". $timestamp ."')",
                        "(SELECT tempin FROM datalog WHERE timestamp = '". $timestamp ."')",
                        $tempOut,
                        "(SELECT active FROM states WHERE name = 'Heating')",
                        "(SELECT humidin FROM datalog WHERE timestamp = '". $timestamp ."')",
                        $humidOut,
                        "(SELECT light FROM datalog WHERE timestamp = '". $timestamp ."')",
                        "(SELECT sound FROM datalog WHERE timestamp = '". $timestamp ."')"],
                        true);

        return $tempOut .":". $humidOut .":". $wind1 .":". $wind2;
    }
}

?>