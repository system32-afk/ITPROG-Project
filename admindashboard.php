<?php
require_once "database.php";
require_once "dashboard_data.php";

//check if user has loggedin
// if(!isset($_SESSION)&&!isset($_SESSION["vendor_id"])){
//     header("Location: ./login.php");
// }

// $vendorID = $_SESSION["vendor_id"];
$vendorID = 1;

$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();
$vendorResult = $getVendorInfo->get_result();
$vendorInfo = $vendorResult->fetch_assoc();

$dashboardStats = getDashboardStats($conn, $vendorID);
$recentOrders = getRecentOrders($conn, $vendorID);
$inventoryAlerts = getInventoryAlerts($conn, $vendorID);

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
        <h2><?php echo htmlspecialchars($vendorInfo["store_name"]); ?></h2>

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
            <a href="menumanagement.php">
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

            <button class="new-order-btn" id="openAddModal">
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
            <h2 id="ordersTodayStat"><?php echo $dashboardStats['ordersToday']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Revenue Today</span>
            <h2 id="revenueTodayStat"><?php echo $dashboardStats['revenueToday']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Active Orders</span>
            <h2 id="activeOrdersStat"><?php echo $dashboardStats['activeOrders']; ?></h2>
        </div>

        <div class="card">
            <span class="card-label">Low Stock Items</span>
            <h2 id="lowStockStat"><?php echo $dashboardStats['lowStockItems']; ?></h2>
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

                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="3">No orders yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($recentOrders as $order): ?>

                        <tr onclick="window.location='vieworder.php?id=<?php echo $order['id']; ?>'">
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer']); ?></td>

                            <td>
                                <span class="status <?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
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

                <div id="inventoryAlertsList">

                    <?php if (empty($inventoryAlerts)): ?>

                        <div class="alert-item">
                            <div class="alert-title">All good</div>
                            <div class="alert-text">No stock or expiry alerts right now.</div>
                        </div>

                    <?php endif; ?>

                    <?php foreach ($inventoryAlerts as $alert): ?>

                        <div class="alert-item">

                            <div class="alert-title">
                                <?php echo htmlspecialchars($alert['item']); ?>
                            </div>

                            <div class="alert-text">
                                <?php echo $alert['message']; ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

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

                <button class="action-btn" onclick="window.location='inventory.php'">
                    Manage Inventory
                </button>

                <button class="action-btn" onclick="window.location='menumanagement.php'">
                    Manage Menu
                </button>

                <button class="action-btn" id="generateQRBtn">
                    Generate QR
                </button>

            </div>

        </div>

    </div>

</div>

<div id="qrModal" class="modal">

    <div class="modal-box">

        <span id="closeQRBtn" class="close-modal">
            &times;
        </span>

        <h2>Restaurant QR Code</h2>

        <div class="qr-placeholder">

            <img src="generateQR.php?id=<?php echo $vendorID; ?>" class="qr-image" alt="QR Code">

        </div>

        <a
            href="generateQR.php?id=<?php echo $vendorID; ?>&download=1"
            class="download-btn"
            download>
            Download QR Code
        </a>

    </div>

</div>

<script src="js/admindashboard.js"></script>

</body>
</html>