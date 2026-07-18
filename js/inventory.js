/* ============================
   ELEMENTS
============================ */

const addModal = document.getElementById("addInventoryModal");
const editModal = document.getElementById("editInventoryModal");
const historyModal = document.getElementById("historyModal");

const openAddBtn = document.getElementById("openAddModal");
const saveItemBtn = document.getElementById("saveItemBtn");
const updateItemBtn = document.getElementById("updateItemBtn");

const tableBody = document.querySelector("#inventoryTable tbody");

const searchInput = document.getElementById("inventorySearch");

const nonPerishable = document.getElementById("nonPerishable");
const expiryInput = document.getElementById("addExpiry");

let selectedRow = null;

/* ============================
   OPEN ADD MODAL
============================ */

openAddBtn.addEventListener("click", () => {
    addModal.style.display = "flex";
});

/* ============================
   CLOSE MODALS
============================ */

function closeAllModals() {

    addModal.style.display = "none";
    editModal.style.display = "none";
    historyModal.style.display = "none";

}

document.querySelectorAll(".close").forEach(btn => {

    btn.addEventListener("click", closeAllModals);

});

document.querySelectorAll(".modal-footer .table-btn").forEach(btn => {

    if (
        btn.textContent.trim() === "Cancel" ||
        btn.textContent.trim() === "Close"
    ) {
        btn.addEventListener("click", closeAllModals);
    }

});

window.addEventListener("click", e => {

    if (e.target === addModal) closeAllModals();
    if (e.target === editModal) closeAllModals();
    if (e.target === historyModal) closeAllModals();

});

/* ============================
   NON PERISHABLE
============================ */

nonPerishable.addEventListener("change", function () {

    expiryInput.disabled = this.checked;

    if (this.checked) {

        expiryInput.value = "";

    }

});

/* ============================
   SEARCH
============================ */

searchInput.addEventListener("keyup", function () {

    const value = this.value.toLowerCase();

    document.querySelectorAll("#inventoryTable tbody tr").forEach(row => {

        const item = row.cells[0].textContent.toLowerCase();

        row.style.display =
            item.includes(value) ? "" : "none";

    });

});

/* ============================
   SAVE ITEM
============================ */

saveItemBtn.addEventListener("click", function () {

    const name = document.getElementById("addName").value.trim();
    const stock = document.getElementById("addQuantity").value;
    const unit = document.getElementById("addUnit").value.trim();
    const threshold = document.getElementById("addThreshold").value;
    const expiry = document.getElementById("addExpiry").value;

    if (!name || !stock || !unit) {

        alert("Please complete all required fields.");
        return;

    }

    let status = "In Stock";
    let statusClass = "good";

    if (Number(stock) <= 0) {

        status = "Out of Stock";
        statusClass = "out";

    } else if (Number(stock) <= Number(threshold)) {

        status = "Low Stock";
        statusClass = "low";

    }

    const formattedExpiry = expiry
        ? new Date(expiry).toLocaleDateString("en-US", {
              month: "short",
              day: "2-digit",
              year: "numeric"
          })
        : "-";

    const row = document.createElement("tr");

    row.innerHTML = `
        <td>${name}</td>
        <td>${stock}</td>
        <td>${unit}</td>
        <td>
            <span class="inventory-status ${statusClass}">
                ${status}
            </span>
        </td>
        <td>${formattedExpiry}</td>
        <td>
            <button class="table-btn edit">Edit</button>
            <button class="table-btn history">History</button>
        </td>
    `;

    tableBody.appendChild(row);

    document.getElementById("addName").value = "";
    document.getElementById("addQuantity").value = "";
    document.getElementById("addUnit").value = "";
    document.getElementById("addThreshold").value = "";
    document.getElementById("addExpiry").value = "";
    nonPerishable.checked = false;
    expiryInput.disabled = false;

    closeAllModals();

    alert("Inventory item added successfully!");

});

/* ============================
   TABLE BUTTONS
============================ */

tableBody.addEventListener("click", function (e) {

    const button = e.target.closest("button");

    if (!button) return;

    if (button.classList.contains("history")) {

        historyModal.style.display = "flex";
        return;

    }

    if (button.classList.contains("edit")) {

        selectedRow = button.closest("tr");

        document.getElementById("editName").value =
            selectedRow.cells[0].textContent.trim();

        document.getElementById("editStock").value =
            selectedRow.cells[1].textContent.trim();

        document.getElementById("editUnit").value =
            selectedRow.cells[2].textContent.trim();

        document.getElementById("editThreshold").value = "";
        document.getElementById("editExpiry").value = "";
        document.getElementById("changeReason").value = "";

        editModal.style.display = "flex";
    }

});

/* ============================
   UPDATE ITEM
============================ */

updateItemBtn.addEventListener("click", function () {

    if (selectedRow === null) {
        alert("Please select an item to edit first.");
        return;
    }

    const name = document.getElementById("editName").value.trim();
    const stock = document.getElementById("editStock").value;
    const unit = document.getElementById("editUnit").value.trim();
    const threshold = document.getElementById("editThreshold").value;
    const expiry = document.getElementById("editExpiry").value;

    let status = "In Stock";
    let statusClass = "good";

    if (Number(stock) <= 0) {
        status = "Out of Stock";
        statusClass = "out";
    } else if (threshold !== "" && Number(stock) <= Number(threshold)) {
        status = "Low Stock";
        statusClass = "low";
    }

    selectedRow.cells[0].textContent = name;
    selectedRow.cells[1].textContent = stock;
    selectedRow.cells[2].textContent = unit;

    selectedRow.cells[3].innerHTML = `
        <span class="inventory-status ${statusClass}">
            ${status}
        </span>
    `;

    if (expiry !== "") {
        selectedRow.cells[4].textContent =
            new Date(expiry).toLocaleDateString("en-US", {
                month: "short",
                day: "2-digit",
                year: "numeric"
            });
    }

    closeAllModals();

    selectedRow = null;

    alert("Inventory updated successfully!");

});
