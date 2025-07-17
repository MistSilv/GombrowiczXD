document.addEventListener('DOMContentLoaded', function () {
    let index = document.querySelectorAll('#produkty-list .produkt-row').length || 1;
    const produkty = window._produkty || [];

    function focusIlosc(element) {
        const produktRow = element.closest('.produkt-row');
        if (!produktRow) return;
        const iloscInput = produktRow.querySelector('input[type="number"]');
        if (iloscInput) {
            iloscInput.focus();
            setTimeout(() => iloscInput.select(), 100);
        }
    }

    // --- Autocomplete dla pojedynczego inputu ---
    function attachAutocomplete(input) {
        let timer = null;
        let suggestions = document.createElement('ul');
        suggestions.className = 'autocomplete-suggestions absolute z-10 bg-gray-700 text-white max-h-40 overflow-auto border border-gray-600 rounded w-full';
        suggestions.style.display = 'none';
        suggestions.style.position = 'absolute';
        suggestions.style.listStyle = 'none';
        suggestions.style.margin = 0;
        suggestions.style.padding = '0';
        suggestions.style.maxHeight = '160px';
        suggestions.style.overflowY = 'auto';
        suggestions.style.boxSizing = 'border-box';

        input.parentNode.style.position = 'relative'; // dla pozycji absolute podpowiedzi
        input.parentNode.appendChild(suggestions);

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const val = input.value.trim().toLowerCase();
            if (val.length < 2) {
                suggestions.style.display = 'none';
                suggestions.innerHTML = '';
                input.nextElementSibling.value = ''; // czyścimy produkt_id jeśli jest
                return;
            }
            timer = setTimeout(() => {
                // filtruj produkty lokalnie
                const matches = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val));
                if (matches.length === 0) {
                    suggestions.style.display = 'none';
                    suggestions.innerHTML = '';
                    input.nextElementSibling.value = '';
                    return;
                }
                suggestions.innerHTML = '';
                matches.forEach(p => {
                    const li = document.createElement('li');
                    li.textContent = p.tw_nazwa;
                    li.dataset.id = p.id;
                    li.style.padding = '6px 10px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('mouseenter', () => li.style.backgroundColor = '#374151');
                    li.addEventListener('mouseleave', () => li.style.backgroundColor = '');
                    li.addEventListener('click', () => {
                        input.value = p.tw_nazwa;
                        // ukryte input produkt_id jest zaraz po input tekstowym
                        input.nextElementSibling.value = p.id;
                        suggestions.style.display = 'none';
                        suggestions.innerHTML = '';
                        focusIlosc(input);
                    });
                    suggestions.appendChild(li);
                });
                suggestions.style.display = 'block';
            }, 200);
        });

        // schowaj podpowiedzi po kliknięciu poza input i suggestions
        document.addEventListener('click', (e) => {
            if (e.target !== input && !suggestions.contains(e.target)) {
                suggestions.style.display = 'none';
                suggestions.innerHTML = '';
            }
        });
    }

    // --- Dołącz autocomplete do wszystkich istniejących inputów na starcie ---
    document.querySelectorAll('#produkty-list .produkt-row .autocomplete-input').forEach(input => {
        attachAutocomplete(input);
    });

    // --- Dodawanie nowego produktu ---
    document.getElementById('add-produkt').addEventListener('click', function () {
        const produktyList = document.getElementById('produkty-list');
        const count = produktyList.children.length;

        const newRow = document.createElement('div');
        newRow.classList.add('produkt-row', 'flex', 'flex-col', 'sm:flex-row', 'gap-2', 'items-stretch', 'sm:items-center');
        newRow.style.position = 'relative'; // dla podpowiedzi

        newRow.innerHTML = `
            <input
                type="text"
                name="produkty[${count}][tw_nazwa]"
                required
                placeholder="Wpisz nazwę produktu"
                autocomplete="off"
                class="form-input w-full text-white bg-gray-800 border border-gray-700 rounded px-3 py-2 autocomplete-input"
            />
            <input type="hidden" name="produkty[${count}][produkt_id]" class="produkt-id-hidden" value="">
            <input
                type="number"
                name="produkty[${count}][ilosc]"
                min="1"
                max="100"
                value="1"
                required
                class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >
            <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">
                X
            </button>
        `;

        produktyList.appendChild(newRow);

        // attach autocomplete do nowo dodanego inputa
        const newInput = newRow.querySelector('.autocomplete-input');
        attachAutocomplete(newInput);

        // focus na input tekstowy produktu
        newInput.focus();
    });

    // --- Usuwanie produktu ---
    document.getElementById('produkty-list').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-produkty')) {
            e.target.closest('.produkt-row').remove();
        }
    });
});
