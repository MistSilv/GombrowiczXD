document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-template');
                const row = document.getElementById('details-' + id);
                if (row.style.display === 'none') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Wczytaj produkty z JSON-a
window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

// Elementy formularza
const toggleBtn = document.getElementById('toggleIsWlasny');
const toggleKnob = document.getElementById('toggleKnob');
const isWlasnyInput = document.getElementById('is_wlasny');
const dodajEanBtn = document.getElementById('dodaj-ean');
const produktyListaTbody = document.querySelector('#produkty-lista tbody');
const productSearchInput = document.getElementById('product-search');
const productSearchEanInput = document.getElementById('product-search-ean');
const productSuggestions = document.getElementById('product-suggestions');

// Filtrowanie produktów wg is_wlasny
function getFilteredProdukty() {
    return window._produkty.filter(p => p.is_wlasny == isWlasnyInput.value);
}

// Produkty już dodane do tabeli
function getUsedProducts() {
    return new Set(
        Array.from(produktyListaTbody.querySelectorAll('tr'))
            .map(tr => parseInt(tr.getAttribute('data-produkt-id')))
            .filter(Boolean)
    );
}

// Reset formularza produktów
function resetFormProducts() {
    produktyListaTbody.innerHTML = '';
    productSearchInput.value = '';
    productSearchEanInput.value = '';
    productSuggestions.classList.add('hidden');
}

// Stan przycisku EAN
function updateDodajEanBtnState() {
    if (isWlasnyInput.value === '1') {
        dodajEanBtn.disabled = true;
        dodajEanBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        dodajEanBtn.disabled = false;
        dodajEanBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Dodaj produkt do tabeli
function addProductRow(productId = '', quantity = '') {
    const usedProducts = getUsedProducts();
    if (productId && usedProducts.has(productId)) {
        updateProductQuantity(productId, quantity);
        return;
    }
    const product = getFilteredProdukty().find(p => p.id === productId);
    if (!product) return;

    const rowHTML = `
        <tr data-produkt-abaco="${product.tw_idabaco}" data-produkt-id="${productId}">
            <td class="border border-gray-300 px-3 py-2 text-left">${product.tw_nazwa}</td>
            <td class="border border-gray-300 px-3 py-2 text-right">
                <input type="number" name="ilosci[${productId}]" min="0" max="3000" step="1"
                       value="${quantity || 0}" class="border rounded px-3 py-1 w-24 text-right text-black focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
                <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded transition remove-row">✕</button>
            </td>
        </tr>
    `;
    produktyListaTbody.insertAdjacentHTML('beforeend', rowHTML);
    const quantityInput = produktyListaTbody.lastElementChild.querySelector('input[type="number"]');
    quantityInput?.focus();
    quantityInput?.select();
}

// Zaktualizuj ilość produktu
function updateProductQuantity(productId, quantity) {
    const row = produktyListaTbody.querySelector(`tr[data-produkt-id="${productId}"]`);
    if (!row) {
        addProductRow(productId, quantity);
        return;
    }
    const input = row.querySelector('input[type="number"]');
    if (input) {
        input.value = (parseInt(input.value) || 0) + parseInt(quantity);
        input.focus();
        input.select();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Obsługa kliknięć (usuwanie produktu, kliknięcie na nazwę)
document.addEventListener('click', e => {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
    else if (e.target.classList.contains('product-name')) {
        const product = getFilteredProdukty().find(p => p.tw_nazwa === e.target.textContent.trim());
        if (product) updateProductQuantity(product.id, 1);
    }
});

// Autouzupełnianie produktów
function showSuggestions(matches) {
    productSuggestions.innerHTML = matches.length ?
        matches.map(p => `<li class="px-3 py-2 hover:bg-blue-100 cursor-pointer product-name" data-produkt-id="${p.id}">${p.tw_nazwa}</li>`).join('') : '';
    productSuggestions.classList.toggle('hidden', !matches.length);
}

productSearchInput.addEventListener('input', () => {
    const searchTerm = productSearchInput.value.trim().toLowerCase();
    if (!searchTerm) {
        showSuggestions([]);
        return;
    }
    const usedProducts = getUsedProducts();
    const matches = getFilteredProdukty()
        .filter(p => p.tw_nazwa.toLowerCase().includes(searchTerm) && !usedProducts.has(p.id))
        .slice(0, 10);
    showSuggestions(matches);
});

productSuggestions.addEventListener('click', e => {
    if (e.target.tagName === 'LI') {
        updateProductQuantity(parseInt(e.target.dataset.produktId), 0);
        productSearchInput.value = '';
        showSuggestions([]);
    }
});

document.addEventListener('click', e => {
    if (!productSearchInput.contains(e.target) && !productSuggestions.contains(e.target)) {
        showSuggestions([]);
    }
});

// Obsługa EAN/PLU
async function handleEanSearch(ean) {
    try {
        const response = await fetch('/api/check-ean', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ kod_ean: ean.trim() })
        });
        if (!response.ok) throw new Error('Błąd sieci');
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Nie znaleziono produktu');
        const quantity = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
        if (!quantity || isNaN(quantity)) throw new Error('Nieprawidłowa ilość');
        let product = getFilteredProdukty().find(p => p.tw_idabaco === data.produkt.tw_idabaco);
        if (!product) {
            product = {
                id: Math.max(0, ...window._produkty.map(p => p.id)) + 1,
                tw_nazwa: data.produkt.tw_nazwa,
                tw_idabaco: data.produkt.tw_idabaco,
                is_wlasny: parseInt(isWlasnyInput.value),
                ean_codes: data.produkt.ean_codes || []
            };
            window._produkty.push(product);
        }
        updateProductQuantity(product.id, parseInt(quantity));
        return true;
    } catch (error) {
        alert(error.message);
        return false;
    }
}

dodajEanBtn.addEventListener('click', () => {
    if (!productSearchEanInput.value.trim()) {
        alert('Wpisz kod EAN/PLU');
        return;
    }
    handleEanSearch(productSearchEanInput.value);
    productSearchEanInput.value = '';
});

// Obsługa switcha
toggleBtn.addEventListener('click', () => {
    const pressed = toggleBtn.getAttribute('aria-pressed') === 'true';
    toggleBtn.setAttribute('aria-pressed', !pressed);

    if (pressed) {
        // wyłączony
        toggleBtn.classList.remove('bg-blue-600');
        toggleBtn.classList.add('bg-gray-600');
        toggleKnob.classList.remove('translate-x-6');
        toggleKnob.classList.add('translate-x-1');
        isWlasnyInput.value = 0;
    } else {
        // włączony
        toggleBtn.classList.remove('bg-gray-600');
        toggleBtn.classList.add('bg-blue-600');
        toggleKnob.classList.remove('translate-x-1');
        toggleKnob.classList.add('translate-x-6');
        isWlasnyInput.value = 1;
    }
    resetFormProducts();
    updateDodajEanBtnState();
});

// Obsługa formularza zamówienia
document.getElementById('zamowienieForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const products = Array.from(produktyListaTbody.querySelectorAll('tr'))
        .map(tr => {
            const productId = parseInt(tr.getAttribute('data-produkt-id'));
            const product = window._produkty.find(p => p.id === productId);
            const quantity = parseInt(tr.querySelector('input[type="number"]').value);
            return product && quantity > 0 ? {
                tw_idabaco: product.tw_idabaco,
                tw_nazwa: product.tw_nazwa,
                ilosc: quantity,
                ean_codes: product.ean_codes || []
            } : null;
        })
        .filter(Boolean);
    if (!products.length) {
        alert('Dodaj przynajmniej jeden produkt przed zapisaniem');
        return;
    }
    const jsonInput = document.createElement('input');
    jsonInput.type = 'hidden';
    jsonInput.name = 'produkty_json';
    jsonInput.value = JSON.stringify(products);
    this.appendChild(jsonInput);
    this.submit();
});

// Inicjalizacja
document.addEventListener('DOMContentLoaded', () => {
    updateDodajEanBtnState();
});