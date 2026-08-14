const API_URL = "apis/orders_api.php";
const QUEUE_STATE_URL = "apis/queue_state.php";
const REFRESH_INTERVAL_MS = 5000;

/* ============================
   HELPERS
============================ */

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

function escapeHtml(value) {
    const div = document.createElement("div");
    div.textContent = value ?? "";
    return div.innerHTML;
}

/* ============================
   ELEMENTS
============================ */

const container = document.querySelector(".live-queue-container");
const emptyCard = document.querySelector(".empty-order-card");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortOrders");
const activeOrdersEl = document.querySelector(".active-orders-number");
const delayedNumberEl = document.querySelector(".delayed-number");

const modal = document.getElementById("verificationModal");
const codeText = document.getElementById("verificationCode");
const closeBtn = document.getElementById("closeModalBtn");
const copyBtn = document.getElementById("copyCodeBtn");

/* ============================
   RENDER (used by auto-refresh)
============================ */

function buildOrderCardEl(order) {
    const statusClass = order.display_status.toLowerCase();

    const card = document.createElement("div");
    card.className = `order-card ${statusClass}`;
    card.dataset.id = order.order_id;
    card.dataset.status = statusClass;
    card.dataset.quantity = order.quantity_total;

    const timeExtra = order.display_status === "Delayed"
        ? `\u2022 ${escapeHtml(order.overrun_display)}`
        : `\u2022 Target: ${escapeHtml(order.target_display)}`;

    const itemsHtml = order.items.map(item => `
        <div class="order-item">
            <span class="item-qty">${item.quantity}x</span>
            <span class="item-name">${escapeHtml(item.name)}</span>
            <span class="item-price">₱${(item.price * item.quantity).toFixed(2)}</span>
            <span class="item-station">${escapeHtml(item.station || "")}</span>
        </div>
    `).join("");

    const orderTotalHtml = `
        <div class="order-total">
            <span>Total</span>
            <span class="order-total-amount">₱${Number(order.order_total).toFixed(2)}</span>
        </div>
    `;

    const verifyHtml = (order.payment_method === "Cash" && order.status === "Awaiting Payment")
        ? `
        <div class="order-actions">
            <button class="verify-btn" data-code="${escapeHtml(order.verification_code)}">
                Generate Verification Code
            </button>
        </div>`
        : "";

    card.innerHTML = `
        <div class="order-header">
            <h3>${escapeHtml(order.order_number)}</h3>
            <span class="order-status-badge ${statusClass}">${escapeHtml(order.display_status)}</span>
        </div>
        <div class="order-content">
            <p class="customer-name">${escapeHtml(order.customer_name)}</p>
            <p class="order-time">${escapeHtml(order.elapsed_display)} ${timeExtra}</p>
            <hr>
            ${itemsHtml}
            ${orderTotalHtml}
        </div>
        <div class="order-actions">
            <button class="done-btn">Done</button>
            <button class="cancel-btn">Cancel</button>
        </div>
        <div class="order-actions">
            <button class="process-btn">Process</button>
            <button class="priority-btn">Set Priority</button>
        </div>
        ${verifyHtml}
    `;

    return card;
}

function renderOrders(orders) {
    container.querySelectorAll(".order-card:not(.empty-order-card)").forEach(card => card.remove());

    const searchFilter = searchInput ? searchInput.value.toLowerCase().trim() : "";

    orders.forEach(order => {
        const card = buildOrderCardEl(order);

        if (searchFilter && !card.textContent.toLowerCase().includes(searchFilter)) {
            card.style.display = "none";
        }

        container.insertBefore(card, emptyCard);
    });

    if (sortSelect && sortSelect.value) {
        applySort(sortSelect.value);
    }
}

function updateStats(data) {
    if (activeOrdersEl) activeOrdersEl.textContent = data.activeOrdersCount;
    if (delayedNumberEl) delayedNumberEl.textContent = data.delayedCount;
}

/* ============================
   AUTO REFRESH (AJAX, every 5s)
============================ */

let refreshInFlight = false;

async function refreshQueue() {
    if (refreshInFlight) return;
    refreshInFlight = true;

    try {
        const response = await fetch(QUEUE_STATE_URL, { cache: "no-store" });
        if (!response.ok) throw new Error("Failed to refresh queue");

        const data = await response.json();

        renderOrders(data.orders);
        updateStats(data);
    } catch (err) {
        // Fail silently on a missed poll -- the next 5s tick will retry.
        console.error("Auto-refresh failed:", err);
    } finally {
        refreshInFlight = false;
    }
}

setInterval(refreshQueue, REFRESH_INTERVAL_MS);

/* ============================
   SEARCH
============================ */

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const filter = this.value.toLowerCase().trim();

        container.querySelectorAll(".order-card").forEach(card => {

            if (card.classList.contains("empty-order-card")) return;

            const text = card.textContent.toLowerCase();

            card.style.display = text.includes(filter) ? "" : "none";

        });

    });

}

/* ============================
   SORT (client-side)
============================ */

function applySort(value) {

    const cards = [...container.querySelectorAll(".order-card:not(.empty-order-card)")];

    if (value === "newest") {
        cards.sort((a, b) => b.dataset.id - a.dataset.id);
    }

    if (value === "quantity") {
        cards.sort((a, b) => b.dataset.quantity - a.dataset.quantity);
    }

    if (value === "delayed") {
        cards.sort((a, b) => {
            const aDelayed = a.dataset.status === "delayed";
            const bDelayed = b.dataset.status === "delayed";
            return bDelayed - aDelayed;
        });
    }

    cards.forEach(card => container.insertBefore(card, emptyCard));
}

if (sortSelect) {
    sortSelect.addEventListener("change", function () {
        applySort(this.value);
    });
}

/* ============================
   ACTIONS
   Event delegation on the container so cards swapped in by
   auto-refresh keep working without re-binding listeners.
============================ */

container.addEventListener("click", async function (e) {

    const card = e.target.closest(".order-card");
    if (!card || card.classList.contains("empty-order-card")) return;

    const orderId = card.dataset.id;

    if (e.target.closest(".cancel-btn")) {
        e.stopPropagation();
        if (!confirm("Cancel this order?")) return;

        try {
            await callApi("updateStatus", { order_id: orderId, status: "canceled" }, "POST");
            card.remove();
        } catch (err) {
            alert(err.message);
        }
        return;
    }

    if (e.target.closest(".done-btn")) {
        e.stopPropagation();
        if (!confirm("Mark order as completed?")) return;

        try {
            await callApi("updateStatus", { order_id: orderId, status: "done" }, "POST");
            card.remove();
        } catch (err) {
            alert(err.message);
        }
        return;
    }

    if (e.target.closest(".process-btn")) {
        e.stopPropagation();

        try {
            await callApi("updateStatus", { order_id: orderId, status: "preparing" }, "POST");

            const badge = card.querySelector(".order-status-badge");
            badge.textContent = "Preparing";
            badge.className = "order-status-badge preparing";

            card.classList.remove("priority", "pending", "delayed");
            card.classList.add("preparing");
            card.dataset.status = "preparing";
        } catch (err) {
            alert(err.message);
        }
        return;
    }

    if (e.target.closest(".priority-btn")) {
        e.stopPropagation();

        try {
            await callApi("updateStatus", { order_id: orderId, status: "priority" }, "POST");

            const badge = card.querySelector(".order-status-badge");
            badge.textContent = "Priority";
            badge.className = "order-status-badge priority";

            card.classList.remove("pending", "preparing", "delayed");
            card.classList.add("priority");
            card.dataset.status = "priority";
        } catch (err) {
            alert(err.message);
        }
        return;
    }

    if (e.target.closest(".verify-btn")) {
        const btn = e.target.closest(".verify-btn");
        codeText.textContent = btn.dataset.code;
        modal.style.display = "flex";
        return;
    }

});

/* ============================
   VERIFICATION CODE MODAL
============================ */

if (closeBtn) {
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });
}

if (copyBtn) {
    copyBtn.addEventListener("click", () => {
        navigator.clipboard.writeText(codeText.textContent);
        alert("Verification code copied!");
    });
}

window.addEventListener("click", function (e) {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});