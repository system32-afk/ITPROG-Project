<?php

$liveOrders = [
    [
        "id" => "4921",
        "customer" => "Customer A",
        "time" => "12m Ago",
        "status" => "Priority",
        "items" => [
            ["qty" => 2, "name" => "Menu Item A"],
            ["qty" => 1, "name" => "Menu Item B"],
            ["qty" => 1, "name" => "Menu Item C"]
        ]
    ],

    [
        "id" => "4922",
        "customer" => "Customer B",
        "time" => "5m Ago",
        "status" => "Preparing",
        "items" => [
            ["qty" => 1, "name" => "Menu Item D"],
            ["qty" => 1, "name" => "Menu Item E"]
        ]
    ],

    [
        "id" => "4923",
        "customer" => "Customer C",
        "time" => "2m Ago",
        "status" => "Pending",
        "items" => [
            ["qty" => 2, "name" => "Menu Item F"],
            ["qty" => 1, "name" => "Menu Item G"]
        ]
    ],

    [
        "id" => "4918",
        "customer" => "Customer D",
        "time" => "22m Ago",
        "status" => "Delayed",
        "items" => [
            ["qty" => 1, "name" => "Menu Item H"],
            ["qty" => 2, "name" => "Menu Item I"]
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

    <link rel="stylesheet" href="css/style.css">

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
            <a href="#">
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
            <span class="card-label">Active Orders</span>
            <h2>24</h2>
        </div>

        <div class="card">
            <span class="card-label">Avg Prep Time</span>
            <h2>14m</h2>
        </div>

        <div class="card">
            <span class="card-label">Rush Status</span>
            <h2>High Load</h2>
        </div>

        <div class="card">
            <span class="card-label">Staff Online</span>
            <h2>08</h2>
        </div>

    </div>

    <h1 class="page-title">
    Live Queue
    </h1>

    <div class="live-queue-container">

        <?php foreach($liveOrders as $order): ?>

        <div class="order-card <?php echo strtolower($order['status']); ?>">

            <div class="order-header">

                <h3>
                    #<?php echo $order['id']; ?>
                </h3>

                <span class="order-status-badge <?php echo strtolower($order['status']); ?>">
                    <?php echo $order['status']; ?>
                </span>

            </div>

            <div
                class="order-content"
                onclick="window.location='vieworder.php?id=<?php echo $order['id']; ?>'"
            >

                <p class="customer-name">
                    <?php echo $order['customer']; ?>
                </p>

                <p class="order-time">
                    <?php echo $order['time']; ?>
                </p>

                <hr>

                <?php foreach($order['items'] as $item): ?>

                <div class="order-item">

                    <span class="item-qty">
                        <?php echo $item['qty']; ?>x
                    </span>

                    <span class="item-name">
                        <?php echo $item['name']; ?>
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

            <button class="priority-btn">
                Set Priority
            </button>

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

<script src="js/script.js"></script>
</body>
</html>
