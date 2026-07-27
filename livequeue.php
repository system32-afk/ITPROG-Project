<?php
require_once "database.php";

// TODO: swap for $_SESSION['vendor_id'] once login/auth is wired up.
$vendorId = 1;

$stmt = $conn->prepare(
    "SELECT order_id, customer_name, customer_contact, payment_method, status, target_minutes, created_at
    FROM orders_tbl
    WHERE vendor_id = ? AND status NOT IN ('done','canceled')
    ORDER BY created_at ASC"
);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$itemStmt = $conn->prepare(
    "SELECT oi.order_item_id, oi.item_id, oi.quantity, oi.price, m.name, m.station
    FROM orderitems_tbl oi
    JOIN menuitems_tbl m ON oi.item_id = m.item_id
    WHERE oi.order_id = ?"
);

$liveOrders = [];
$delayedCount = 0;
$now = time();

foreach ($orders as $order) {
    $itemStmt->bind_param("i", $order['order_id']);
    $itemStmt->execute();
    $order['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $createdTs = strtotime($order['created_at']);
    $elapsedMinutes = max(0, (int)floor(($now - $createdTs) / 60));
    $overrunMinutes = $elapsedMinutes - (int)$order['target_minutes'];
    $isDelayed = $overrunMinutes > 0;

    // "Delayed" overrides the display badge/class, but the real status
    // column underneath (pending/priority/preparing) is left untouched --
    // see orders_api.php for why it's computed instead of stored.
    $order['display_status'] = $isDelayed ? "Delayed" : ucfirst($order['status']);
    $order['elapsed_display'] = $elapsedMinutes . "m Ago";
    $order['target_display'] = $order['target_minutes'] . "m";
    $order['overrun_display'] = "Exceeded by " . $overrunMinutes . "m";
    $order['quantity_total'] = array_sum(array_column($order['items'], 'quantity'));

    if ($isDelayed) {
        $delayedCount++;
    }

    $liveOrders[] = $order;
}

$activeOrdersCount = count($liveOrders);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pabili Live Queue</title>

    <link rel="stylesheet" href="css/livequeue.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>

<div class="sidebar">

    <div class="logo">
        <h2>Kitchen Admin</h2>
        <p>Station #04</p>
    </div>

    <ul class="menu">

        <li>
            <a href="admindashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="livequeue.php" class="active">
                <i class="fa-solid fa-utensils"></i>
                Live Queue
            </a>
        </li>

        <li>
            <a href="inventory.php">
                <i class="fa-solid fa-box"></i>
                Inventory
            </a>
        </li>

        <li>
            <a href="menumanagement.php">
                <i class="fa-solid fa-clipboard-list"></i>
                Menu Management
            </a>
        </li>

    </ul>

    <div class="sidebar-footer">

        <a href="#">
            <i class="fa-solid fa-chart-pie"></i>
            Reports
        </a>

        <a href="#">
            <i class="fa-solid fa-circle-question"></i>
            Help
        </a>

    </div>

</div>

<div class="main">

    <div class="topbar">

        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search orders...">
        </div>

        <div class="top-actions">

            <button class="icon-btn">
                <i class="fa-regular fa-bell"></i>
            </button>

            <button class="icon-btn">
                <i class="fa-solid fa-gear"></i>
            </button>

            <button class="new-order-btn" id="openAddModal">
                + New Order
            </button>

            <div class="profile-circle" onclick="window.location='profile.php'">
                A
            </div>

        </div>

    </div>

    <div class="stats">

        <div class="card">
            <span class="card-label">
                Active Orders
            </span>
            <h2 class="active-orders-number"><?= $activeOrdersCount ?></h2>
        </div>

        <div class="card">
            <span class="card-label">
                Currently Delayed
            </span>
            <h2 class="delayed-number">
                <?= $delayedCount ?>
            </h2>
        </div>

    </div>

    <h1 class="page-title">
    Live Queue
    </h1>

    <div class="queue-toolbar">

    <button class="filter-btn">
        <i class="fa-solid fa-filter"></i>
        All Stations
    </button>

    <select id="sortOrders" class="filter-btn">

        <option value="">Sort By</option>
        <option value="delayed">Delayed First</option>
        <option value="quantity">Quantity Highest First</option>
        <option value="newest">Newest First</option>

    </select>

    </div>

    <div class="live-queue-container">

        <?php foreach ($liveOrders as $order): ?>

        <div
            class="order-card <?php echo strtolower($order['display_status']); ?>"

            data-id="<?php echo $order['order_id']; ?>"

            data-status="<?php echo strtolower($order['display_status']); ?>"

            data-quantity="<?php echo $order['quantity_total']; ?>"
        >

            <div class="order-header">

                <h3>
                    #<?php echo $order['order_id']; ?>
                </h3>

                <span class="order-status-badge <?php echo strtolower($order['display_status']); ?>">
                    <?php echo htmlspecialchars($order['display_status']); ?>
                </span>

            </div>

            <div class="order-content">

                <p class="customer-name">
                    <?php echo htmlspecialchars($order['customer_name']); ?>
                </p>

                <p class="order-time">

                    <?php echo htmlspecialchars($order['elapsed_display']); ?>

                    <?php if ($order['display_status'] === "Delayed"): ?>

                    • <?php echo htmlspecialchars($order['overrun_display']); ?>

                    <?php else: ?>

                        • Target:
                        <?php echo htmlspecialchars($order['target_display']); ?>

                    <?php endif; ?>

                </p>

                <hr>

                <?php foreach ($order['items'] as $item): ?>

                <div class="order-item">

                    <span class="item-qty">
                        <?php echo $item['quantity']; ?>x
                    </span>

                    <span class="item-name">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </span>

                    <span class="item-station">
                        <?php echo htmlspecialchars($item['station'] ?? ''); ?>
                    </span>

                </div>

                <?php endforeach; ?>

            </div>

            <div class="order-actions">

                <button class="done-btn">
                    Done
                </button>

                <button class="cancel-btn">
                    Cancel
                </button>

            </div>

            <div class="order-actions">

                <button class="process-btn">
                    Process
                </button>

                <button class="priority-btn">
                    Set Priority
                </button>

            </div>

            <?php if ($order["payment_method"] === "Cash"): ?>

            <div class="order-actions">

                <button class="verify-btn">
                Generate Verification Code
                </button>

            </div>

        <?php endif; ?>

    </div>
        <?php endforeach; ?>

        <div class="order-card empty-order-card">

            <i class="fa-solid fa-plus"></i>

            <p>
                Awaiting New Orders
            </p>

        </div>

    </div>

</div>
<div id="verificationModal" class="modal">

    <div class="modal-content">

        <h2>Verification Code</h2>

        <h1 id="verificationCode">
            000000
        </h1>

        <button id="copyCodeBtn" class="process-btn">
            Copy Code
            
        </button>

        <button id="closeModalBtn" class="cancel-btn">
            Close
        </button>

    </div>

</div>

<script src="js/livequeue.js"></script>
</body>
</html>