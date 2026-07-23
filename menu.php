<?php

/* Dummy Menu Items */

$menuItems = [

    [
        "id" => 1,
        "name" => "Classic Burger",
        "category" => "Meals",
        "price" => 120,
        "description" => "Juicy beef patty with cheese and lettuce.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 2,
        "name" => "Chicken Burger",
        "category" => "Meals",
        "price" => 135,
        "description" => "Crispy chicken with mayo.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 3,
        "name" => "French Fries",
        "category" => "Snacks",
        "price" => 75,
        "description" => "Golden crispy fries.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 4,
        "name" => "Carbonara",
        "category" => "Meals",
        "price" => 180,
        "description" => "Creamy pasta with bacon.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 5,
        "name" => "Coke",
        "category" => "Drinks",
        "price" => 45,
        "description" => "Ice cold soft drink.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 6,
        "name" => "Iced Tea",
        "category" => "Drinks",
        "price" => 55,
        "description" => "Fresh brewed iced tea.",
        "image" => "https://placehold.co/600x400"
    ],

    [
        "id" => 7,
        "name" => "Chocolate Cake",
        "category" => "Desserts",
        "price" => 95,
        "description" => "Rich chocolate cake slice.",
        "image" => "https://placehold.co/600x400"
    ]

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Pabili Mobile Ordering</title>

    <link
        rel="stylesheet"
        href="css/menu.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

</head>

<body>

    <div class="container">

        <header>

            <div>

                <h1>Pabili</h1>

                <p>Kitchen Station #04</p>

            </div>

            <button id="cartButton">

                <i class="fa-solid fa-cart-shopping"></i>

                <span id="cartCount">0</span>

            </button>

        </header>

        <div class="search-container">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="searchInput"
                placeholder="Search menu...">

        </div>

        <div class="categories">

            <button class="category active">
                All
            </button>

            <button class="category">
                Meals
            </button>

            <button class="category">
                Snacks
            </button>

            <button class="category">
                Drinks
            </button>

            <button class="category">
                Desserts
            </button>

        </div>

        <div class="menu-grid">
            <?php foreach ($menuItems as $item): ?>

                <div
                    class="menu-card"
                    data-category="<?php echo $item['category']; ?>">

                    <img
                        src="<?php echo $item['image']; ?>"
                        alt="<?php echo $item['name']; ?>">

                    <div class="menu-content">

                        <div class="menu-top">

                            <h3>
                                <?php echo $item['name']; ?>
                            </h3>

                            <span class="price">
                                ₱<?php echo number_format($item['price']); ?>
                            </span>

                        </div>

                        <p class="description">

                            <?php echo $item['description']; ?>

                        </p>

                        <div class="menu-footer">

                            <span class="category-badge">

                                <?php echo $item['category']; ?>

                            </span>

                            <button
                                class="add-btn"
                                data-id="<?php echo $item['id']; ?>"
                                data-name="<?php echo $item['name']; ?>"
                                data-price="<?php echo $item['price']; ?>"
                                data-description="<?php echo $item['description']; ?>"
                                data-image="<?php echo $item['image']; ?>">

                                <i class="fa-solid fa-plus"></i>
                                Add
                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <button
            id="floatingCart"
            class="floating-cart">

            <i class="fa-solid fa-cart-shopping"></i>

            <span id="floatingCartCount">
                0
            </span>

        </button>

                <div
            id="itemModal"
            class="modal">

            <div class="modal-content">

                <span
                    id="closeItemModal"
                    class="close-modal">

                    &times;

                </span>

                <img
                    id="modalImage"
                    src=""
                    alt="Food Image">

                <div class="modal-body">

                    <h2 id="modalName">
                        Item Name
                    </h2>

                    <p
                        id="modalDescription"
                        class="modal-description">
                        Item description goes here.
                    </p>

                    <div class="modal-price">

                        ₱<span id="modalPrice">0</span>

                    </div>

                    <div class="quantity-container">

                        <span>
                            Quantity
                        </span>

                        <div class="quantity-controls">

                            <button id="minusBtn">

                                <i class="fa-solid fa-minus"></i>

                            </button>

                            <span id="quantity">

                                1

                            </span>

                            <button id="plusBtn">

                                <i class="fa-solid fa-plus"></i>

                            </button>

                        </div>

                    </div>

                    <label for="notes">
                        Special Instructions
                    </label>

                    <textarea
                        id="notes"
                        rows="4"
                        placeholder="Example: No onions, extra cheese..."></textarea>

                    <button
                        id="addToCartBtn"
                        class="primary-btn">

                        Add to Cart

                    </button>

                </div>

            </div>

        </div>

                <div
            id="cartDrawer"
            class="cart-drawer">

            <div class="cart-header">

                <h2>
                    Your Cart
                </h2>

                <button
                    id="closeCart"
                    class="close-cart">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

            <div
                id="cartItems"
                class="cart-items">

                <div class="empty-cart">

                    <i class="fa-solid fa-cart-shopping"></i>

                    <p>
                        Your cart is empty.
                    </p>

                </div>

            </div>

            <div class="cart-footer">

                <div class="cart-total">

                    <span>

                        Total

                    </span>

                    <strong>

                        ₱<span id="cartTotal">0</span>

                    </strong>

                </div>

                <button
                    id="checkoutBtn"
                    class="primary-btn">

                    Proceed to Checkout

                </button>

            </div>

        </div>

        <div
            id="drawerOverlay"
            class="drawer-overlay">

        </div>

                <div
            id="checkoutModal"
            class="modal">

            <div class="modal-content checkout-modal">

                <span
                    id="closeCheckoutModal"
                    class="close-modal">

                    &times;

                </span>

                <h2>
                    Checkout
                </h2>

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="customerName"
                        placeholder="Enter your name">

                </div>

                <div class="form-group">

                    <label>
                        Contact Number
                    </label>

                    <input
                        type="text"
                        id="customerNumber"
                        placeholder="09XXXXXXXXX">

                </div>

                <div class="form-group">

                    <label>
                        Order Type
                    </label>

                    <select id="orderType">

                        <option>
                            Dine In
                        </option>

                        <option>
                            Take Out
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Table Number
                    </label>

                    <input
                        type="number"
                        id="tableNumber"
                        placeholder="Optional">

                </div>

                <div class="form-group">

                    <label>
                        Payment Method
                    </label>

                    <select id="paymentMethod">

                        <option>
                            Cash
                        </option>

                        <option>
                            GCash
                        </option>

                    </select>

                </div>

                <button
                    id="placeOrderBtn"
                    class="primary-btn">

                    Place Order

                </button>

            </div>

        </div>

                <div
            id="successModal"
            class="modal">

            <div class="modal-content success-modal">

                <div class="success-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <h2>
                    Order Placed!
                </h2>

                <p>
                    Thank you for ordering with Pabili.
                </p>

                <div class="success-details">

                    <div class="success-row">

                        <span>
                            Order Number
                        </span>

                        <strong id="orderNumber">

                            #0001

                        </strong>

                    </div>

                    <div class="success-row">

                        <span>
                            Estimated Time
                        </span>

                        <strong>
                            15 - 20 mins
                        </strong>

                    </div>

                </div>

                <button
                    id="continueOrderingBtn"
                    class="primary-btn">

                    Continue Ordering

                </button>

            </div>

        </div>

    </div>

    <script src="js/menu.js"></script>

</body>

</html>
    