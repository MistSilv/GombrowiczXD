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
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end produkt-item">
                <div class="w-full sm:w-auto flex-grow">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nazwa produktu</label>
                    <input
                        type="text"
                        name="produkty[${index}][tw_nazwa]"
                        class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-transparent autocomplete-input"
                        placeholder="Wpisz nazwę produktu"
                        required
                        autocomplete="off"
                        value="${nazwaProduktu}">
                    <input type="hidden" name="produkty[${index}][produkt_id]" class="produkt-id-hidden" value="${produktId ?? ''}">
                </div>

                <div class="flex items-end gap-2">
                    <div class="w-24">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Ilość</label>
                        <input
                            type="number"
                            name="produkty[${index}][ilosc]"
                            min="1" max="3000"
                            class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="Ilość"
                            required
                            value="${ilosc}">
                    </div>

                    <button type="button" class="h-[42px] px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-item flex items-center justify-center">
                        Usuń
                    </button>
                </div>
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
