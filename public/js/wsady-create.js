$(document).ready(function () {
    const $searchInput = $('#szukaj-produkt');
    const $suggestions = $('#lista-podpowiedzi');
    const $produktyLista = $('#produkty-lista');

    let debounceTimer;
    let index = $produktyLista.children().length || 1;

    // Cached products from server
    const produkty = window._produkty || [];

    // --- Autocomplete AJAX search with debounce (search input on top) ---
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

    // --- When user clicks a suggestion on top search input ---
    $suggestions.on('click', 'li', function () {
        const productId = $(this).data('id');
        const productName = $(this).text();

        dodajProduktDoListy(productId, productName);
        $searchInput.val('');
        $suggestions.hide().empty();
    });

    function focusIlosc(item) {
        const iloscInput = item.querySelector('input[type="number"]');
        if (iloscInput) {
            setTimeout(() => {
                iloscInput.focus();
                iloscInput.select();
            }, 50);
        }
    }


    // --- Add product row to the list with input text + hidden ID + qty ---
    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        // Check if product already exists in list by produktId and increase qty
        if (produktId) {
            let found = false;
            $produktyLista.find('.produkt-item').each(function () {
                const $hiddenId = $(this).find('.produkt-id-hidden');
                if ($hiddenId.val() == produktId) {
                    const $iloscInput = $(this).find('input[type="number"]');
                    $iloscInput.val(parseInt($iloscInput.val()) + ilosc).focus().select();
                    found = true;
                    return false; // break each
                }
            });
            if (found) return;
        }

        // Create new product row
        const $newItem = $(`
            <div class="flex items-center gap-2 mb-2 produkt-item">
                <input
                    type="text"
                    name="produkty[${index}][tw_nazwa]"
                    class="form-input w-full autocomplete-input text-black"
                    placeholder="Wpisz nazwę produktu"
                    required
                    autocomplete="off"
                    value="${nazwaProduktu}"
                >
                <input type="hidden" name="produkty[${index}][produkt_id]" class="produkt-id-hidden" value="${produktId ?? ''}">
                <input
                    type="number"
                    name="produkty[${index}][ilosc]"
                    min="1" max="3000"
                    class="form-input w-24 text-black"
                    placeholder="Ilość"
                    required
                    value="${ilosc}"
                >
                <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
            </div>
        `);

        $produktyLista.append($newItem);

        // Attach autocomplete to new input
        attachAutocomplete($newItem.find('.autocomplete-input'));

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

    // --- Autocomplete for input text fields in product rows ---
    function attachAutocomplete($input) {
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
            $input.focus();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest($localSuggestions).length && e.target !== $input[0]) {
                $localSuggestions.hide().empty();
            }
        });
    }

    // Attach autocomplete to existing inputs on page load
    $produktyLista.find('.autocomplete-input').each(function () {
        attachAutocomplete($(this));
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
});
