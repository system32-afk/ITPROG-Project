<?php
require_once "database.php";

session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./login.php");
    exit();
}

//get vendor info
$vendorID = $_SESSION["vendor_id"];
$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();

$vendorResult = $getVendorInfo->get_result();

$vendorInfo = $vendorResult->fetch_assoc();


//get orders
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
    $order['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $createdTs = strtotime($order['created_at']);
    $elapsedMinutes = max(0, (int) floor(($now - $createdTs) / 60));
    $overrunMinutes = $elapsedMinutes - (int) $order['target_minutes'];
    $isDelayed = $overrunMinutes > 0;

    // "Delayed" overrides the display badge/class, but the real status
    // column underneath (pending/priority/preparing) is left untouched --
    // see orders_api.php for why it's computed instead of stored.
    $order['display_status'] = $isDelayed ? "Delayed" : ucfirst($order['status']);
    $order['elapsed_display'] = $elapsedMinutes . "m Ago";
    $order['target_display'] = $order['target_minutes'] . "m";
    $order['overrun_display'] = "Exceeded by " . $overrunMinutes . "m";
    $order['quantity_total'] = array_sum(array_column($order['items'], 'quantity'));
    $order['order_total'] = array_sum(array_map(
        fn($item) => (float) $item['price'] * (int) $item['quantity'],
        $order['items']
    ));
    $order['verification_code'] = $order['items'][0]['verification_code'] ?? null;
    $order['status'] = $order['items'][0]['status'] ?? null;
    $order['order_number'] = $order['items'][0]['order_number'] ?? null;
    if ($isDelayed) {
        $delayedCount++;
    }

    $liveOrders[] = $order;
}

$activeOrdersCount = count($liveOrders);

$allStations = [];
foreach ($liveOrders as $order) {
    foreach ($order['items'] as $item) {
        if (!empty($item['station'])) {
            $allStations[$item['station']] = true;
        }
    }
}
$allStations = array_keys($allStations);
sort($allStations);


//get store data




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
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
</head>

<body>

    <div class="sidebar">

        <div class="logo">
            <h2><?php echo htmlspecialchars($vendorInfo["store_name"]); ?>
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
            <a href="reports.php">
                <i class="fa-solid fa-chart-pie"></i>
                Reports</a>

            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>

    </div>

    <div class="main">

        <div class="topbar">

            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search orders...">
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

            <select id="stationFilter" class="filter-btn">

                <option value="">All Stations</option>
                <?php foreach ($allStations as $station): ?>
                    <option value="<?php echo htmlspecialchars($station); ?>">
                        <?php echo htmlspecialchars($station); ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <select id="sortOrders" class="filter-btn">

                <option value="">Sort By</option>
                <option value="delayed">Delayed First</option>
                <option value="quantity">Quantity Highest First</option>
                <option value="newest">Newest First</option>

            </select>

        </div>

        <div class="live-queue-container">

            <?php foreach ($liveOrders as $order): ?>

                <div class="order-card <?php echo strtolower($order['display_status']); ?>"
                    data-id="<?php echo $order['order_id']; ?>"
                    data-status="<?php echo strtolower($order['display_status']); ?>"
                    data-quantity="<?php echo $order['quantity_total']; ?>"
                    data-stations="<?php echo htmlspecialchars(implode(',', array_unique(array_column($order['items'], 'station')))); ?>">

                    <div class="order-header">

                        <h3>
                            <?= $order['order_number']; ?>
                        </h3>

                        <span class="order-status-badge <?php echo strtolower($order['display_status']); ?>">
                            <?php echo htmlspecialchars($order['display_status']); ?>
                        </span>

                    </div>

                    <div class="order-content">

                        <p class="customer-name">
                            <?php echo htmlspecialchars($order['customer_name']); ?>
                        </p>

                        <p class="customer-contact">
                            <?php echo htmlspecialchars($order['customer_contact']); ?>
                        </p>

                        <p class="table-number">
                            Table <?php echo htmlspecialchars($order['table_number']); ?>
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

                                <span class="item-price">
                                    ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </span>

                                <span class="item-station">
                                    <?php echo htmlspecialchars($item['station'] ?? ''); ?>
                                </span>

                                <?php if (!empty($item['Instructions'])): ?>
                                    <span class="item-notes">
                                        Note: <?php echo htmlspecialchars($item['Instructions']); ?>
                                    </span>
                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                        <div class="order-total">
                            <span>Total</span>
                            <span class="order-total-amount">₱<?php echo number_format($order['order_total'], 2); ?></span>
                        </div>

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

                    <?php if ($order["payment_method"] === "Cash" && $order['status'] === "Awaiting Payment"): ?>

                        <div class="order-actions">

                            <button class="verify-btn" data-code="<?= htmlspecialchars($order['verification_code']); ?>">
                                Generate Verification Code
                            </button>

                        </div>

                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <div class="order-card empty-order-card">

                <i class="fa-solid fa-plus"></i>

                <p>
                    New Orders will appear here
                </p>

            </div>

        </div>

    </div>
    <div id="verificationModal" class="modal">

        <div class="modal-content">

            <h2>Verification Code</h2>

            <h1 id="verificationCode">

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