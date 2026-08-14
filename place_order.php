<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once "database.php";
require_once "apis/cart_functions.php";
$data = json_decode(file_get_contents("php://input"), true);

$vendorID = $data["vendorID"];
$orderNumber = $data["orderNumber"];
$orderType = $data["orderType"];
$tableNumber = $data["tableNumber"];
$paymentMethod = $data["paymentMethod"];
$total = calculateCartTotal($conn, $data["items"]);
$customerName = $data["customer"]["name"];
$customerContact = $data["customer"]["contact"];
$items = $data["items"];

if ($paymentMethod === "Cash") {
    $verificationCode = random_int(1000, 9999);
    $status = "Awaiting Payment";
} else {
    $verificationCode = null;
    $status = "Pending";
}

//check first if the item is enabled

foreach ($items as $item) {
    $checkstatus = $conn->prepare("SELECT item_id FROM menuitems_tbl WHERE item_id = ? AND vendor_id = ? AND is_enabled = 1");
    $checkstatus->bind_param("ii", $item["id"], $vendorID);
    $checkstatus->execute();

    if ($checkstatus->get_result()->fetch_assoc() === null) {
        echo json_encode([
            "success" => false,
            "message" => "An item in your cart does not exist or is not active."
        ]);
        exit();
    }
}

$conn->begin_transaction();

try {

    $insertToOrdersTBL = $conn->prepare("
        INSERT INTO orders_tbl
        (
            vendor_id,
            order_number,
            order_type,
            table_number,
            customer_name,
            customer_contact,
            payment_method,
            total,
            status,
            verification_code
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertToOrdersTBL->bind_param(
        "ississsdss",
        $vendorID,
        $orderNumber,
        $orderType,
        $tableNumber,
        $customerName,
        $customerContact,
        $paymentMethod,
        $total,
        $status,
        $verificationCode
    );

    $insertToOrdersTBL->execute();
    $orderId = $conn->insert_id;


    $stmt = $conn->prepare("
        INSERT INTO orderitems_tbl
        (order_id, item_id, instructions, quantity)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $stmt->bind_param(
            "issi",
            $orderId,
            $item["id"],
            $item["notes"],
            $item["quantity"],
        );

        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "orderId" => $orderId,
        "paymentMethod" => $paymentMethod,
        "total" => $total,
        "message" => "Order created successfully."
    ]);

} catch (Exception $e) {
    // Rollback changes on failure
    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>