let index = 0;

$(document).ready(function () {
    const $globalSearchInput = $('#szukaj-produkt');
    const $globalSuggestions = $('#lista-podpowiedzi');
    const $productContainer = $('#produkty-lista');

    let debounceTimer;
    let index = $productContainer.children().length || 1;

    const produkty = window._produkty || [];

}


    // --- Globalne wyszukiwanie z użyciem AJAX ---
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
                            .addClass('cursor-pointer px-4 py-2 hover:bg-gray-600 text-white')
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

    // --- Kliknięcie na podpowiedź z wyszukiwarki globalnej ---
    $globalSuggestions.on('click', 'li', function () {
        const productId = $(this).data('id');
        const productName = $(this).text();

        addProductRow(productId, productName);
        $globalSearchInput.val('');
        $globalSuggestions.hide().empty();

        // Ustawienie fokusu na pole ilości
        const lastItem = $('#produkty-lista .produkt-item').last();
        focusQuantityField(lastItem);
    });

    // --- Pomocnicza funkcja ustawiająca fokus na pole ilości ---
    function focusQuantityField($item) {
        const $iloscInput = $item.find('input[type="number"]');
        if ($iloscInput.length) {
            setTimeout(() => {
                $iloscInput.focus().select();
            }, 50);
        }
    }

    // --- Dodawanie nowego wiersza produktu ---
    function addProductRow(productId = null, productName = '', qty = 1) {
        // Jeśli produkt już istnieje, zwiększ ilość
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
            <div class="produkt-item mb-4">
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                    <div class="w-full sm:w-auto flex-grow">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nazwa produktu</label>
                        <input
                            type="text"
                            name="produkty[${index}][tw_nazwa]"
                            class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="Wpisz nazwę produktu"
                            required
                            autocomplete="off"
                            value="${productName}">
                        <input type="hidden" name="produkty[${index}][produkt_id]" class="produkt-id-hidden" value="${productId ?? ''}">
                    </div>
                    
                    <div class="flex items-end gap-3 w-full sm:w-auto">
                        <div class="flex-1 min-w-[6rem]">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Ilość</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    name="produkty[${index}][ilosc]"
                                    min="1" max="3000"
                                    class="flex-1 px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                    placeholder="Ilość"
                                    required
                                    value="${qty}">
                                    
                                <button type="button" class="h-[42px] px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-item flex items-center justify-center">
                                    Usuń
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);

        $productContainer.append($newItem);
        attachRowAutocomplete($newItem.find('input[type="text"]'));
        focusQuantityField($newItem);
        index++;
    }

    // --- Usuwanie wiersza produktu ---
    $productContainer.on('click', '.remove-item', function () {
        $(this).closest('.produkt-item').remove();
    });

    // --- Ukrywanie globalnych podpowiedzi po kliknięciu poza ---
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#lista-podpowiedzi, #szukaj-produkt').length) {
            $globalSuggestions.hide().empty();
        }
    });

    // --- Podpowiedzi (autocomplete) w wierszach ---
    function attachRowAutocomplete($input) {
        let timer = null;
        const $localSuggestions = $('<ul class="absolute z-10 bg-gray-700 text-white max-h-60 overflow-auto border border-gray-600 rounded-lg w-full mt-1 shadow-lg" style="display:none;"></ul>');
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
                        .addClass('cursor-pointer px-4 py-2 hover:bg-gray-600')
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

    // --- Podłącz autocomplete do istniejących pól po załadowaniu ---
    $productContainer.find('input[type="text"]').each(function () {
        attachRowAutocomplete($(this));
    });

    // --- Dodanie produktu ręcznie ---
    $('#dodaj-produkt').on('click', () => {
        addProductRow();
    });

    // --- Obsługa skanera EAN ---
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

    // --- Rozpoczęcie skanowania ---
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


