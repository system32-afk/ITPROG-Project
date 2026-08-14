<?php

require "vendor/autoload.php";
require_once "database.php";
require "vendor/setasign/fpdf/fpdf.php";
session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./access_denied.php");
}

$vendorID = $_GET['id'];
$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();
$vendorResult = $getVendorInfo->get_result();
$vendorInfo = $vendorResult->fetch_assoc();


use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$url = "http://192.168.1.23/ITPROG-Project/menu.php?vendor=" . $vendorID;
$qrCode = new QrCode($url);
$writer = new PngWriter();
$result = $writer->write($qrCode);
$tempQR = "temp/qr_$vendorID.png";
$result->saveToFile($tempQR);




if (isset($_GET['download'])) {

    $pdf = new FPDF();
    $pdf->SetTextColor(50, 114, 253);
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 22);

    $pdf->Cell(
        0,
        12,
        $vendorInfo['store_name'],
        0,
        1,
        'C'
    );

    $pdf->SetFont('Arial', 'B', 18);

    $pdf->Cell(
        0,
        10,
        'Scan to Order',
        0,
        1,
        'C'
    );

    $pdf->Image(
        $tempQR,
        55,
        40,
        100
    );

    $pdf->Ln(110);

    $pdf->SetFont('Arial', '', 13);

    $pdf->Cell(
        0,
        8,
        'Open your camera and scan the QR code.',
        0,
        1,
        'C'
    );

    $pdf->SetFont('Arial', 'I', 10);

    $pdf->Cell(
        0,
        8,
        'Powered by Pabilli',
        0,
        1,
        'C'
    );

    $pdf->Output(
        'D',
        'RestaurantQR.pdf'
    );

    if (file_exists($tempQR)) {
        unlink($tempQR);
    }

    exit;
}


// only output QR image when NOT downloading
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
?>