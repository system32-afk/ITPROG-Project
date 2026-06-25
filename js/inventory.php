<?php

$inventoryItems = [

    [
        "id" => 1,
        "name" => "Classic Burger",
        "price" => 120,
        "stock" => 35,
        "threshold" => 10,
        "updated" => "2 mins ago"
    ],

    [
        "id" => 2,
        "name" => "French Fries",
        "price" => 75,
        "stock" => 8,
        "threshold" => 10,
        "updated" => "5 mins ago"
    ],

    [
        "id" => 3,
        "name" => "Iced Tea",
        "price" => 45,
        "stock" => 0,
        "threshold" => 5,
        "updated" => "12 mins ago"
    ],

    [
        "id" => 4,
        "name" => "Chicken Nuggets",
        "price" => 110,
        "stock" => 24,
        "threshold" => 8,
        "updated" => "1 hour ago"
    ]

];

/* Dashboard Cards */

$totalItems = count($inventoryItems);

$inStock = 0;
$lowStock = 0;
$outOfStock = 0;

foreach($inventoryItems as $item){

    if($item["stock"] == 0){

        $outOfStock++;

    }

    elseif($item["stock"] <= $item["threshold"]){

        $lowStock++;

    }

    else{

        $inStock++;

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inventory</title>

<link rel="stylesheet" href="css/style.css">

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

<li>

<a href="admindashboard.php">

<i class="fa-solid fa-chart-line"></i>

Dashboard

</a>

</li>

<li>

<a href="livequeue.php">

<i class="fa-solid fa-utensils"></i>

Live Queue

</a>

</li>

<li>

<a href="inventory.php" class="active">

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

</div>

<div class="main">

<div class="topbar">

<div class="search-container">

<i class="fa-solid fa-magnifying-glass"></i>

<input
type="text"
id="inventorySearch"
placeholder="Search inventory...">

</div>

<div class="top-actions">

<button class="new-order-btn">

+ Add Item

</button>

</div>

</div>

<h1 class="page-title">

Inventory

</h1>

<div class="stats">

<div class="card">

<span class="card-label">

Total Items

</span>

<h2>

<?php echo $totalItems; ?>

</h2>

</div>

<div class="card">

<span class="card-label">

In Stock

</span>

<h2>

<?php echo $inStock; ?>

</h2>

</div>

<div class="card">

<span class="card-label">

Low Stock

</span>

<h2>

<?php echo $lowStock; ?>

</h2>

</div>

<div class="card">

<span class="card-label">

Out of Stock

</span>

<h2>

<?php echo $outOfStock; ?>

</h2>

</div>

</div>

<div class="panel">

<table id="inventoryTable">

<thead>

<tr>

<th>Item</th>

<th>Price</th>

<th>Stock</th>

<th>Threshold</th>

<th>Status</th>

<th>Updated</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($inventoryItems as $item): ?>

<?php

if($item["stock"] == 0){

$status="Out of Stock";

$class="out";

}

elseif($item["stock"] <= $item["threshold"]){

$status="Low Stock";

$class="low";

}

else{

$status="In Stock";

$class="good";

}

?>

<tr>

<td>

<?php echo $item["name"]; ?>

</td>

<td>

₱<?php echo number_format($item["price"],2); ?>

</td>

<td>

<?php echo $item["stock"]; ?>

</td>

<td>

<?php echo $item["threshold"]; ?>

</td>

<td>

<span class="inventory-status <?php echo $class; ?>">

<?php echo $status; ?>

</span>

</td>

<td>

<?php echo $item["updated"]; ?>

</td>

<td>

<button class="table-btn edit">

Edit

</button>

<button class="table-btn restock">

Restock

</button>

<button class="table-btn delete">

Delete

</button>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<script src="js/script.js"></script>

</body>

</html>