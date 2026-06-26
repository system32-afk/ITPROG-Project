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

document.querySelectorAll(".cancel-btn").forEach(button => {

    button.addEventListener("click", function(e){

        e.stopPropagation();

        if(confirm("Cancel this order?")){

            this.closest(".order-card").remove();

        }

    });

});

document.querySelectorAll(".done-btn").forEach(button => {

    button.addEventListener("click", function(e){

        e.stopPropagation();

        if(confirm("Mark order as completed?")){

            this.closest(".order-card").remove();

        }

    });

});

document.querySelectorAll(".process-btn").forEach(button=>{

    button.addEventListener("click",function(e){

        e.stopPropagation();

        const card=this.closest(".order-card");

        const badge=card.querySelector(".order-status-badge");

        badge.textContent="Preparing";

        badge.className="order-status-badge preparing";

        card.classList.remove("priority","pending","delayed");
        card.classList.add("preparing");

    });

});

document.querySelectorAll(".priority-btn").forEach(button=>{

    button.addEventListener("click",function(e){

        e.stopPropagation();

        const card=this.closest(".order-card");

        const badge=card.querySelector(".order-status-badge");

        badge.textContent="Priority";

        badge.className="order-status-badge priority";

        card.classList.remove("pending","preparing","delayed");
        card.classList.add("priority");

    });

});

const sortBtn = document.getElementById("sortNewest");

sortBtn.addEventListener("click",()=>{

    const container=document.querySelector(".live-queue-container");

    const cards=[...container.querySelectorAll(".order-card:not(.empty-order-card)")];

    cards.reverse().forEach(card=>container.prepend(card));

});