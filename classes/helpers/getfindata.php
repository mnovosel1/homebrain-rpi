<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
require "simple_html_dom.php";


// DIONICE
$html = file_get_html('https://is.vobco.hr/hr/NoviPodaci/ERNT');

$html = explode("|", $html);

echo "ERNT";
echo ":";
echo $html[2];
echo ":";
echo $html[10];
echo ":";
echo $html[11];
echo PHP_EOL;


// ZDMF ENT
$html = file_get_html('https://www.rmf.hr/default.aspx?id=833');
echo "ZDMFENT:";
echo $html->find('#promjenjivDatum')[0]->innertext;
echo ":";
echo $html->find('#promjenjivaVrijednost')[0]->innertext;
echo PHP_EOL;


// TECAJNA LISTA
$html = file_get_html('https://www.rba.hr/aktualna-tecajna-lista');
$valute = array( "EUR", "USD", "SEK", "CHF", "GBP", "JPY");
foreach ($html->find('tr') as $row) {

    $valuta = trim($row->children(1)->innertext);

    if ( in_array($valuta, $valute) !== false ) {
        echo $valuta;
        echo ":";
        echo $row->children(2)->innertext;
        echo ":";
        echo $row->children(3)->innertext;
        echo ":";
        echo $row->children(4)->innertext;
        echo PHP_EOL;
    }
}

?>