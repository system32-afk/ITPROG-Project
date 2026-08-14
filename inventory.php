<?php
require_once "database.php";

session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./login.php");
    exit();
}


$vendorID = $_SESSION["vendor_id"];

$stmt = $conn->prepare(
    "SELECT inventory_id, item_name, unit, qty_on_hand, reorder_threshold, expiry_date, is_perishable
    FROM inventory
    WHERE vendor_id = ?
    ORDER BY item_name ASC"
);
$stmt->bind_param("i", $vendorID);
$stmt->execute();
$result = $stmt->get_result();

$inventoryItems = [];
$lowStock = 0;
$expiringSoon = 0;
$thirtyDaysOut = strtotime("+30 days");

while ($row = $result->fetch_assoc()) {
    if ($row["qty_on_hand"] <= 0) {
        $status = "Out of Stock";
        $class = "out";
    } elseif ($row["qty_on_hand"] <= $row["reorder_threshold"]) {
        $status = "Low Stock";
        $class = "low";
    } else {
        $status = "In Stock";
        $class = "good";
    }

    $row["status"] = $status;
    $row["status_class"] = $class;

    if ($class !== "good") {
        $lowStock++;
    }
    if ($row["expiry_date"] && strtotime($row["expiry_date"]) <= $thirtyDaysOut) {
        $expiringSoon++;
    }

    $inventoryItems[] = $row;
}


$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();
$vendorResult = $getVendorInfo->get_result();
$vendorInfo = $vendorResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>

    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono&display=swap"
        rel="stylesheet">

</head>

<body>

    <div class="sidebar">

        <div class="logo">
            <h2><?php echo htmlspecialchars($vendorInfo["store_name"]); ?>
            </h2>

        </div>

        <ul class="menu">
            <li>
                <a href="admindashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard</a>
            </li>
            <li>
                <a href="livequeue.php">
                    <i class="fa-solid fa-utensils"></i>
                    Live Queue</a>
            </li>
            <li>
                <a href="inventory.php" class="active">
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
            <a href="reports.php">
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
                <input type="text" id="inventorySearch" placeholder="Filter by ingredient name or SKU...">
            </div>

            <div class="top-actions">
                <button class="new-order-btn" id="openAddModal">
                    <i class="fa-solid fa-circle-plus"></i>
                    Add New Stock
                </button>
            </div>

        </div>

        <h1 class="page-title">Inventory Management</h1>

        <div class="stats">

            <div class="card">
                <span class="card-label">Low Stock Items</span>
                <h2 id="lowStockCount">
                    <?php echo $lowStock; ?>
                </h2>
            </div>

            <div class="card">
                <span class="card-label">Expiring Soon</span>
                <h2 id="expiringSoonCount">
                    <?php echo $expiringSoon; ?>
                </h2>
            </div>

        </div>

        <div class="panel">

            <table id="inventoryTable">

                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($inventoryItems as $item): ?>

                        <tr data-id="<?= (int) $item['inventory_id'] ?>"
                            data-perishable="<?= (int) $item['is_perishable'] ?>">

                            <td><?= htmlspecialchars($item["item_name"]) ?></td>

                            <td><?= htmlspecialchars($item["qty_on_hand"]) ?></td>

                            <td><?= htmlspecialchars($item["unit"]) ?></td>

                            <td>
                                <span class="inventory-status <?= htmlspecialchars($item["status_class"]) ?>">
                                    <?= htmlspecialchars($item["status"]) ?>
                                </span>
                            </td>

                            <td><?= $item["expiry_date"] ? date("M d, Y", strtotime($item["expiry_date"])) : "-" ?></td>

                            <td>

                                <button class="table-btn edit">Edit</button>
                                <button class="table-btn history">History</button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- ADD MODAL -->
    <div class="modal" id="addInventoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Inventory Item</h2>
                <span class="close">&times;</span>
            </div>

            <div class="modal-body">

                <h3>Ingredient Information</h3>

                <div class="form-group">
                    <label>Ingredient Name *</label>
                    <input type="text" id="addName">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" id="addUnit" placeholder="kg, g, pcs, L">
                    </div>

                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" id="addQuantity">
                    </div>
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label>Minimum Threshold</label>
                        <input type="number" id="addThreshold">
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" id="addExpiry">
                    </div>

                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="nonPerishable">
                    <label for="nonPerishable">Non-perishable Food</label>
                </div>

            </div>

            <div class="modal-footer">
                <button class="table-btn">Cancel</button>
                <button class="table-btn edit" id="saveItemBtn">
                    Save Item
                </button>
            </div>

        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal" id="editInventoryModal">

        <div class="modal-content">

            <div class="modal-header">
                <h2>Edit Ingredient</h2>
                <span class="close">&times;</span>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Ingredient Name</label>
                    <input type="text" id="editName">
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label>Current Stock</label>
                        <input type="number" id="editStock">
                    </div>

                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" id="editUnit">
                    </div>

                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label>Minimum Threshold</label>
                        <input type="number" id="editThreshold">
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" id="editExpiry">
                    </div>

                </div>

                <div class="form-group">
                    <label>Reason for Change *</label>
                    <textarea id="changeReason" rows="3"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="table-btn">Cancel</button>
                <button class="table-btn edit" id="updateItemBtn">
                    Update Item
                </button>
            </div>

        </div>

    </div>

    <!-- HISTORY MODAL -->
    <div class="modal" id="historyModal">
        <div class="modal-content">

            <div class="modal-header">
                <h2>Inventory Edit History</h2>
                <span class="close">&times;</span>
            </div>

            <div class="modal-body">

                <table>

                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Action</th>
                            <th>Change</th>
                        </tr>
                    </thead>

                    <tbody id="historyTableBody">
                        <!-- populated by inventory.js via inventory_api.php?action=history -->
                    </tbody>

                </table>

            </div>

            <div class="modal-footer">
                <button class="table-btn">Close</button>
            </div>

        </div>
    </div>

    <script src="js/inventory.js"></script>

</body>

</html>