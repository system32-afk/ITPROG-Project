const state = {
    cart: [],
    selectedItem: null,
    quantity: 1
};

const elements = {
    searchInput: document.getElementById("searchInput"),

    categoryButtons: document.querySelectorAll(".category"),
    menuCards: document.querySelectorAll(".menu-card"),
    addButtons: document.querySelectorAll(".add-btn"),

    floatingCart: document.getElementById("floatingCart"),
    floatingCartCount: document.getElementById("floatingCartCount"),

    cartButton: document.getElementById("cartButton"),
    cartCount: document.getElementById("cartCount"),

    cartDrawer: document.getElementById("cartDrawer"),
    drawerOverlay: document.getElementById("drawerOverlay"),
    closeCart: document.getElementById("closeCart"),

    cartItems: document.getElementById("cartItems"),
    cartTotal: document.getElementById("cartTotal"),
    checkoutBtn: document.getElementById("checkoutBtn"),

    itemModal: document.getElementById("itemModal"),
    modalImage: document.getElementById("modalImage"),
    modalName: document.getElementById("modalName"),
    modalDescription: document.getElementById("modalDescription"),
    modalPrice: document.getElementById("modalPrice"),

    minusBtn: document.getElementById("minusBtn"),
    plusBtn: document.getElementById("plusBtn"),
    quantity: document.getElementById("quantity"),
    notes: document.getElementById("notes"),
    addToCartBtn: document.getElementById("addToCartBtn"),
    closeItemModal: document.getElementById("closeItemModal"),

    checkoutModal: document.getElementById("checkoutModal"),
    closeCheckoutModal: document.getElementById("closeCheckoutModal"),
    placeOrderBtn: document.getElementById("placeOrderBtn"),

    orderType: document.getElementById("orderType"),
    tableNumber: document.getElementById("tableNumber"),
    paymentMethod: document.getElementById("paymentMethod"),

    customerName: document.getElementById("customerName"),
    customerContact: document.getElementById("customerContact"),

    successModal: document.getElementById("successModal"),
    orderNumber: document.getElementById("orderNumber"),
    continueOrderingBtn: document.getElementById("continueOrderingBtn")
};

function peso(value) {
    return `₱${Number(value).toFixed(2)}`;
}

function generateOrderNumber() {
    return "PB-" + Date.now().toString().slice(-6);
}

function updateBadges() {

    const totalItems = state.cart.reduce((sum, item) => sum + item.quantity, 0);

    elements.cartCount.textContent = totalItems;
    elements.floatingCartCount.textContent = totalItems;

}

function calculateTotal() {

    return state.cart.reduce((sum, item) => {

        return sum + (item.price * item.quantity);

    }, 0);

}

function openModal(modal) {

    modal.style.display = "flex";
    document.body.style.overflow = "hidden";

}

function closeModal(modal) {

    modal.style.display = "none";
    document.body.style.overflow = "";

}

function openDrawer() {

    elements.cartDrawer.classList.add("open");
    elements.drawerOverlay.style.display = "block";

}

function closeDrawer() {

    elements.cartDrawer.classList.remove("open");
    elements.drawerOverlay.style.display = "none";

}

function resetItemModal() {

    state.quantity = 1;

    elements.quantity.textContent = state.quantity;
    elements.notes.value = "";

}

function loadItem(button) {

    state.selectedItem = {
        id: button.dataset.id,
        name: button.dataset.name,
        description: button.dataset.description,
        image: button.dataset.image,
        price: Number(button.dataset.price)
    };

    elements.modalImage.src = state.selectedItem.image;
    elements.modalName.textContent = state.selectedItem.name;
    elements.modalDescription.textContent = state.selectedItem.description;
    elements.modalPrice.textContent = peso(state.selectedItem.price);

    resetItemModal();
    openModal(elements.itemModal);

}

function clearCart() {

    state.cart = [];

    renderCart();
    updateBadges();

}

function addToCart() {

    if (!state.selectedItem) return;

    const existingItem = state.cart.find(item => item.id === state.selectedItem.id);

    if (existingItem) {

        existingItem.quantity += state.quantity;

        if (elements.notes.value.trim() !== "") {

            existingItem.notes = elements.notes.value.trim();

        }

    } else {

        state.cart.push({
            id: state.selectedItem.id,
            name: state.selectedItem.name,
            price: state.selectedItem.price,
            image: state.selectedItem.image,
            quantity: state.quantity,
            notes: elements.notes.value.trim()
        });

    }

    renderCart();
    updateBadges();
    closeModal(elements.itemModal);

}

function increaseCartQuantity(index) {

    state.cart[index].quantity++;

    renderCart();
    updateBadges();

}

function decreaseCartQuantity(index) {

    if (state.cart[index].quantity > 1) {

        state.cart[index].quantity--;

    } else {

        state.cart.splice(index, 1);

    }

    renderCart();
    updateBadges();

}

function removeCartItem(index) {

    state.cart.splice(index, 1);

    renderCart();
    updateBadges();

}

function renderCart() {

    elements.cartItems.innerHTML = "";

    if (state.cart.length === 0) {

        elements.cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>Your cart is empty.</p>
            </div>
        `;

        elements.cartTotal.textContent = peso(0);

        return;

    }

    state.cart.forEach((item, index) => {

        const cartItem = document.createElement("div");

        cartItem.className = "cart-item";

        cartItem.innerHTML = `

            <div class="cart-item-info">

                <img src="${item.image}" alt="${item.name}">

                <div>

                    <h4>${item.name}</h4>

                    <p>${peso(item.price)}</p>

                    ${
                        item.notes
                            ? `<small>${item.notes}</small>`
                            : ""
                    }

                </div>

            </div>

            <div class="cart-item-actions">

                <button class="cart-minus" data-index="${index}">
                    <i class="fa-solid fa-minus"></i>
                </button>

                <span>${item.quantity}</span>

                <button class="cart-plus" data-index="${index}">
                    <i class="fa-solid fa-plus"></i>
                </button>

                <button class="cart-remove" data-index="${index}">
                    <i class="fa-solid fa-trash"></i>
                </button>

            </div>

        `;

        elements.cartItems.appendChild(cartItem);

    });

    document.querySelectorAll(".cart-plus").forEach(button => {

        button.addEventListener("click", () => {

            increaseCartQuantity(Number(button.dataset.index));

        });

    });

    document.querySelectorAll(".cart-minus").forEach(button => {

        button.addEventListener("click", () => {

            decreaseCartQuantity(Number(button.dataset.index));

        });

    });

    document.querySelectorAll(".cart-remove").forEach(button => {

        button.addEventListener("click", () => {

            removeCartItem(Number(button.dataset.index));

        });

    });

    elements.cartTotal.textContent = peso(calculateTotal());

}

function updateQuantityDisplay() {

    elements.quantity.textContent = state.quantity;

}

function increaseItemQuantity() {

    state.quantity++;

    updateQuantityDisplay();

}

function decreaseItemQuantity() {

    if (state.quantity > 1) {

        state.quantity--;

        updateQuantityDisplay();

    }

}

function filterMenu(search = "", category = "All") {

    const keyword = search.toLowerCase();

    elements.menuCards.forEach(card => {

        const cardText = card.textContent.toLowerCase();
        const cardCategory = card.dataset.category;

        const matchesSearch = cardText.includes(keyword);

        const matchesCategory =
            category === "All" || cardCategory === category;

        card.style.display =
            matchesSearch && matchesCategory
                ? ""
                : "none";

    });

}

elements.searchInput.addEventListener("input", () => {

    const activeCategory =
        document.querySelector(".category.active").textContent.trim();

    filterMenu(elements.searchInput.value, activeCategory);

});

elements.categoryButtons.forEach(button => {

    button.addEventListener("click", () => {

        elements.categoryButtons.forEach(btn =>
            btn.classList.remove("active")
        );

        button.classList.add("active");

        filterMenu(
            elements.searchInput.value,
            button.textContent.trim()
        );

    });

});

elements.addButtons.forEach(button => {

    button.addEventListener("click", () => {

        loadItem(button);

    });

});

elements.plusBtn.addEventListener("click", increaseItemQuantity);

elements.minusBtn.addEventListener("click", decreaseItemQuantity);

elements.addToCartBtn.addEventListener("click", addToCart);

elements.closeItemModal.addEventListener("click", () => {

    closeModal(elements.itemModal);

});

elements.cartButton.addEventListener("click", openDrawer);

elements.floatingCart.addEventListener("click", openDrawer);

elements.closeCart.addEventListener("click", closeDrawer);

elements.drawerOverlay.addEventListener("click", closeDrawer);

elements.checkoutBtn.addEventListener("click", () => {

    if (state.cart.length === 0) {

        alert("Your cart is empty.");

        return;

    }

    closeDrawer();

    openModal(elements.checkoutModal);

});

elements.orderType.addEventListener("change", () => {

    if (elements.orderType.value === "Take Out") {

        elements.tableNumber.disabled = true;
        elements.tableNumber.value = "";

    } else {

        elements.tableNumber.disabled = false;

    }

});

elements.placeOrderBtn.addEventListener("click", () => {

    if (
        elements.customerName.value.trim() === "" ||
        elements.customerContact.value.trim() === ""
    ) {

        alert("Please complete the required information.");

        return;

    }

    const order = {

        orderNumber: generateOrderNumber(),

        customer: {

            name: elements.customerName.value.trim(),
            contact: elements.customerContact.value.trim()

        },

        orderType: elements.orderType.value,

        tableNumber: elements.tableNumber.value,

        paymentMethod: elements.paymentMethod.value,

        total: calculateTotal(),

        items: state.cart

    };

    console.log(order);

    closeModal(elements.checkoutModal);

    elements.orderNumber.textContent = order.orderNumber;

    openModal(elements.successModal);

});

elements.continueOrderingBtn.addEventListener("click", () => {

    clearCart();

    elements.customerName.value = "";
    elements.customerContact.value = "";
    elements.orderType.selectedIndex = 0;
    elements.tableNumber.selectedIndex = 0;
    elements.paymentMethod.selectedIndex = 0;

    closeModal(elements.successModal);

});

window.addEventListener("click", e => {

    if (e.target === elements.itemModal) {

        closeModal(elements.itemModal);

    }

    if (e.target === elements.checkoutModal) {

        closeModal(elements.checkoutModal);

    }

    if (e.target === elements.successModal) {

        closeModal(elements.successModal);

    }

});

window.addEventListener("keydown", e => {

    if (e.key === "Escape") {

        closeModal(elements.itemModal);
        closeModal(elements.checkoutModal);
        closeModal(elements.successModal);
        closeDrawer();

    }

});

function initialize() {

    updateBadges();

    renderCart();

    filterMenu();

}

initialize();