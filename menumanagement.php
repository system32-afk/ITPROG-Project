<?php

$menuItems = [
    [
        'image'       => 'img/wagyu-burger.jpg',
        'name'        => 'Signature Wagyu Burger',
        'description' => 'Wagyu beef, aged cheddar, truffle aioli, brioche.',
        'category'    => 'Main Course',
        'price'       => '24.00',
        'enabled'     => true,
    ],
    [
        'image'       => 'img/kale-caesar.jpg',
        'name'        => 'Rustic Kale Caesar',
        'description' => 'Organic kale, shaved parmesan, garlic croutons.',
        'category'    => 'Salads',
        'price'       => '16.50',
        'enabled'     => true,
    ],
    [
        'image'       => 'img/flatbread.jpg',
        'name'        => 'Spicy Pepperoni Flatbread',
        'description' => 'Hand-stretched dough, calabrian chili, mozzarella.',
        'category'    => 'Appetizers',
        'price'       => '18.00',
        'enabled'     => false,
    ],
    [
        'image'       => 'img/lava-cake.jpg',
        'name'        => 'Truffle Lava Cake',
        'description' => '70% dark chocolate, raspberry coulis, vanilla bean ice cream.',
        'category'    => 'Desserts',
        'price'       => '12.00',
        'enabled'     => true,
    ],
];
 
$totalItems   = 42;
$currentPage  = 1;
$totalPages   = 11;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pabili Menu Management</title>

    <link rel="stylesheet" href="css/menumanagement.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- ═══════════════════════════ SIDEBAR ═══════════════════════════ -->

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
            <a href="inventory.php">
                <i class="fa-solid fa-box"></i>
                Inventory
            </a>
        </li>

        <li>
            <a href="menumanagement.php" class="active">
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

<!-- ═══════════════════════════ END SIDEBAR ═══════════════════════════ -->

<!-- ═══════════════════════════ MAIN ═══════════════════════════ -->

<div class="main">

    <!-- Topbar -->

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

    <!-- end topbar -->

    <!-- Page header -->

    <div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Menu Catalog</h1>
        <p class="page-description">Manage your digital menu items, pricing, and availability.</p>
    </div>

        <button class="add-item-btn">
            <i class="fa-solid fa-circle-plus"></i>
            Add New Item
        </button>
    </div>

    <div id="addMenuModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Food Item</h3>
            <button class="close-btn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addMenuForm">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" placeholder="e.g. Truffle Lava Cake" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select required>
                        <option value="">Select category...</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Appetizers">Appetizers</option>
                        <option value="Salads">Salads</option>
                        <option value="Desserts">Desserts</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea rows="3" placeholder="Enter item description..."></textarea>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" placeholder="e.g. img/new-item.jpg">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="addMenuForm" class="save-btn">Save Item</button>
        </div>
        </div>
    </div>

    <!-- Menu table -->
    <div class="menu-table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width:70px;">Item</th>
                    <th>Name &amp; Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($menuItems as $index => $item): ?>
                <tr>
                    <!-- Thumbnail -->
                    <td>
                        <img
                            class="item-thumb"
                            src="<?= htmlspecialchars($item['image']) ?>"
                            alt="<?= htmlspecialchars($item['name']) ?>"
                            onerror="this.style.background='#EEF2FF'"
                        >
                    </td>
 
                    <!-- Name & description -->
                    <td>
                        <div class="item-name">
                            <?= htmlspecialchars($item['name']) ?>
                            <?php if (!$item['enabled']): ?>
                                <span class="disabled-badge">Disabled</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-desc"><?= htmlspecialchars($item['description']) ?></div>
                    </td>
 
                    <!-- Category -->
                    <td>
                        <span class="category-badge"><?= htmlspecialchars($item['category']) ?></span>
                    </td>
 
                    <!-- Price -->
                    <td>
                        <span class="item-price">$<?= number_format((float)$item['price'], 2) ?></span>
                    </td>
 
                    <!-- Toggle -->
                    <td>
                        <label class="toggle-label" title="<?= $item['enabled'] ? 'Enabled' : 'Disabled' ?>">
                            <input
                                type="checkbox"
                                <?= $item['enabled'] ? 'checked' : '' ?>
                                onchange="toggleItem(this, <?= $index ?>)"
                            >
                            <span class="toggle-track"></span>
                        </label>
                    </td>
 
                    <!-- Actions -->
                    <td>
                        <div class="action-cell">
                            <button class="action-btn" title="Edit item">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn delete" title="Delete item" onclick="confirmDelete(<?= $index ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
 
        <!-- Footer / pagination -->
        <div class="table-footer">
            <span class="showing-text">
                Showing 1–<?= count($menuItems) ?> of <?= $totalItems ?> Menu Items
            </span>
 
            <div class="pagination">
                <?php for ($p = 1; $p <= min(3, $totalPages); $p++): ?>
                    <button class="page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></button>
                <?php endfor; ?>
 
                <?php if ($totalPages > 4): ?>
                    <span class="page-ellipsis">...</span>
                    <button class="page-btn"><?= $totalPages ?></button>
                <?php endif; ?>
            </div>
        </div>
 
    </div>
    <!-- end menu table -->
 
</div>

<!-- end main -->
 
<script src="js/menumanagement.js"></script>

</body>
</html>