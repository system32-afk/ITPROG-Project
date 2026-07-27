const API_URL = "apis/dashboard_api.php";
const REFRESH_INTERVAL_MS = 10000; // 10s -- adjust if you want it snappier/slower

const searchInput = document.getElementById("searchInput");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const filter = this.value.toLowerCase().trim();

        document.querySelectorAll("#ordersTable tr").forEach(row => {

            const text = row.textContent.toLowerCase();

            row.style.display = text.includes(filter) ? "" : "none";

        });

    });

}
const generateQRBtn = document.getElementById("generateQRBtn");
const qrModal = document.getElementById("qrModal");
const closeQRBtn = document.getElementById("closeQRBtn");

generateQRBtn.addEventListener("click", function(){

    qrModal.style.display = "flex";

});

closeQRBtn.addEventListener("click", function(){

    qrModal.style.display = "none";

});

window.addEventListener("click", function(e){

    if(e.target === qrModal){

        qrModal.style.display = "none";

    }

});

/* ============================
   AUTO-REFRESH
============================ */

const ordersTodayStat = document.getElementById("ordersTodayStat");
const revenueTodayStat = document.getElementById("revenueTodayStat");
const activeOrdersStat = document.getElementById("activeOrdersStat");
const lowStockStat = document.getElementById("lowStockStat");
const ordersTable = document.getElementById("ordersTable");
const inventoryAlertsList = document.getElementById("inventoryAlertsList");

function renderRecentOrders(orders) {
    ordersTable.innerHTML = "";

    if (orders.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 3;
        cell.textContent = "No orders yet.";
        row.appendChild(cell);
        ordersTable.appendChild(row);
        return;
    }

    orders.forEach(order => {
        const row = document.createElement("tr");
        row.addEventListener("click", () => {
            window.location = `vieworder.php?id=${order.id}`;
        });

        const idCell = document.createElement("td");
        idCell.textContent = order.id;

        const customerCell = document.createElement("td");
        customerCell.textContent = order.customer;

        const statusCell = document.createElement("td");
        const badge = document.createElement("span");
        badge.className = `status ${order.status.toLowerCase()}`;
        badge.textContent = order.status;
        statusCell.appendChild(badge);

        row.append(idCell, customerCell, statusCell);
        ordersTable.appendChild(row);
    });
}

function renderInventoryAlerts(alerts) {
    inventoryAlertsList.innerHTML = "";

    if (alerts.length === 0) {
        const item = document.createElement("div");
        item.className = "alert-item";
        item.innerHTML = `
            <div class="alert-title">All good</div>
            <div class="alert-text">No stock or expiry alerts right now.</div>
        `;
        inventoryAlertsList.appendChild(item);
        return;
    }

    alerts.forEach(alert => {
        const item = document.createElement("div");
        item.className = "alert-item";

        const title = document.createElement("div");
        title.className = "alert-title";
        title.textContent = alert.item;

        const text = document.createElement("div");
        text.className = "alert-text";
        text.textContent = alert.message;

        item.append(title, text);
        inventoryAlertsList.appendChild(item);
    });
}

async function refreshDashboard() {
    try {
        const response = await fetch(`${API_URL}?action=stats`);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || "Something went wrong.");
        }

        ordersTodayStat.textContent = data.stats.ordersToday;
        revenueTodayStat.textContent = data.stats.revenueToday;
        activeOrdersStat.textContent = data.stats.activeOrders;
        lowStockStat.textContent = data.stats.lowStockItems;

        renderRecentOrders(data.recentOrders);
        renderInventoryAlerts(data.inventoryAlerts);
    } catch (err) {
        // Silent on purpose -- a failed background refresh shouldn't
        // interrupt someone actively looking at the dashboard. It'll
        // just try again on the next interval.
        console.error("Dashboard refresh failed:", err);
    }
}

setInterval(refreshDashboard, REFRESH_INTERVAL_MS);