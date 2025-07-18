document.addEventListener('DOMContentLoaded', function () {
    const $globalSearchInput = document.getElementById('szukaj-produkt');
    const $globalSuggestions = document.getElementById('lista-podpowiedzi');
    const $productContainer = document.getElementById('produkty-list');
    const produkty = window._produkty || [];

    let index = $productContainer.children.length || 1;
    let debounceTimer;

    // --- Focus helper ---
    function focusQuantity(row) {
        const input = row.querySelector('input[type="number"]');
        if (input) {
            setTimeout(() => {
                input.focus();
                input.select();
            }, 50);
        }
    }

    // --- Global search input ---
    $globalSearchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim().toLowerCase();

        if (query.length < 2) {
            $globalSuggestions.style.display = 'none';
            $globalSuggestions.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            const matches = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(query));
            if (matches.length === 0) {
                $globalSuggestions.style.display = 'none';
                $globalSuggestions.innerHTML = '';
                return;
            }

            $globalSuggestions.innerHTML = '';
            matches.forEach(p => {
                const li = document.createElement('li');
                li.textContent = p.tw_nazwa;
                li.dataset.id = p.id;
                li.className = 'cursor-pointer px-2 py-1 hover:bg-gray-300';
                li.addEventListener('click', () => {
                    addProductRow(p.id, p.tw_nazwa);
                    $globalSearchInput.value = '';
                    $globalSuggestions.style.display = 'none';
                    $globalSuggestions.innerHTML = '';
                });
                $globalSuggestions.appendChild(li);
            });
            $globalSuggestions.style.display = 'block';
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#szukaj-produkt') && !e.target.closest('#lista-podpowiedzi')) {
            $globalSuggestions.style.display = 'none';
        }
    });

    // --- Add product row ---
    function addProductRow(productId = null, productName = '', qty = 1) {
        // Check if product exists
        if (productId) {
            const existing = Array.from($productContainer.querySelectorAll('.produkt-row')).find(row => {
                return row.querySelector('.produkt-id-hidden').value == productId;
            });

            if (existing) {
                const qtyInput = existing.querySelector('input[type="number"]');
                qtyInput.value = parseInt(qtyInput.value) + qty;
                focusQuantity(existing);
                return;
            }
        }

        const row = document.createElement('div');
        row.className = 'produkt-row flex flex-col sm:flex-row gap-2 items-stretch sm:items-center relative';
        row.innerHTML = `
            <input
                type="text"
                name="produkty[${index}][tw_nazwa]"
                required
                placeholder="Wpisz nazwę produktu"
                autocomplete="off"
                class="form-input w-full text-white bg-gray-800 border border-gray-700 rounded px-3 py-2 autocomplete-input"
                value="${productName}"
                ${productId ? 'readonly' : ''}
            />
            <input type="hidden" name="produkty[${index}][produkt_id]" class="produkt-id-hidden" value="${productId ?? ''}">
            <input
                type="number"
                name="produkty[${index}][ilosc]"
                min="1"
                max="100"
                value="${qty}"
                required
                class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >
            <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">
                X
            </button>
        `;

        $productContainer.appendChild(row);
        attachRowAutocomplete(row.querySelector('.autocomplete-input'));
        focusQuantity(row);
        index++;
    }

    // --- Attach autocomplete for row input ---
    function attachRowAutocomplete(input) {
        let timer = null;
        const suggestions = document.createElement('ul');
        suggestions.className = 'absolute z-10 bg-gray-700 text-white max-h-40 overflow-auto border border-gray-600 rounded w-full';
        suggestions.style.display = 'none';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(suggestions);

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const val = input.value.trim().toLowerCase();
            if (val.length < 2) {
                suggestions.style.display = 'none';
                suggestions.innerHTML = '';
                input.nextElementSibling.value = '';
                return;
            }
            timer = setTimeout(() => {
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
                        input.nextElementSibling.value = p.id;
                        suggestions.style.display = 'none';
                        suggestions.innerHTML = '';
                        focusQuantity(input.closest('.produkt-row'));
                    });
                    suggestions.appendChild(li);
                });
                suggestions.style.display = 'block';
            }, 200);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest(suggestions) && e.target !== input) {
                suggestions.style.display = 'none';
                suggestions.innerHTML = '';
            }
        });
    }

    // Attach autocomplete to existing rows
    document.querySelectorAll('.autocomplete-input').forEach(input => attachRowAutocomplete(input));

    // Add new product button
    document.getElementById('add-produkt').addEventListener('click', () => {
        addProductRow();
    });

    // Remove product
    $productContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-produkty')) {
            e.target.closest('.produkt-row').remove();
        }
    });
});
