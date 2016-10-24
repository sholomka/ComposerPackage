<?php
declare(strict_types=1);
require_once "vendor/autoload.php";

$class = new \Sholomka\Image\Image();
$class->getImages("http://pattaya.zagranitsa.com/");
