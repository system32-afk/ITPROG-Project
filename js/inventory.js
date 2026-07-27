/* ============================
   CONFIG
============================ */

const API_URL = "apis/inventory_api.php";

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
const historyTableBody = document.getElementById("historyTableBody");

const searchInput = document.getElementById("inventorySearch");

const nonPerishable = document.getElementById("nonPerishable");
const expiryInput = document.getElementById("addExpiry");

const lowStockCount = document.getElementById("lowStockCount");
const expiringSoonCount = document.getElementById("expiringSoonCount");

let selectedRow = null;

/* ============================
   HELPERS
============================ */

function formatExpiry(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-US", {
        month: "short",
        day: "2-digit",
        year: "numeric",
    });
}

function statusBadge(status, statusClass) {
    return `<span class="inventory-status ${statusClass}">${status}</span>`;
}

async function callApi(action, params = {}, method = "GET") {
    let response;

    if (method === "GET") {
        const query = new URLSearchParams({ action, ...params });
        response = await fetch(`${API_URL}?${query.toString()}`);
    } else {
        const body = new URLSearchParams({ action, ...params });
        response = await fetch(API_URL, { method: "POST", body });
    }

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.error || "Something went wrong.");
    }

    return data;
}

/* ============================
   REFRESH STATS (after add/update)
============================ */

function bumpStats(prevClass, nextClass) {
    // Cheap client-side nudge so the cards feel live; a full page
    // reload will always re-sync the exact numbers from the DB.
    if (!lowStockCount) return;

    let low = parseInt(lowStockCount.textContent, 10) || 0;

    const wasLow = prevClass && prevClass !== "good";
    const isLow = nextClass !== "good";

    if (!wasLow && isLow) low++;
    if (wasLow && !isLow) low = Math.max(0, low - 1);

    lowStockCount.textContent = low;
}

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

document.querySelectorAll(".close").forEach((btn) => {
    btn.addEventListener("click", closeAllModals);
});

document.querySelectorAll(".modal-footer .table-btn").forEach((btn) => {
    if (btn.textContent.trim() === "Cancel" || btn.textContent.trim() === "Close") {
        btn.addEventListener("click", closeAllModals);
    }
});

window.addEventListener("click", (e) => {
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

    document.querySelectorAll("#inventoryTable tbody tr").forEach((row) => {
        const item = row.cells[0].textContent.toLowerCase();
        row.style.display = item.includes(value) ? "" : "none";
    });
});

/* ============================
   SAVE (ADD) ITEM
============================ */

saveItemBtn.addEventListener("click", async function () {
    const name = document.getElementById("addName").value.trim();
    const stock = document.getElementById("addQuantity").value;
    const unit = document.getElementById("addUnit").value.trim();
    const threshold = document.getElementById("addThreshold").value;
    const expiry = document.getElementById("addExpiry").value;
    const isPerishable = !nonPerishable.checked;

    if (!name || stock === "" || !unit) {
        alert("Please complete all required fields.");
        return;
    }

    saveItemBtn.disabled = true;

    try {
        const data = await callApi(
            "add",
            {
                name,
                unit,
                stock,
                threshold: threshold || 0,
                expiry: isPerishable ? expiry : "",
                is_perishable: isPerishable ? 1 : 0,
            },
            "POST"
        );

        const row = document.createElement("tr");
        row.dataset.id = data.inventory_id;
        row.dataset.perishable = isPerishable ? "1" : "0";

        row.innerHTML = `
            <td>${name}</td>
            <td>${stock}</td>
            <td>${unit}</td>
            <td>${statusBadge(data.status, data.status_class)}</td>
            <td>${isPerishable ? formatExpiry(expiry) : "-"}</td>
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

        bumpStats("good", data.status_class);
        closeAllModals();
        alert("Inventory item added successfully!");
    } catch (err) {
        alert(err.message);
    } finally {
        saveItemBtn.disabled = false;
    }
});

/* ============================
   TABLE BUTTONS (Edit / History)
============================ */

tableBody.addEventListener("click", async function (e) {
    const button = e.target.closest("button");
    if (!button) return;

    const row = button.closest("tr");
    const inventoryId = row.dataset.id;

    if (button.classList.contains("history")) {
        historyTableBody.innerHTML = `<tr><td colspan="3">Loading...</td></tr>`;
        historyModal.style.display = "flex";

        try {
            const data = await callApi("history", { inventory_id: inventoryId });

            if (!data.history.length) {
                historyTableBody.innerHTML = `<tr><td colspan="3">No history yet for this item.</td></tr>`;
                return;
            }

            historyTableBody.innerHTML = data.history
                .map(
                    (entry) => `
                    <tr>
                        <td>${new Date(entry.changed_at).toLocaleString()}</td>
                        <td>${entry.action.charAt(0).toUpperCase() + entry.action.slice(1)}</td>
                        <td>${entry.change_description}</td>
                    </tr>
                `
                )
                .join("");
        } catch (err) {
            historyTableBody.innerHTML = `<tr><td colspan="3">${err.message}</td></tr>`;
        }

        return;
    }

    if (button.classList.contains("edit")) {
        selectedRow = row;

        document.getElementById("editName").value = row.cells[0].textContent.trim();
        document.getElementById("editStock").value = row.cells[1].textContent.trim();
        document.getElementById("editUnit").value = row.cells[2].textContent.trim();
        document.getElementById("editThreshold").value = "";
        document.getElementById("editExpiry").value = "";
        document.getElementById("changeReason").value = "";

        editModal.style.display = "flex";
    }
});

/* ============================
   UPDATE ITEM
============================ */

updateItemBtn.addEventListener("click", async function () {
    if (selectedRow === null) {
        alert("Please select an item to edit first.");
        return;
    }

    const name = document.getElementById("editName").value.trim();
    const stock = document.getElementById("editStock").value;
    const unit = document.getElementById("editUnit").value.trim();
    const threshold = document.getElementById("editThreshold").value;
    const expiry = document.getElementById("editExpiry").value;
    const reason = document.getElementById("changeReason").value.trim();

    if (!name || stock === "" || !reason) {
        alert("Name, stock, and a reason for the change are required.");
        return;
    }

    const prevBadge = selectedRow.querySelector(".inventory-status");
    const prevClass = prevBadge ? [...prevBadge.classList].find((c) => c !== "inventory-status") : "good";

    updateItemBtn.disabled = true;

    try {
        const data = await callApi(
            "update",
            {
                inventory_id: selectedRow.dataset.id,
                name,
                stock,
                unit,
                threshold,
                expiry,
                reason,
            },
            "POST"
        );

        selectedRow.cells[0].textContent = name;
        selectedRow.cells[1].textContent = stock;
        selectedRow.cells[2].textContent = unit;
        selectedRow.cells[3].innerHTML = statusBadge(data.status, data.status_class);

        if (expiry !== "") {
            selectedRow.cells[4].textContent = formatExpiry(expiry);
        }

        bumpStats(prevClass, data.status_class);
        closeAllModals();
        selectedRow = null;
        alert("Inventory updated successfully!");
    } catch (err) {
        alert(err.message);
    } finally {
        updateItemBtn.disabled = false;
    }
});