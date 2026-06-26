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