window.isFirmoweWifi = 0;

function checkNetworkType() {
    fetch('/api/network/check')
        .then(res => res.json())
        .then(data => {
            window.isFirmoweWifi = data.connection_type === 'wifi_firmowe' ? 1 : 0;
            console.log(window.isFirmoweWifi ? 'Połączono z siecią firmową WiFi' : 'Nie jesteś połączony z siecią firmową WiFi');
        })
        .catch(() => { window.isFirmoweWifi = 0; });
}
document.addEventListener('DOMContentLoaded', checkNetworkType);

// Products management
window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

function getUsedProducts() {
    return new Set(
        Array.from(document.querySelectorAll('#produkty-lista tbody tr'))
            .map(tr => parseInt(tr.getAttribute('data-produkt-id')))
            .filter(Boolean)
    );
}

function updateAvailableProducts() {
    const usedProducts = getUsedProducts();
    const addButton = document.getElementById('dodaj-produkt');
    
    if (addButton) {
        const isDisabled = usedProducts.size >= window._produkty.length;
        addButton.disabled = isDisabled;
        addButton.classList.toggle('opacity-50', isDisabled);
        addButton.classList.toggle('cursor-not-allowed', isDisabled);
    }
    
    return usedProducts;
}

function addProductRow(productId = '', quantity = '') {
    const usedProducts = updateAvailableProducts();
    if (productId && usedProducts.has(productId)) {
        updateProductQuantity(productId, quantity);
        return;
    }

    const product = window._produkty.find(p => p.id === productId);
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

    const tbody = document.querySelector('#produkty-lista tbody');
    tbody.insertAdjacentHTML('beforeend', rowHTML);
    
    const quantityInput = tbody.lastElementChild.querySelector('input[type="number"]');
    quantityInput?.focus();
    quantityInput?.select();
    
    updateAvailableProducts();
}

function updateProductQuantity(productId, quantity) {
    const row = document.querySelector(`#produkty-lista tr[data-produkt-id="${productId}"]`);
    if (!row) {
        addProductRow(productId, quantity);
        return;
    }

    const input = row.querySelector('input[type="number"]');
    if (input) {
        input.value = (parseInt(input.value) || 0) + quantity;
        input.focus();
        input.select();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Event listeners
document.addEventListener('click', e => {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        updateAvailableProducts();
    }
    else if (e.target.classList.contains('product-name')) {
        const product = window._produkty.find(p => p.tw_nazwa === e.target.textContent.trim());
        if (product) updateProductQuantity(product.id, 1);
    }
});

// Product search
const searchInput = document.getElementById('product-search');
const suggestionsList = document.getElementById('product-suggestions');

function showSuggestions(matches) {
    suggestionsList.innerHTML = matches.length ? 
        matches.map(p => `<li class="px-3 py-2 hover:bg-blue-100 cursor-pointer" data-produkt-id="${p.id}">${p.tw_nazwa}</li>`).join('') : '';
    suggestionsList.classList.toggle('hidden', !matches.length);
}

searchInput.addEventListener('input', () => {
    const searchTerm = searchInput.value.trim().toLowerCase();
    if (!searchTerm) {
        showSuggestions([]);
        return;
    }
    
    const usedProducts = getUsedProducts();
    const matches = window._produkty
        .filter(p => p.tw_nazwa.toLowerCase().includes(searchTerm) && !usedProducts.has(p.id))
        .slice(0, 10);
    
    showSuggestions(matches);
});

suggestionsList.addEventListener('click', e => {
    if (e.target.tagName === 'LI') {
        updateProductQuantity(parseInt(e.target.dataset.produktId), 0);
        searchInput.value = '';
        showSuggestions([]);
    }
});

document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
        showSuggestions([]);
    }
});

// EAN handling
async function handleEanSearch(ean) {
    try {
        const response = await fetch(window.isFirmoweWifi ? '/api/check-ean-firmowe' : '/api/check-ean', {
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

        let product = window._produkty.find(p => p.tw_idabaco === data.produkt.tw_idabaco);
        if (!product) {
            product = {
                id: Math.max(0, ...window._produkty.map(p => p.id)) + 1,
                tw_nazwa: data.produkt.tw_nazwa,
                tw_idabaco: data.produkt.tw_idabaco,
                ean_codes: data.produkt.ean_codes || []
            };
            window._produkty.push(product);
        }

        updateProductQuantity(product.id, parseInt(quantity));
        return true;
    } catch (error) {
        console.error('Błąd:', error);
        alert(error.message);
        return false;
    }
}

document.getElementById('dodaj-ean').addEventListener('click', () => {
    const eanInput = document.getElementById('product-search-ean');
    if (!eanInput.value.trim()) {
        alert('Wpisz kod EAN/PLU');
        return;
    }
    handleEanSearch(eanInput.value);
    eanInput.value = '';
});

// EAN Scanner
const scanner = new Html5Qrcode("reader");
let isScanning = false;

document.getElementById('start-scan').addEventListener('click', async () => {
    if (isScanning) return;

    try {
        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
            alert("Brak dostępnych kamer");
            return;
        }

        $('#reader').show();
        await scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            async (decodedText) => {
                console.log("Zeskanowano:", decodedText);
                await scanner.stop();
                isScanning = false;
                $('#reader').hide();
                $('#scan-result').text(`Zeskanowano: ${decodedText}`);
                await handleEanSearch(decodedText);
            }
        );
        isScanning = true;
    } catch (err) {
        console.error("Błąd skanera:", err);
        alert(`Błąd skanera: ${err.message}`);
    }
});

// Order submission
document.getElementById('zamowienieForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const products = Array.from(document.querySelectorAll('#produkty-lista tbody tr'))
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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    updateAvailableProducts();
});