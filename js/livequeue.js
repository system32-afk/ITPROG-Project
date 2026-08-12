const API_URL = "apis/orders_api.php";

/* ============================
   HELPER
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

/* ============================
   SEARCH
============================ */

const searchInput = document.getElementById("searchInput");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const filter = this.value.toLowerCase().trim();

        document.querySelectorAll(".order-card").forEach(card => {

            if (card.classList.contains("empty-order-card")) return;

            const text = card.textContent.toLowerCase();

            card.style.display = text.includes(filter) ? "" : "none";

        });

    });

}

/* ============================
   CANCEL
============================ */

document.querySelectorAll(".order-card .cancel-btn").forEach(button => {

    button.addEventListener("click", async function (e) {

        e.stopPropagation();

        if (!confirm("Cancel this order?")) return;

        const card = this.closest(".order-card");
        const orderId = card.dataset.id;

        try {
            await callApi("updateStatus", { order_id: orderId, status: "canceled" }, "POST");
            card.remove();
        } catch (err) {
            alert(err.message);
        }

    });

});

/* ============================
   DONE
============================ */

document.querySelectorAll(".done-btn").forEach(button => {

    button.addEventListener("click", async function (e) {

        e.stopPropagation();

        if (!confirm("Mark order as completed?")) return;

        const card = this.closest(".order-card");
        const orderId = card.dataset.id;

        try {
            await callApi("updateStatus", { order_id: orderId, status: "done" }, "POST");
            card.remove();
        } catch (err) {
            alert(err.message);
        }

    });

});

/* ============================
   PROCESS (-> preparing)
============================ */

document.querySelectorAll(".process-btn").forEach(button => {

    button.addEventListener("click", async function (e) {

        e.stopPropagation();

        const card = this.closest(".order-card");
        const orderId = card.dataset.id;

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

    });

});

/* ============================
   SET PRIORITY
============================ */

document.querySelectorAll(".priority-btn").forEach(button => {

    button.addEventListener("click", async function (e) {

        e.stopPropagation();

        const card = this.closest(".order-card");
        const orderId = card.dataset.id;

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

    });

});

/* ============================
   SORT (client-side, unchanged)
============================ */

const sortSelect = document.getElementById("sortOrders");

sortSelect.addEventListener("change", function () {

    const container = document.querySelector(".live-queue-container");

    const cards = [...container.querySelectorAll(".order-card:not(.empty-order-card)")];

    if (this.value === "newest") {

        cards.sort((a, b) =>
            b.dataset.id - a.dataset.id
        );

    }

    if (this.value === "quantity") {

        cards.sort((a, b) =>
            b.dataset.quantity - a.dataset.quantity
        );

    }

    if (this.value === "delayed") {

        cards.sort((a, b) => {

            const aDelayed = a.dataset.status === "delayed";
            const bDelayed = b.dataset.status === "delayed";

            return bDelayed - aDelayed;

        });

    }

    cards.forEach(card => {

        container.insertBefore(
            card,
            document.querySelector(".empty-order-card")
        );

    });
});

/* ============================
   VERIFICATION CODE (client-side only, no DB persistence)
============================ */

const modal = document.getElementById("verificationModal");

const codeText = document.getElementById("verificationCode");

const closeBtn = document.getElementById("closeModalBtn");

const copyBtn = document.getElementById("copyCodeBtn");

document.querySelectorAll(".verify-btn").forEach(button => {

    button.addEventListener("click", () => {
        codeText.textContent = button.dataset.code;
        modal.style.display = "flex";
    });

});

closeBtn.addEventListener("click", () => {

    modal.style.display = "none";

});

copyBtn.addEventListener("click", () => {

    navigator.clipboard.writeText(codeText.textContent);

    alert("Verification code copied!");

});

window.addEventListener("click", function (e) {

    if (e.target === modal) {

        modal.style.display = "none";

    }

});