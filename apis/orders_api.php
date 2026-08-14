<?php
require_once __DIR__ . "/../database.php";

header('Content-Type: application/json');

session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./access_denied.php");
    exit();
}


$vendorId = $_SESSION['vendor_id'];

// Statuses that can be *set* via this endpoint. "pending" is a starting
// state assigned at order creation (out of scope here), not something the
// kitchen admin sets manually, so it's deliberately left out of this list.
const ALLOWED_STATUSES = ['priority', 'preparing', 'done', 'canceled'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'updateStatus':
        updateOrderStatus($conn, $vendorId);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown or missing action."]);
}

/* ============================
   UPDATE STATUS
   Covers Done, Cancel, Process (-> preparing), and Set Priority --
   all four buttons on the order card map to this one action with
   a different target status.
============================ */

function updateOrderStatus(mysqli $conn, int $vendorId): void
{
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!$orderId || !in_array($status, ALLOWED_STATUSES, true)) {
        http_response_code(422);
        echo json_encode(["error" => "Missing order_id or invalid status."]);
        return;
    }

    $stmt = $conn->prepare("UPDATE orders_tbl SET status = ? WHERE order_id = ? AND vendor_id = ?");
    $stmt->bind_param("sii", $status, $orderId, $vendorId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Order not found."]);
        return;
    }

    echo json_encode(["success" => true, "status" => $status]);
}