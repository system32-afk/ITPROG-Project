// ===============================
// Inventory Search
// ===============================

const inventorySearch = document.getElementById("inventorySearch");

if (inventorySearch) {

    inventorySearch.addEventListener("keyup", function () {

        const filter = this.value.toLowerCase().trim();

        document.querySelectorAll("#inventoryTable tbody tr").forEach(row => {

            const text = row.textContent.toLowerCase();

            row.style.display = text.includes(filter) ? "" : "none";

        });

    });

}


// ===============================
// Edit Ingredient
// ===============================

document.querySelectorAll(".table-btn.edit").forEach(button => {

    button.addEventListener("click", function () {

        const item = this.closest("tr").children[0].textContent.trim();

        alert("Edit Ingredient: " + item);

        // TODO:
        // Open the Edit Ingredient modal here.

    });

});


// ===============================
// Delete Ingredient
// ===============================

document.querySelectorAll(".table-btn.delete").forEach(button => {

    button.addEventListener("click", function () {

        const row = this.closest("tr");

        const item = row.children[0].textContent.trim();

        if (confirm(`Delete "${item}" from inventory?`)) {

            row.remove();

        }

    });

});


// ===============================
// Add New Stock
// ===============================

const addStockBtn = document.querySelector(".new-order-btn");

if (addStockBtn) {

    addStockBtn.addEventListener("click", function () {

        alert("Open Add New Stock form.");

        // TODO:
        // Open your Add New Stock modal
        // or redirect to addstock.php

    });

});