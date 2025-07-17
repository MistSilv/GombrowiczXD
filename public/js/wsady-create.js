$(document).ready(function () {
    const $searchInput = $('#szukaj-produkt');
    const $suggestions = $('#lista-podpowiedzi');
    const $produktyLista = $('#produkty-lista');

    let debounceTimer;
    let index = $produktyLista.children().length || 1; // continue index after existing items

    // Cached products from server
    const produkty = window._produkty || [];

    // --- Autocomplete AJAX search with debounce ---
    $searchInput.on('input', function () {
        clearTimeout(debounceTimer);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $suggestions.hide().empty();
            return;
        }

        debounceTimer = setTimeout(() => {
            $.ajax({
                url: '/produkty/search',
                data: { q: query },
                success: function (products) {
                    $suggestions.empty();
                    if (products.length === 0) {
                        $suggestions.hide();
                        return;
                    }

                    products.forEach(p => {
                        $('<li>')
                            .text(p.tw_nazwa)
                            .attr('data-id', p.id)
                            .addClass('cursor-pointer px-2 py-1 hover:bg-gray-300')
                            .appendTo($suggestions);
                    });
                    $suggestions.show();
                },
                error: function () {
                    $suggestions.hide();
                }
            });
        }, 300);
    });

    // --- Handle click on autocomplete suggestion ---
    $suggestions.on('click', 'li', function () {
        const productId = $(this).data('id');
        const productName = $(this).text();

        dodajProduktDoListy(productId, productName);
        $searchInput.val('');
        $suggestions.hide().empty();
    });

    // --- Add product row to the list ---
    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        // Check if product already exists - just increase quantity
        if (produktId) {
            const existingSelect = $produktyLista.find(`select option:selected[value="${produktId}"]`);
            if (existingSelect.length) {
                const $inputIlosc = existingSelect.closest('.produkt-item').find('input[type="number"]');
                $inputIlosc.val(parseInt($inputIlosc.val()) + ilosc).focus().select();
                return;
            }
        }

        // Build options for select
        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(p => {
            const selected = (produktId && p.id == produktId) ? 'selected' : '';
            options += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
        });

        const newItem = $(`
            <div class="flex items-center gap-2 mb-2 produkt-item">
                <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                    ${options}
                </select>
                <input type="number" name="produkty[${index}][ilosc]" min="1" max="3000" value="${ilosc}" class="form-input w-24 text-black" placeholder="Ilość" required>
                <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
            </div>
        `);

        $produktyLista.append(newItem);
        const $selectNowy = newItem.find('select');

        // On select change: merge duplicates or focus input
        $selectNowy.on('change', function () {
            const val = $(this).val();
            if (!val) return;

            const duplicates = $produktyLista.find(`select`).filter(function () {
                return $(this).val() === val && this !== $selectNowy[0];
            });

            if (duplicates.length) {
                const $input = duplicates.closest('.produkt-item').find('input[type="number"]');
                $input.val(parseInt($input.val()) + 1).focus().select();
                newItem.remove();
            } else {
                const $inputIlosc = newItem.find('input[type="number"]');
                $inputIlosc.focus().select();
            }
        });

        index++;
    }

    // --- Remove product row ---
    $produktyLista.on('click', '.remove-item', function () {
        $(this).closest('.produkt-item').remove();
    });

    // --- Hide suggestions when clicking outside ---
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#lista-podpowiedzi, #szukaj-produkt').length) {
            $suggestions.hide().empty();
        }
    });

    // --- EAN Scanner setup ---
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    function onScanSuccess(decodedText) {
        scanner.stop().then(() => {
            isScanning = false;
            $('#reader').hide();
            $('#scan-result').text(`Zeskanowano: ${decodedText}`);

            const token = $('meta[name="csrf-token"]').attr('content');

            fetch('/api/check-ean', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ kod_ean: decodedText })
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw err });
                return res.json();
            })
            .then(data => {
                const ilosc = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
                if (ilosc && !isNaN(ilosc) && parseInt(ilosc) > 0) {
                    dodajProduktDoListy(data.produkt.id.toString(), data.produkt.tw_nazwa, parseInt(ilosc));
                } else {
                    alert("Nieprawidłowa ilość.");
                }
            })
            .catch(err => alert(err.message || 'Błąd przy sprawdzaniu kodu.'));
        });
    }

    $('#start-scan').on('click', () => {
        if (isScanning) return;

        Html5Qrcode.getCameras()
            .then(devices => {
                if (devices.length) {
                    $('#reader').show();
                    scanner.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: 250 },
                        onScanSuccess
                    ).then(() => {
                        isScanning = true;
                    }).catch(err => {
                        alert("Błąd startu skanera: " + err);
                    });
                } else {
                    alert("Brak kamer.");
                }
            })
            .catch(err => alert("Błąd pobierania kamer: " + err));
    });

    // --- Handle initial selects on page load ---
    $produktyLista.find('select').each(function () {
        $(this).on('change', function () {
            const val = $(this).val();
            if (!val) return;

            const duplicates = $produktyLista.find('select').filter(function () {
                return $(this).val() === val && this !== this;
            });

            if (duplicates.length) {
                const $input = duplicates.closest('.produkt-item').find('input[type="number"]');
                $input.val(parseInt($input.val()) + 1).focus().select();
                $(this).closest('.produkt-item').remove();
            }
        });
    });

});
