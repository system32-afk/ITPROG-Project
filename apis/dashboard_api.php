<?php
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../dashboard_data.php";

header('Content-Type: application/json');

// TODO: swap for $_SESSION['vendor_id'] once login/auth is wired up.
$vendorId = 1;

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