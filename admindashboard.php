<?php
/*placeholder data, replace once we have db */
$dashboardStats = [
    "ordersToday" => 36,
    "revenueToday" => "₱12,000",
    "activeOrders" => 25,
    "lowStockItems" => 12
];

$recentOrders = [
    ["id" => "007", "customer" => "Customer G", "status" => "Pending"],
    ["id" => "006", "customer" => "Customer F", "status" => "Preparing"],
    ["id" => "005", "customer" => "Customer E", "status" => "Ready"],
    ["id" => "004", "customer" => "Customer D", "status" => "Preparing"],
    ["id" => "003", "customer" => "Customer C", "status" => "Pending"],
    ["id" => "002", "customer" => "Customer B", "status" => "Ready"],
    ["id" => "001", "customer" => "Customer A", "status" => "Preparing"]
];

$inventoryAlerts = [
    ["item" => "Ingredient A", "message" => "🔴Low Stock"],
    ["item" => "Ingredient B", "message" => "🟡Restock Soon"],
    ["item" => "Ingredient C", "message" => "🟠Expiring Soon"]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pabili Admin Dashboard</title>

    <link rel="stylesheet" href="css/admindashboard.css">

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
           <a href="admindashboard.php" class="active">
            <i class="fa-solid fa-chart-line"></i>
            Dashboard</a>
        </li>
        <li>
            <a href="livequeue.php">
            <i class="fa-solid fa-utensils"></i>
            Live Queue</a>
        </li>
        <li>
            <a href="inventory.php">
            <i class="fa-solid fa-box"></i>
            Inventory</a>
        </li>
        <li>
            <a href="#">
            <i class="fa-solid fa-clipboard-list"> </i>
            Menu Management</a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="#">
            <i class="fa-solid fa-chart-pie"></i>
            Reports</a>
        <a href="#">
            <i class="fa-solid fa-question-circle"></i>
            Help</a>
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

    <h1 class="page-title">Dashboard Overview</h1>

    <div class="stats">

        <div class="card">
            <span class="card-label">Orders Today</span>
            <h2><?php echo $dashboardStats['ordersToday']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Revenue Today</span>
            <h2><?php echo $dashboardStats['revenueToday']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Active Orders</span>
            <h2><?php echo $dashboardStats['activeOrders']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Low Stock Items</span>
            <h2><?php echo $dashboardStats['lowStockItems']; ?></h2>
        </div>

    </div>

    <div class="dashboard-grid">

        <div class="left-column">

            <div class="panel">

                <div class="panel-header">
                    <h3>Recent Orders</h3>
                </div>

                <table>

                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="ordersTable">

                    <?php foreach(array_slice($recentOrders, 0, 5) as $order): ?>

                        <tr onclick="window.location='vieworder.php?id=<?php echo $order['id']; ?>'">
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['customer']; ?></td>

                            <td>
                                <span class="status <?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <div class="right-column">

            <div class="panel">

                <div class="panel-header">
                    <h3>Inventory Alerts</h3>
                </div>

                <?php foreach($inventoryAlerts as $alert): ?>

                    <div class="alert-item">

                        <div class="alert-title">
                            <?php echo $alert['item']; ?>
                        </div>

                        <div class="alert-text">
                            <?php echo $alert['message']; ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

            <div class="panel">

                <div class="panel-header">
                    <h3>Quick Actions</h3>
                </div>

                <button 
                    class="action-btn"
                    onclick="window.location='livequeue.php'">
                    View Live Queue
                </button>

                <button class="action-btn">
                    Manage Inventory
                </button>

                <button class="action-btn">
                    Manage Menu
                </button>

            </div>

        </div>

    </div>

</div>
<script src="js/admindashboard.js"></script>

</body>
</html>