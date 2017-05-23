<?php
error_reporting(E_ERROR | E_PARSE);

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, "http://vrijeme.hr/aktpod.php?id=hrvatska1_n");
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($handle);
curl_close($handle);
	
	//var_dump($html);
	
$newDom = new domDocument;

$newDom->loadHTML($html);
$newDom->preserveWhiteSpace = false;
$cells = $newDom->getElementsByTagName('td');

$nodeNo = $cells->length;

for($i=0; $i<$nodeNo; $i++) {
    $currValue = trim($cells->item($i)->nodeValue);
	
	if ( strpos($currValue, "Zagreb-Maksimir") !== false )
	{
		$currValues = explode(PHP_EOL, $currValue);
		foreach ( $currValues as $key => $val )
		{
			if ( trim($val) == "Stanje vremena" )
			{
				echo trim($currValues[$key+4]);
				break 2;
			}
		}
	}
}