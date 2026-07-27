document.addEventListener('DOMContentLoaded', () => {

    const API_URL = "apis/menu_api.php";

    /* ============================
       ELEMENTS
    ============================ */

    const addModal = document.getElementById('addMenuModal');
    const editModal = document.getElementById('editMenuModal');

    const addBtn = document.querySelector('.add-item-btn');
    const closeBtn = document.querySelector('.close-btn:not(.edit-close-btn)');
    const editCloseBtn = document.querySelector('.edit-close-btn');

    const addMenuForm = document.getElementById('addMenuForm');
    const editMenuForm = document.getElementById('editMenuForm');

    const tableBody = document.querySelector('.menu-table-wrapper tbody');
    const searchInput = document.getElementById('searchInput');

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

    /* ============================
       ADD MODAL — open/close
    ============================ */

    if (addBtn && addModal) {
        addBtn.addEventListener('click', (e) => {
            e.preventDefault();
            addModal.style.display = 'flex';
        });
    }

    const closeAddModal = () => {
        if (addModal) {
            addModal.style.display = 'none';
            if (addMenuForm) addMenuForm.reset();
        }
    };

    if (closeBtn) closeBtn.addEventListener('click', closeAddModal);

    /* ============================
       EDIT MODAL — open/close
    ============================ */

    const closeEditModal = () => {
        if (editModal) editModal.style.display = 'none';
    };

    if (editCloseBtn) editCloseBtn.addEventListener('click', closeEditModal);

    window.addEventListener('click', (e) => {
        if (e.target === addModal) closeAddModal();
        if (e.target === editModal) closeEditModal();
    });

    /* ============================
       ADD ITEM
    ============================ */

    if (addMenuForm) {
        addMenuForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('addItemName').value.trim();
            const category = document.getElementById('addItemCategory').value;
            const station = document.getElementById('addItemStation').value;
            const price = document.getElementById('addItemPrice').value;
            const description = document.getElementById('addItemDescription').value.trim();
            const imageUrl = document.getElementById('addItemImage').value.trim();

            if (!name || !category || price === '') {
                alert('Please fill in the required fields.');
                return;
            }

            try {
                await callApi('add', {
                    name,
                    category,
                    station,
                    price,
                    description,
                    image_url: imageUrl,
                }, 'POST');

                // Reload so pagination/total counts stay accurate — the
                // new item lands on page 1 since the table sorts newest first.
                window.location.href = 'menumanagement.php?page=1';
            } catch (err) {
                alert(err.message);
            }
        });
    }

    /* ============================
       EDIT ITEM — open modal prefilled from the row's data-* attrs
    ============================ */

    tableBody.addEventListener('click', (e) => {
        const editButton = e.target.closest('.action-btn.edit');
        if (!editButton) return;

        const row = editButton.closest('tr');

        document.getElementById('editItemId').value = row.dataset.id;
        document.getElementById('editItemName').value = row.dataset.name;
        document.getElementById('editItemCategory').value = row.dataset.category;
        document.getElementById('editItemStation').value = row.dataset.station || '';
        document.getElementById('editItemPrice').value = row.dataset.price;
        document.getElementById('editItemDescription').value = row.dataset.description;
        document.getElementById('editItemImage').value = row.dataset.image;

        editModal.style.display = 'flex';
    });

    if (editMenuForm) {
        editMenuForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const itemId = document.getElementById('editItemId').value;
            const name = document.getElementById('editItemName').value.trim();
            const category = document.getElementById('editItemCategory').value;
            const station = document.getElementById('editItemStation').value;
            const price = document.getElementById('editItemPrice').value;
            const description = document.getElementById('editItemDescription').value.trim();
            const imageUrl = document.getElementById('editItemImage').value.trim();

            if (!name || !category || price === '') {
                alert('Please fill in the required fields.');
                return;
            }

            try {
                await callApi('update', {
                    item_id: itemId,
                    name,
                    category,
                    station,
                    price,
                    description,
                    image_url: imageUrl,
                }, 'POST');

                const row = tableBody.querySelector(`tr[data-id="${itemId}"]`);

                row.dataset.name = name;
                row.dataset.category = category;
                row.dataset.station = station;
                row.dataset.price = price;
                row.dataset.description = description;
                row.dataset.image = imageUrl;

                row.querySelector('.item-thumb').src = imageUrl;
                row.querySelector('.item-name').firstChild.textContent = name + ' ';
                row.querySelector('.item-desc').textContent = description;
                row.querySelector('.category-badge').textContent = category;
                row.querySelector('.item-price').textContent = '$' + parseFloat(price).toFixed(2);

                closeEditModal();
                alert('Item updated successfully!');
            } catch (err) {
                alert(err.message);
            }
        });
    }

    /* ============================
       TOGGLE (enable/disable)
    ============================ */

    tableBody.addEventListener('change', async (e) => {
        const toggle = e.target.closest('.toggle-input');
        if (!toggle) return;

        const row = toggle.closest('tr');
        const itemId = row.dataset.id;
        const enabled = toggle.checked ? 1 : 0;

        try {
            await callApi('toggle', { item_id: itemId, enabled }, 'POST');

            const nameCell = row.querySelector('.item-name');
            const existingBadge = nameCell.querySelector('.disabled-badge');

            if (enabled && existingBadge) {
                existingBadge.remove();
            } else if (!enabled && !existingBadge) {
                const badge = document.createElement('span');
                badge.className = 'disabled-badge';
                badge.textContent = 'Disabled';
                nameCell.appendChild(badge);
            }

            toggle.closest('.toggle-label').title = enabled ? 'Enabled' : 'Disabled';
        } catch (err) {
            alert(err.message);
            toggle.checked = !toggle.checked; // revert on failure
        }
    });

    /* ============================
       DELETE
    ============================ */

    tableBody.addEventListener('click', async (e) => {
        const deleteButton = e.target.closest('.action-btn.delete');
        if (!deleteButton) return;

        const row = deleteButton.closest('tr');
        const itemId = row.dataset.id;

        if (!confirm(`Delete "${row.dataset.name}"? This can't be undone.`)) {
            return;
        }

        try {
            await callApi('delete', { item_id: itemId }, 'POST');
            // Reload so pagination/total counts stay accurate.
            window.location.reload();
        } catch (err) {
            alert(err.message);
        }
    });

    /* ============================
       SEARCH
       Note: only filters the items on the current page, since
       listing is paginated server-side.
    ============================ */

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase().trim();

            tableBody.querySelectorAll('tr').forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

});