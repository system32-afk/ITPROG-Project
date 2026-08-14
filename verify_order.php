<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once "database.php";

$data = json_decode(file_get_contents("php://input"), true);
session_start();


$orderId = $data['orderId'] ?? null;
$verificationCode = $data['verificationCode'] ?? null;

if (!$orderId || !$verificationCode) {
    echo json_encode([
        "success" => false,
        "message" => "Order ID and code are required."
    ]);
    exit;
}

try {

    $verify = $conn->prepare("SELECT order_id
    FROM orders_tbl
    WHERE order_id = ? AND verification_code = ? AND status = 'Awaiting Payment'");

    $verify->bind_param("ii", $orderId, $verificationCode);
    $verify->execute();

    $result = $verify->get_result();

    //order is verified
    if ($result->num_rows === 1) {
        $update = $conn->prepare(" UPDATE orders_tbl SET status = 'Pending' WHERE order_id = ?");
        $update->bind_param("i", $orderId);
        $update->execute();

        echo json_encode([
            "success" => true,
            "message" => "Order verified successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid verification code. Please check and try again."
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>