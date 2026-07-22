<?php 

require "vendor/autoload.php";

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$qrCode = new QrCode(
    'https://www.youtube.com/watch?v=8xPWPGxL7Xk'
);

$writer = new PngWriter();

$result = $writer->write($qrCode);

header('Content-Type: '.$result->getMimeType());
echo $result->getString();

?>