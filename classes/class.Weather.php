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
        return exec("sudo ". DIR ."/bin/nrf 1 sens");
    }

    public static function tempOut() {
        require DIR ."/classes/helpers/simple_html_dom.php";

        $html = file_get_html('http://meteo.hr/podaci.php?section=podaci_vrijeme&prikaz=abc');

        foreach ($html->find('tr') as $row) {
            if (trim($row->children(0)->innertext) == "Zagreb-Maksimir") {
                $wind = $row->children(1);
                $temp = $row->children(2);
                $humid = $row->children(3);
            }
        }

        $ret = "";
        $ret .= $temp->innertext;
        $ret .= ":";
        $ret .= $humid->innertext;
        $ret .= ":";
        $ret .= $wind->children(0)->innertext;
        $ret .= ":";
        $ret .= $wind->children(1)->innertext;

        return $ret;
    }
}

?>