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