$(document).ready(function () {
    const $globalSearchInput = $('#szukaj-produkt');
    const $globalSuggestions = $('#lista-podpowiedzi');
    const $productContainer = $('#produkty-lista');

    let debounceTimer;
    let index = $productContainer.children().length || 1;

    const produkty = window._produkty || [];

    // --- Global search with AJAX ---
    $globalSearchInput.on('input', function () {
        clearTimeout(debounceTimer);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $globalSuggestions.hide().empty();
            return;
        }

        debounceTimer = setTimeout(() => {
            $.ajax({
                url: '/api/produkty/search',
                data: { q: query },
                success: function (products) {
                    $globalSuggestions.empty();
                    if (products.length === 0) {
                        $globalSuggestions.hide();
                        return;
                    }

                    products.forEach(p => {
                        $('<li>')
                            .text(p.tw_nazwa)
                            .attr('data-id', p.id)
                            .addClass('cursor-pointer px-2 py-1 hover:bg-gray-300')
                            .appendTo($globalSuggestions);
                    });
                    $globalSuggestions.show();
                },
                error: function () {
                    $globalSuggestions.hide();
                }
            });
        }, 300);
    });

    // --- Click on suggestion from global search ---
    $globalSuggestions.on('click', 'li', function () {
        const productId = $(this).data('id');
        const productName = $(this).text();

        addProductRow(productId, productName);
        $globalSearchInput.val('');
        $globalSuggestions.hide().empty();

        // Focus quantity
        const lastItem = $('#produkty-lista .produkt-item').last();
        focusQuantityField(lastItem);
    });

    // --- Focus helper ---
    function focusQuantityField($item) {
        const $iloscInput = $item.find('input[type="number"]');
        if ($iloscInput.length) {
            setTimeout(() => {
                $iloscInput.focus().select();
            }, 50);
        }
    }

    // --- Add product row ---
    function addProductRow(productId = null, productName = '', qty = 1) {
        // If product already exists, increment quantity
        if (productId) {
            let found = false;
            $productContainer.find('.produkt-item').each(function () {
                const $hiddenId = $(this).find('.produkt-id-hidden');
                if ($hiddenId.val() == productId) {
                    const $iloscInput = $(this).find('input[type="number"]');
                    $iloscInput.val(parseInt($iloscInput.val()) + qty);
                    focusQuantityField($(this));
                    found = true;
                    return false;
                }
            });
            if (found) return;
        }

        const $newItem = $(`
            <div class="flex items-center gap-2 mb-2 produkt-item">
                <input
                    type="text"
                    name="produkty[${index}][tw_nazwa]"
                    class="form-input w-full autocomplete-input text-black"
                    placeholder="Wpisz nazwę produktu"
                    required
                    autocomplete="off"
                    value="${productName}"
                >
                <input type="hidden" name="produkty[${index}][produkt_id]" class="produkt-id-hidden" value="${productId ?? ''}">
                <input
                    type="number"
                    name="produkty[${index}][ilosc]"
                    min="1" max="3000"
                    class="form-input w-24 text-black"
                    placeholder="Ilość"
                    required
                    value="${qty}"
                >
                <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
            </div>
        `);

        $productContainer.append($newItem);
        attachRowAutocomplete($newItem.find('.autocomplete-input'));
        focusQuantityField($newItem);
        index++;
    }

    // --- Remove product row ---
    $productContainer.on('click', '.remove-item', function () {
        $(this).closest('.produkt-item').remove();
    });

    // --- Hide global suggestions on click outside ---
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#lista-podpowiedzi, #szukaj-produkt').length) {
            $globalSuggestions.hide().empty();
        }
    });

    // --- Autocomplete inside rows ---
    function attachRowAutocomplete($input) {
        let timer = null;
        const $localSuggestions = $('<ul class="absolute z-10 bg-white text-black max-h-40 overflow-auto border w-full" style="display:none;"></ul>');
        $input.after($localSuggestions);

        $input.on('input', function () {
            clearTimeout(timer);
            const val = $(this).val().trim();

            if (val.length < 2) {
                $localSuggestions.hide().empty();
                return;
            }

            timer = setTimeout(() => {
                const matches = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val.toLowerCase()));
                if (matches.length === 0) {
                    $localSuggestions.hide().empty();
                    return;
                }

                $localSuggestions.empty();
                matches.forEach(p => {
                    $('<li>')
                        .text(p.tw_nazwa)
                        .attr('data-id', p.id)
                        .addClass('cursor-pointer px-2 py-1 hover:bg-gray-300')
                        .appendTo($localSuggestions);
                });
                $localSuggestions.show();
            }, 200);
        });

        $localSuggestions.on('click', 'li', function () {
            const productId = $(this).data('id');
            const productName = $(this).text();

            $input.val(productName);
            $input.siblings('.produkt-id-hidden').val(productId);
            $localSuggestions.hide().empty();

            const $parentItem = $input.closest('.produkt-item');
            focusQuantityField($parentItem);
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest($localSuggestions).length && e.target !== $input[0]) {
                $localSuggestions.hide().empty();
            }
        });
    }

    // Attach autocomplete to existing inputs on load
    $productContainer.find('.autocomplete-input').each(function () {
        attachRowAutocomplete($(this));
    });

    // --- Add product manually ---
    $('#dodaj-produkt').on('click', () => {
        addProductRow();
    });

    // --- EAN Scanner ---
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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ kod_ean: decodedText })
            })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err });
                    return res.json();
                })
                .then(data => {
                    const qty = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
                    if (qty && !isNaN(qty) && parseInt(qty) > 0) {
                        addProductRow(data.produkt.id.toString(), data.produkt.tw_nazwa, parseInt(qty));
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
});
