<?php
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../dashboard_data.php";

header('Content-Type: application/json');
session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./access_denied.php");
    exit();
}

session_start();
$vendorId = $_SESSION['vendor_id'];

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        echo json_encode([
            "stats" => getDashboardStats($conn, $vendorId),
            "recentOrders" => getRecentOrders($conn, $vendorId),
            "inventoryAlerts" => getInventoryAlerts($conn, $vendorId),
        ]);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown or missing action."]);
}