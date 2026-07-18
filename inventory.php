<?php

$inventoryItems = [

    [
        "id" => 1,
        "name" => "Chicken Breast",
        "stock" => 15.5,
        "unit" => "kg",
        "threshold" => 10,
        "expiry" => "2026-10-18"
    ],

    [
        "id" => 2,
        "name" => "Tomatoes",
        "stock" => 8,
        "unit" => "kg",
        "threshold" => 10,
        "expiry" => "2026-07-01"
    ],

    [
        "id" => 3,
        "name" => "Whole Milk",
        "stock" => 48,
        "unit" => "Liters",
        "threshold" => 10,
        "expiry" => "2026-10-12"
    ],

    [
        "id" => 4,
        "name" => "Baby Spinach",
        "stock" => 22,
        "unit" => "kg",
        "threshold" => 10,
        "expiry" => "2026-10-15"
    ]

];

$totalItems = count($inventoryItems);
$lowStock = 0;
$expiringSoon = 0;

foreach($inventoryItems as $item){
    if($item["stock"] <= $item["threshold"]){
        $lowStock++;
    }

    if(strtotime($item["expiry"]) <= strtotime("+30 days")){
        $expiringSoon++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory</title>

<link rel="stylesheet" href="css/inventory.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">

</head>
<body>

<div class="sidebar">
<div class="logo">
<h2>Kitchen Admin</h2>
<p>Station #04</p>
</div>

<ul class="menu">
<li><a href="livequeue.php"><i class="fa-solid fa-utensils"></i> Live Queue</a></li>
<li><a href="inventory.php" class="active"><i class="fa-solid fa-box"></i> Inventory</a></li>
<li><a href="menumanagement.php"><i class="fa-solid fa-clipboard-list"></i> Menu Management</a></li>
</ul>

<div class="sidebar-footer">
<a href="#"><i class="fa-solid fa-chart-pie"></i> Reports</a>
<a href="#"><i class="fa-solid fa-circle-question"></i> Help</a>
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
<h2><?php echo $lowStock; ?></h2>
</div>

<div class="card">
<span class="card-label">Expiring Soon</span>
<h2><?php echo $expiringSoon; ?></h2>
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

<?php foreach($inventoryItems as $item):

if($item["stock"]<=0){
    $status="Out of Stock";
    $class="out";
}elseif($item["stock"]<=$item["threshold"]){
    $status="Low Stock";
    $class="low";
}else{
    $status="In Stock";
    $class="good";
}
?>

<tr>

<td><?php echo $item["name"]; ?></td>

<td><?php echo $item["stock"]; ?></td>

<td><?php echo $item["unit"]; ?></td>

<td>
<span class="inventory-status <?php echo $class; ?>">
<?php echo $status; ?>
</span>
</td>

<td><?php echo date("M d, Y",strtotime($item["expiry"])); ?></td>

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
<th>Date & Time</th>
<th>Action</th>
<th>User</th>
<th>Change</th>
</tr>
</thead>

<tbody>

<tr>
<td>Jul 18, 2026 9:45 AM</td>
<td>Updated</td>
<td>Einzenn Ham</td>
<td>Stock changed from 20 kg → 35 kg</td>
</tr>

<tr>
<td>Jul 17, 2026 3:10 PM</td>
<td>Added</td>
<td>Einzenn Ham</td>
<td>Ingredient created</td>
</tr>

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
