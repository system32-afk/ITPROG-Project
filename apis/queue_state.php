<?php
require_once "../database.php";

session_start();
header("Content-Type: application/json");

// check if user has logged in
if (!isset($_SESSION["vendor_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit();
}

$vendorID = $_SESSION["vendor_id"];

$stmt = $conn->prepare(
    "SELECT order_id, customer_name, customer_contact, table_number, payment_method, status, target_minutes, created_at
    FROM orders_tbl
    WHERE vendor_id = ? AND status NOT IN ('done','canceled')
    ORDER BY created_at ASC"
);
$stmt->bind_param("i", $vendorID);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$itemStmt = $conn->prepare(
    "SELECT oi.order_item_id, oi.item_id, oi.quantity, oi.Instructions, m.price, m.name, m.station, o.verification_code, o.status, o.order_number
    FROM orderitems_tbl oi
    JOIN menuitems_tbl m ON oi.item_id = m.item_id
    JOIN orders_tbl o ON oi.order_id = o.order_id
    WHERE oi.order_id = ?"
);

$liveOrders = [];
$delayedCount = 0;
$now = time();

foreach ($orders as $order) {
    $itemStmt->bind_param("i", $order['order_id']);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $createdTs = strtotime($order['created_at']);
    $elapsedMinutes = max(0, (int) floor(($now - $createdTs) / 60));
    $overrunMinutes = $elapsedMinutes - (int) $order['target_minutes'];
    $isDelayed = $overrunMinutes > 0;

    // Same "Delayed" display-only override as livequeue.php -- the real
    // status column (pending/priority/preparing) is left untouched.
    $displayStatus = $isDelayed ? "Delayed" : ucfirst($order['status']);

    $liveOrders[] = [
        "order_id" => (int) $order['order_id'],
        "order_number" => $items[0]['order_number'] ?? null,
        "customer_name" => $order['customer_name'],
        "customer_contact" => $order['customer_contact'],
        "table_number" => $order['table_number'],
        "payment_method" => $order['payment_method'],
        "status" => $items[0]['status'] ?? null,
        "display_status" => $displayStatus,
        "elapsed_display" => $elapsedMinutes . "m Ago",
        "target_display" => $order['target_minutes'] . "m",
        "overrun_display" => "Exceeded by " . $overrunMinutes . "m",
        "quantity_total" => array_sum(array_column($items, 'quantity')),
        "order_total" => array_sum(array_map(
            fn($item) => (float) $item['price'] * (int) $item['quantity'],
            $items
        )),
        "verification_code" => $items[0]['verification_code'] ?? null,
        "items" => array_map(function ($item) {
            return [
                "quantity" => (int) $item['quantity'],
                "name" => $item['name'],
                "station" => $item['station'],
                "price" => (float) $item['price'],
                "Instructions" => $item['Instructions'],
            ];
        }, $items),
    ];

    if ($isDelayed) {
        $delayedCount++;
    }
}

echo json_encode([
    "activeOrdersCount" => count($liveOrders),
    "delayedCount" => $delayedCount,
    "orders" => $liveOrders,
]);