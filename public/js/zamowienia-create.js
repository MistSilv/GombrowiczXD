$(document).ready(function () {
    const $produktyLista = $('#produkty-lista');
    let index = $produktyLista.children().length || 1;
    const produkty = (window._produkty || []).filter(p => p.is_wlasny);

    function attachAutocomplete($input) {
        let timer = null;
        const $suggestions = $('<ul class="absolute z-10 bg-white text-black max-h-40 overflow-auto border w-full" style="display:none;"></ul>');
        $input.after($suggestions);

        $input.on('input', function () {
            clearTimeout(timer);
            const val = $(this).val().trim();

            if (val.length < 2) {
                $suggestions.hide().empty();
                return;
            }

            timer = setTimeout(() => {
                const matches = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val.toLowerCase()));
                if (matches.length === 0) {
                    $suggestions.hide().empty();
                    return;
                }

                $suggestions.empty();
                matches.forEach(p => {
                    $('<li>')
                        .text(p.tw_nazwa)
                        .attr('data-id', p.id)
                        .addClass('cursor-pointer px-2 py-1 hover:bg-gray-300')
                        .appendTo($suggestions);
                });
                $suggestions.show();
            }, 200);
        });

        $suggestions.on('click', 'li', function () {
            const productId = $(this).data('id');
            const productName = $(this).text();

            $input.val(productName);
            $input.siblings('.produkt-id-hidden').val(productId);
            $suggestions.hide().empty();

            const $iloscInput = $input.closest('.produkt-item').find('input[type="number"]');
            $iloscInput.focus().select();
        });


        $(document).on('click', function (e) {
            if (!$(e.target).closest($suggestions).length && e.target !== $input[0]) {
                $suggestions.hide().empty();
            }
        });
    }

    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        // Sprawdź czy produkt już jest
        if (produktId) {
            let found = false;
            $produktyLista.find('.produkt-item').each(function () {
                const $hiddenId = $(this).find('.produkt-id-hidden');
                if ($hiddenId.val() == produktId) {
                    const $iloscInput = $(this).find('input[type="number"]');
                    $iloscInput.val(parseInt($iloscInput.val()) + ilosc).focus().select();
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
        attachAutocomplete($newItem.find('.autocomplete-input'));
        index++;
    }

    // Obsługa przycisku dodawania
    $('#dodaj-produkt').on('click', function () {
        dodajProduktDoListy();
    });

    // Usuwanie produktu
    $produktyLista.on('click', '.remove-item', function () {
        $(this).closest('.produkt-item').remove();
    });

    // Autocomplete dla pierwszego inputa
    $produktyLista.find('.autocomplete-input').each(function () {
        attachAutocomplete($(this));
    });

    // ✅ Nowa sekcja – globalna wyszukiwarka produktów
    const $searchInput = $('#szukaj-produkt');
    const $globalSuggestions = $('#lista-podpowiedzi');

    let debounceTimer;
    $searchInput.on('input', function () {
        clearTimeout(debounceTimer);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $globalSuggestions.hide().empty();
            return;
        }

        debounceTimer = setTimeout(() => {
            const matches = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(query.toLowerCase()));
            if (matches.length === 0) {
                $globalSuggestions.hide().empty();
                return;
            }

            $globalSuggestions.empty();
            matches.forEach(p => {
                $('<li>')
                    .text(p.tw_nazwa)
                    .attr('data-id', p.id)
                    .addClass('cursor-pointer px-2 py-1 hover:bg-gray-300')
                    .appendTo($globalSuggestions);
            });
            $globalSuggestions.show();
        }, 300);
    });

    $globalSuggestions.on('click', 'li', function () {
        const productId = $(this).data('id');
        const productName = $(this).text();

        dodajProduktDoListy(productId, productName);

        // ✅ Focus na pole ilości w nowo dodanym wierszu
        const lastItem = $('#produkty-lista .produkt-item').last();
        const iloscInput = lastItem.find('input[type="number"]');
        if (iloscInput.length) {
            iloscInput.focus().select();
        }

        $searchInput.val('');
        $globalSuggestions.hide().empty();
    });


    $(document).on('click', function (e) {
        if (!$(e.target).closest($globalSuggestions).length && e.target !== $searchInput[0]) {
            $globalSuggestions.hide().empty();
        }
    });
});
