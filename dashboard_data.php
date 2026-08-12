<?php
/* ============================
   Shared dashboard query helpers.
   Used by both admindashboard.php (initial render) and
   dashboard_api.php (polling) so the two never drift apart.
============================ */

function getDashboardStats(mysqli $conn, int $vendorId): array
{
    $ordersTodayStmt = $conn->prepare(
        "SELECT COUNT(*) AS total FROM orders_tbl WHERE vendor_id = ? AND DATE(created_at) = CURDATE()"
    );
    $ordersTodayStmt->bind_param("i", $vendorId);
    $ordersTodayStmt->execute();
    $ordersToday = (int) $ordersTodayStmt->get_result()->fetch_assoc()['total'];

    // Counts all of today's orders except cancelled ones -- payment is
    // typically taken at order time (GCash) or on pickup (Cash) regardless
    // of whether the kitchen has finished preparing it yet.
    $revenueTodayStmt = $conn->prepare(
        "SELECT COALESCE(SUM(oi.quantity * oi.price), 0) AS revenue
         FROM orderitems_tbl oi
         JOIN orders_tbl o ON oi.order_id = o.order_id
         WHERE o.vendor_id = ? AND DATE(o.created_at) = CURDATE() AND o.status != 'canceled'"
    );
    $revenueTodayStmt->bind_param("i", $vendorId);
    $revenueTodayStmt->execute();
    $revenueToday = (float) $revenueTodayStmt->get_result()->fetch_assoc()['revenue'];

    $activeOrdersStmt = $conn->prepare(
        "SELECT COUNT(*) AS total FROM orders_tbl WHERE vendor_id = ? AND status NOT IN ('done', 'canceled')"
    );
    $activeOrdersStmt->bind_param("i", $vendorId);
    $activeOrdersStmt->execute();
    $activeOrders = (int) $activeOrdersStmt->get_result()->fetch_assoc()['total'];

    $lowStockStmt = $conn->prepare(
        "SELECT COUNT(*) AS total FROM inventory WHERE vendor_id = ? AND qty_on_hand <= reorder_threshold"
    );
    $lowStockStmt->bind_param("i", $vendorId);
    $lowStockStmt->execute();
    $lowStockItems = (int) $lowStockStmt->get_result()->fetch_assoc()['total'];

    return [
        "ordersToday" => $ordersToday,
        "revenueToday" => "₱" . number_format($revenueToday, 2),
        "activeOrders" => $activeOrders,
        "lowStockItems" => $lowStockItems,
    ];
}

function getRecentOrders(mysqli $conn, int $vendorId, int $limit = 5): array
{
    $stmt = $conn->prepare(
        "SELECT order_id, customer_name, status, target_minutes, created_at
         FROM orders_tbl
         WHERE vendor_id = ?
         ORDER BY created_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("ii", $vendorId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $recentOrders = [];
    $now = time();

    foreach ($rows as $order) {
        $displayStatus = ucfirst($order['status']);

        // Only orders still in the queue can be "Delayed" -- done/cancelled
        // orders keep their final status as-is.
        if (!in_array($order['status'], ['done', 'canceled'], true)) {
            $elapsedMinutes = max(0, (int) floor(($now - strtotime($order['created_at'])) / 60));
            if ($elapsedMinutes - (int) $order['target_minutes'] > 0) {
                $displayStatus = "Delayed";
            }
        }

        $recentOrders[] = [
            "id" => str_pad($order['order_id'], 3, "0", STR_PAD_LEFT),
            "customer" => $order['customer_name'],
            "status" => $displayStatus,
        ];
    }

    return $recentOrders;
}

function getInventoryAlerts(mysqli $conn, int $vendorId, int $limit = 5): array
{
    $stmt = $conn->prepare(
        "SELECT item_name, qty_on_hand, reorder_threshold, expiry_date
         FROM inventory
         WHERE vendor_id = ?
           AND (qty_on_hand <= reorder_threshold
                OR (expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)))
         ORDER BY qty_on_hand ASC
         LIMIT ?"
    );
    $stmt->bind_param("ii", $vendorId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $inventoryAlerts = [];

    foreach ($rows as $item) {
        if ($item['qty_on_hand'] <= 0) {
            $message = "🔴Out of Stock";
        } elseif ($item['qty_on_hand'] <= $item['reorder_threshold']) {
            $message = "🟡Restock Soon";
        } else {
            $message = "🟠Expiring Soon";
        }

        $inventoryAlerts[] = [
            "item" => $item['item_name'],
            "message" => $message,
        ];
    }

    return $inventoryAlerts;
}