const searchInput = document.getElementById("searchInput");

if(searchInput){

    searchInput.addEventListener("keyup", function(){

        const filter = this.value.toLowerCase();

        /* dashboard table search */
        document.querySelectorAll("tbody tr").forEach(row => {

            const text = row.textContent.toLowerCase();

            if(text.includes(filter)){
                row.style.display = "";
            }
            else{
                row.style.display = "none";
            }

        });

        /* Live queue card search */
        document.querySelectorAll(".order-card").forEach(card => {

            if(card.classList.contains("empty-order-card")){
                return;
            }

            const text = card.textContent.toLowerCase();

            if(text.includes(filter)){
                card.style.display = "";
            }
            else{
                card.style.display = "none";
            }

        });

    });

}