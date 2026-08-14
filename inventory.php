<?php
require_once "database.php";

session_start();
//check if user has loggedin
if (!isset($_SESSION["vendor_id"])) {
    header("Location: ./login.php");
    exit();
}


$vendorID = $_SESSION["vendor_id"];

$perPage = 5;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM inventory WHERE vendor_id = ?");
$countStmt->bind_param("i", $vendorID);
$countStmt->execute();
$totalItems = (int) $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

// Stats (Low Stock / Expiring Soon) are computed across ALL of the
// vendor's inventory, not just the current page, so the cards stay accurate.
$statsStmt = $conn->prepare(
    "SELECT qty_on_hand, reorder_threshold, expiry_date FROM inventory WHERE vendor_id = ?"
);
$statsStmt->bind_param("i", $vendorID);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();

$lowStock = 0;
$expiringSoon = 0;
$thirtyDaysOut = strtotime("+30 days");

while ($row = $statsResult->fetch_assoc()) {
    if ($row["qty_on_hand"] <= $row["reorder_threshold"]) {
        $lowStock++;
    }
    if ($row["expiry_date"] && strtotime($row["expiry_date"]) <= $thirtyDaysOut) {
        $expiringSoon++;
    }
}

$stmt = $conn->prepare(
    "SELECT inventory_id, item_name, unit, qty_on_hand, reorder_threshold, expiry_date, is_perishable, last_updated
    FROM inventory
    WHERE vendor_id = ?
    ORDER BY item_name ASC
    LIMIT ? OFFSET ?"
);
$stmt->bind_param("iii", $vendorID, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$inventoryItems = [];

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
                <input type="text" id="inventorySearch" placeholder="Filter by ingredient name or SKU...">
            </div>

            <div class="top-actions">
                <div class="sort-container">
                    <label for="sortSelect" class="sort-label">Sort by</label>
                    <select id="sortSelect">
                        <option value="">Default</option>
                        <option value="name-asc">Name (A–Z)</option>
                        <option value="name-desc">Name (Z–A)</option>
                        <option value="qty-asc">Stock (Low–High)</option>
                        <option value="qty-desc">Stock (High–Low)</option>
                        <option value="status-asc">Status (Critical First)</option>
                        <option value="expiry-asc">Expiry Date (Soonest)</option>
                        <option value="expiry-desc">Expiry Date (Latest)</option>
                        <option value="updated-desc">Latest Updated</option>
                        <option value="updated-asc">Oldest Updated</option>
                    </select>
                </div>
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
                            data-perishable="<?= (int) $item['is_perishable'] ?>"
                            data-name="<?= htmlspecialchars($item['item_name']) ?>"
                            data-qty="<?= htmlspecialchars($item['qty_on_hand']) ?>"
                            data-status="<?= htmlspecialchars($item['status_class']) ?>"
                            data-expiry="<?= $item['expiry_date'] ? htmlspecialchars($item['expiry_date']) : '' ?>"
                            data-updated="<?= htmlspecialchars($item['last_updated']) ?>">

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

            <div class="table-footer">
                <span class="showing-text">
                    Showing <?= $totalItems === 0 ? 0 : $offset + 1 ?>–<?= min($offset + $perPage, $totalItems) ?> of
                    <?= $totalItems ?> Inventory Items
                </span>

                <div class="pagination">
                    <?php for ($p = 1; $p <= min(3, $totalPages); $p++): ?>
                        <a href="?page=<?= $p ?>" class="page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($totalPages > 4): ?>
                        <span class="page-ellipsis">...</span>
                        <a href="?page=<?= $totalPages ?>"
                            class="page-btn <?= $totalPages === $currentPage ? 'active' : '' ?>"><?= $totalPages ?></a>
                    <?php elseif ($totalPages === 4): ?>
                        <a href="?page=4" class="page-btn <?= $currentPage === 4 ? 'active' : '' ?>">4</a>
                    <?php endif; ?>
                </div>
            </div>

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