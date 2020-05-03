<?php

$im = new Imagick();
$svg = file_get_contents("http://hbrain/kdisplay");

$im->readImageBlob($svg);

$im->setImageFormat("png8");
//$im->resizeImage(720, 445, imagick::FILTER_LANCZOS, 1);  /*Optional, if you need to resize*/

$im->writeImage('blank-us-map.png');

header('Content-type: image/png');
echo $im;

$im->clear();
$im->destroy();

?>