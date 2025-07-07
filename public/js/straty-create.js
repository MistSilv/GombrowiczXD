document.addEventListener('DOMContentLoaded', function () {
    function focusIlosc(selectElem) {
        const produktRow = selectElem.closest('.produkt-row');
        if (!produktRow) return;
        const iloscInput = produktRow.querySelector('input[type="number"]');
        if (iloscInput) {
            iloscInput.focus();
            setTimeout(() => iloscInput.select(), 100);
        }
    }

    // Nasłuchuj na wszystkie istniejące selecty
    document.querySelectorAll('#produkty-list select').forEach(select => {
        select.addEventListener('change', () => focusIlosc(select));
    });

    // Obsługa dodawania nowego produktu
    document.getElementById('add-produkt').addEventListener('click', function () {
        const produktyList = document.getElementById('produkty-list');
        const count = produktyList.children.length;

        const newRow = document.createElement('div');
        newRow.classList.add('produkt-row', 'flex', 'flex-col', 'sm:flex-row', 'gap-2', 'items-stretch', 'sm:items-center');

        // Pobierz opcje produktów z pierwszego selecta (z serwera)
        const selectOptions = produktyList.querySelector('select').innerHTML;

        newRow.innerHTML = `
            <select name="produkty[${count}][produkt_id]" required class="border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 flex-1 focus:outline-none focus:border-blue-500">
                ${selectOptions}
            </select>
            <input type="number" name="produkty[${count}][ilosc]" min="1" value="1" required class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500">
            <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">X</button>
        `;

        produktyList.appendChild(newRow);

        // Podpięcie eventu change do nowo dodanego selecta
        const newSelect = newRow.querySelector('select');
        newSelect.addEventListener('change', () => focusIlosc(newSelect));

        // Od razu ustaw focus na nowo dodanym select
        newSelect.focus();
    });

    // Usuwanie produktu z listy
    document.getElementById('produkty-list').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-produkty')) {
            e.target.parentElement.remove();
        }
    });
});