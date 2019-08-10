<?php

require "simple_html_dom.php";

$html = file_get_html('http://meteo.hr/podaci.php?section=podaci_vrijeme&prikaz=abc');

foreach ($html->find('tr') as $row) {
    if (trim($row->children(0)->innertext) == "Zagreb-Maksimir") {
        $wind = $row->children(1);
        $temp = $row->children(2);
        $humid = $row->children(3);
    }
}

echo $temp->innertext;
echo ":";
echo $humid->innertext;
echo ":";
echo $wind->children(0)->innertext;
echo ":";
echo $wind->children(1)->innertext;

?>