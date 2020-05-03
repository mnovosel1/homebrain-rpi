<?php
date_default_timezone_set('CET');

$counter = 0;
$forecast = file_get_contents("forecast.txt");
$svgOutput = file_get_contents("weather-script-preprocess.svg");
$today = date("Y-m-d", strtotime("today"));
$tomorrow = date("Y-m-d", strtotime("tomorrow"));
$tomorrow2 = date("Y-m-d", strtotime("+2 days"));
$tomorrow3 = date("Y-m-d", strtotime("+3 days"));

$forecast = explode("}\n{", $forecast);
//var_dump($forecast);

$days = array(
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
);
$dani = array(
    "Nedjelja",
    "Ponedjeljak",
    "Utorak",
    "Srijeda",
    "Četvrtak",
    "Petak",
    "Subota"
);

function getIcon($symbol) {

    switch ($symbol) {
        case "Partly cloudy":
            return "bkn";

        case "Clear sky":
            return "skc";

        case "Fair":
            return "few";

        case "Fog":
            return "fg";

        case "Cloudy":
            return "ovc";

        case "Light rain":
        case "Light rain showers":
            return "shra";

        case "Rain":
        case "Rain showers":
        case "Heavy rain":
            return "ra";

        case "Light sleet":
        case "Sleet":
        case "Heavy sleet":
        case "Light sleet showers":
        case "Sleet showers":
        case "Heavy sleet showers":
            return "mix";

        case "Light snow":
        case "Snow":
        case "Heavy snow":
        case "Light snow showers":
        case "Snow showers ":
        case "Heavy snow showers":
            return "sn";

        case "Light rain showers and thunder":
        case "Rain showers and thunder":
        case "Heavy rain showers and thunder":
            return "scttsra";

        case "Light rain and thunder":
            return "tsra";

        default:
            return "";
    }
}

foreach ($forecast as $f) {
    if ($counter == 0) {
        $f = (json_decode($f ."}", true));
    }
    else {
        $f = (json_decode("{". $f ."}", true));
    }

    $dateStr = explode("T", $f["@from"])[0];

    $temps[$dateStr][] = (int)$f["temperature"]["@value"];
    $precs[$dateStr][] = (float)$f["precipitation"]["@value"]*1;

    if ($counter == 0 || $f["@period"] == 1) {
        //var_dump($f);
        $symbol[$dateStr] = $f["symbol"]["@name"];
        $counter++;
    }
}
/*
var_dump($temps);
var_dump($precs);

echo max($precs["2020-04-20"]);
echo min($precs["2020-04-20"]);
*/

$svgOutput = str_replace("TODAY", date("l d.m.", strtotime($today)), $svgOutput);

$haveInfo = false;
if ($haveInfo) {
    $svgOutput = str_replace("INFO_ICO", "calendar", $svgOutput);
    $svgOutput = str_replace("INFO", "Info i čćžšđ ŽĆČŠĐ", $svgOutput);
} else {
    $svgOutput = str_replace("INFO", "", $svgOutput);
}

$svgOutput = str_replace("TEMP_IN", 20.5, $svgOutput);
$svgOutput = str_replace("TEMP_OUT", 24.5, $svgOutput);
$svgOutput = str_replace("HUMID_IN", 55.8, $svgOutput);
$svgOutput = str_replace("HUMID_OUT", 32.4, $svgOutput);

$svgOutput = str_replace("ICON_ONE", getIcon($symbol[$today]), $svgOutput);
$svgOutput = str_replace("ICON_TWO", getIcon($symbol[$tomorrow]), $svgOutput);
$svgOutput = str_replace("ICON_THREE", getIcon($symbol[$tomorrow2]), $svgOutput);
$svgOutput = str_replace("ICON_FOUR", getIcon($symbol[$tomorrow3]), $svgOutput);

$svgOutput = str_replace("TOMORROW", date("l", strtotime($tomorrow)), $svgOutput);
$svgOutput = str_replace("DAY_THREE", date("l", strtotime($tomorrow2)), $svgOutput);
$svgOutput = str_replace("DAY_FOUR", date("l", strtotime($tomorrow3)), $svgOutput);

if (min($temps[$today]) == max($temps[$today])) {
	$lowHighOne = min($temps[$today]);
} else {
	$lowHighOne = min($temps[$today]) ."-". max($temps[$today]);
}

$svgOutput = str_replace("LOW_HIGH_ONE", $lowHighOne, $svgOutput);

if (max($precs[$today]) > 0) {
    $svgOutput = str_replace("PRECIP_ICO_ONE", "drops", $svgOutput);
    $svgOutput = str_replace("PRECIP_ONE", sprintf("%01.1f mm", max($precs[$today])), $svgOutput);
} else {
    $svgOutput = str_replace("PRECIP_ONE", "", $svgOutput);
}
if (max($precs[$tomorrow]) > 0) {
    $svgOutput = str_replace("PRECIP_ICO_TWO", "drops", $svgOutput);
    $svgOutput = str_replace("PRECIP_TWO", sprintf("%01.1f mm", max($precs[$tomorrow])), $svgOutput);
}
else {
    $svgOutput = str_replace("PRECIP_TWO", "", $svgOutput);
}
if (max($precs[$tomorrow2]) > 0) {
    $svgOutput = str_replace("PRECIP_ICO_THREE", "drops", $svgOutput);
    $svgOutput = str_replace("PRECIP_THREE", sprintf("%01.1f mm", max($precs[$tomorrow2])), $svgOutput);
}
else {
    $svgOutput = str_replace("PRECIP_THREE", "", $svgOutput);
}
if (max($precs[$tomorrow3]) > 0) {
    $svgOutput = str_replace("PRECIP_ICO_FOUR", "drops", $svgOutput);
    $svgOutput = str_replace("PRECIP_FOUR", sprintf("%01.1f mm", max($precs[$tomorrow3])), $svgOutput);
}
else {
    $svgOutput = str_replace("PRECIP_FOUR", "", $svgOutput);
}


$svgOutput = str_replace("HIGH_TWO", max($temps[$tomorrow]), $svgOutput);
$svgOutput = str_replace("LOW_TWO", min($temps[$tomorrow]), $svgOutput);

$svgOutput = str_replace("HIGH_THREE", max($temps[$tomorrow2]), $svgOutput);
$svgOutput = str_replace("LOW_THREE", min($temps[$tomorrow2]), $svgOutput);

$svgOutput = str_replace("HIGH_FOUR", max($temps[$tomorrow3]), $svgOutput);
$svgOutput = str_replace("LOW_FOUR", min($temps[$tomorrow3]), $svgOutput);

$svgOutput = str_replace("TIME", date("H:i"), $svgOutput);

//var_dump(json_decode($forecast, true));

//file_put_contents("kdisplay.svg", $svgOutput);

echo str_replace($days, $dani, $svgOutput);

?>
