document.addEventListener('DOMContentLoaded', () => {
    // Modal Elements
    const modal = document.getElementById('addMenuModal');
    const addBtn = document.querySelector('.add-item-btn');
    const closeBtn = document.querySelector('.close-btn');
    const addMenuForm = document.getElementById('addMenuForm');

    // Open Modal
    if (addBtn && modal) {
        addBtn.addEventListener('click', (e) => {
            e.preventDefault(); 
            modal.style.display = 'flex';
        });
    }

    // Close Modal Function
    const closeModal = () => {
        if (modal) {
            modal.style.display = 'none';
            if (addMenuForm) addMenuForm.reset();
        }
    };

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
});
