<?php

$liveOrders = [

    [
        "order_id" => 4921,
        "vendor_id" => 1,
        "customer_name" => "Customer A",
        "customer_contact" => "09171234567",
        "status" => "Priority",
        "target" => "15m",
        "created_at" => "12m Ago",

        "items" => [

            [
                "order_item_id" => 1,
                "item_id" => 1,
                "quantity" => 2,
                "price" => 180,
                "name" => "Menu Item A",
                "station" => "GRILL"
            ],

            [
                "order_item_id" => 2,
                "item_id" => 2,
                "quantity" => 1,
                "price" => 95,
                "name" => "Menu Item B",
                "station" => "FRYER"
            ],

            [
                "order_item_id" => 3,
                "item_id" => 3,
                "quantity" => 1,
                "price" => 70,
                "name" => "Menu Item C",
                "station" => "COLD"
            ]

        ]

    ],

    [
        "order_id" => 4922,
        "vendor_id" => 1,
        "customer_name" => "Customer B",
        "customer_contact" => "09171234568",
        "status" => "Preparing",
        "target" => "10m",
        "created_at" => "5m Ago",

        "items" => [

            [
                "order_item_id" => 4,
                "item_id" => 4,
                "quantity" => 1,
                "price" => 150,
                "name" => "Menu Item D",
                "station" => "PREP"
            ],

            [
                "order_item_id" => 5,
                "item_id" => 5,
                "quantity" => 1,
                "price" => 80,
                "name" => "Menu Item E",
                "station" => "COLD"
            ]

        ]

    ],

    [
        "order_id" => 4923,
        "vendor_id" => 1,
        "customer_name" => "Customer C",
        "customer_contact" => "09171234569",
        "status" => "Pending",
        "target" => "15m",
        "created_at" => "2m Ago",

        "items" => [

            [
                "order_item_id" => 6,
                "item_id" => 6,
                "quantity" => 2,
                "price" => 210,
                "name" => "Menu Item F",
                "station" => "GRILL"
            ],

            [
                "order_item_id" => 7,
                "item_id" => 7,
                "quantity" => 1,
                "price" => 60,
                "name" => "Menu Item G",
                "station" => "COLD"
            ]

        ]

    ],

    [
        "order_id" => 4918,
        "vendor_id" => 1,
        "customer_name" => "Customer D",
        "customer_contact" => "09171234570",
        "status" => "Delayed",
        "target" => "15m",
        "created_at" => "22m Ago",

        "items" => [

            [
                "order_item_id" => 8,
                "item_id" => 8,
                "quantity" => 1,
                "price" => 170,
                "name" => "Menu Item H",
                "station" => "FRYER"
            ],

            [
                "order_item_id" => 9,
                "item_id" => 9,
                "quantity" => 2,
                "price" => 120,
                "name" => "Menu Item I",
                "station" => "GRILL"
            ]

        ]

    ]

];

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
            <a href="#">
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

            <button class="new-order-btn">
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
            <h2>24</h2>
        </div>

        <div class="card">
            <span class="card-label">
                Currently Delayed
            </span>
            <h2 class="delayed-number">
                1
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

    <button class="filter-btn">

        Newest First

    </button>

    </div>

    <div class="live-queue-container">

        <?php foreach($liveOrders as $order): ?>

        <div class="order-card <?php echo strtolower($order['status']); ?>">

            <div class="order-header">

                <h3>
                    #<?php echo $order['order_id']; ?>
                </h3>

                <span class="order-status-badge <?php echo strtolower($order['status']); ?>">
                    <?php echo $order['status']; ?>
                </span>

            </div>

            <div class="order-content">

                <p class="customer-name">
                    <?php echo $order['customer_name']; ?>
                </p>

                <p class="order-time">

                    <?php echo $order['created_at']; ?>

                    <?php if($order['status']=="Delayed"): ?>

                    • Exceeded by 7m

                    <?php else: ?>

                        • Target:
                        <?php echo $order['target']; ?>

                    <?php endif; ?>

                </p>

                <hr>

                <?php foreach($order['items'] as $item): ?>

                <div class="order-item">

                    <span class="item-qty">
                        <?php echo $item['quantity']; ?>x
                    </span>

                    <span class="item-name">
                        <?php echo $item['name']; ?>
                    </span>

                    <span class="item-station">
                        <?php echo $item['station']; ?>
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

<script src="js/livequeue.js"></script>
</body>
</html>
