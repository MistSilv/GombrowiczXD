window._produkty = window._produkty || [];

const templateButtons = document.querySelectorAll('.template-select-btn');
const templateDetails = document.querySelectorAll('.template-details');

templateButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.templateId;
        const selectedDetail = document.querySelector(`.template-details[data-template-id="${id}"]`);
        const isVisible = selectedDetail.style.display === 'block';

        // Zamknij wszystkie szczegóły
        templateDetails.forEach(detail => {
            detail.style.display = 'none';

            const editBtn = detail.querySelector('.edit-btn');
            const saveBtn = detail.querySelector('.save-btn');
            const addBtn = detail.querySelector('.dodaj-produkt');
            const inputs = detail.querySelectorAll('.product-amount-input, .product-name-input');
            const removeBtns = detail.querySelectorAll('.remove-product-btn');
            const nameInput = detail.querySelector('input[name="nazwa"]');

            // Resetuj stan edycji
            editBtn.textContent = '✏️ Modyfikuj';
            editBtn.dataset.editing = "false";
            saveBtn.classList.add('hidden');
            addBtn.classList.add('hidden');
            nameInput.disabled = true;
            inputs.forEach(i => i.disabled = true);
            removeBtns.forEach(b => b.classList.add('hidden'));
        });

        // Jeżeli kliknięty był już otwarty — to go zamknęliśmy powyżej, więc nie otwieramy
        if (!isVisible) {
            selectedDetail.style.display = 'block';
        }
    });
});

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const container = button.closest('.template-details');
        const inputs = container.querySelectorAll('.product-amount-input, .product-name-input');
        const removeBtns = container.querySelectorAll('.remove-product-btn');
        const saveBtn = container.querySelector('.save-btn');
        const addBtn = container.querySelector('.dodaj-produkt');
        const nameInput = container.querySelector('input[name="nazwa"]');
        const editing = button.dataset.editing === "true";
        const productsTable = container.querySelector('tbody.products-table');

        if (editing) {
            button.textContent = '✏️ Modyfikuj';
            saveBtn.classList.add('hidden');
            addBtn.classList.add('hidden');
            removeBtns.forEach(b => b.classList.add('hidden'));
            nameInput.disabled = true;

            if (productsTable.dataset.originalHtml !== undefined) {
                productsTable.innerHTML = productsTable.dataset.originalHtml;

                productsTable.querySelectorAll('.remove-product-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        btn.closest('tr').remove();
                    });
                });

                productsTable.querySelectorAll('.product-name-input').forEach(input => {
                    setupAutocomplete($(input));
                });
            }

            inputs.forEach(input => {
                if (input.dataset.originalValue !== undefined) {
                    input.value = input.dataset.originalValue;
                }
                input.disabled = true;
            });

            if (nameInput.dataset.originalValue !== undefined) {
                nameInput.value = nameInput.dataset.originalValue;
            }

            inputs.forEach(input => {
                delete input.dataset.originalValue;
            });
            delete nameInput.dataset.originalValue;
            delete productsTable.dataset.originalHtml;

            button.dataset.editing = "false";
        } else {
            button.textContent = 'Anuluj';
            saveBtn.classList.remove('hidden');
            addBtn.classList.remove('hidden');
            removeBtns.forEach(b => b.classList.remove('hidden'));
            nameInput.disabled = false;

            inputs.forEach(input => {
                if (input.dataset.originalValue === undefined) {
                    input.dataset.originalValue = input.value;
                }

                if (input.classList.contains('product-amount-input')) {
                    input.disabled = false;
                } else if (input.classList.contains('product-name-input')) {
                    input.disabled = true;
                }
            });

            if (nameInput.dataset.originalValue === undefined) {
                nameInput.dataset.originalValue = nameInput.value;
            }

            if (productsTable.dataset.originalHtml === undefined) {
                productsTable.dataset.originalHtml = productsTable.innerHTML;
            }

            button.dataset.editing = "true";
        }
    });
});

document.querySelectorAll('.save-btn').forEach(button => {
    button.addEventListener('click', () => {
        const container = button.closest('.template-details');
        const templateId = container.dataset.templateId;
        const produkty = [];

        container.querySelectorAll('tbody.products-table tr').forEach(row => {
            const nameInput = row.querySelector('.product-name-input');
            const amountInput = row.querySelector('.product-amount-input');

            if (!nameInput || !amountInput) return;

            const produkt_id = nameInput.getAttribute('data-produkt-id');
            const ilosc = amountInput.value;

            if (produkt_id && ilosc !== null && ilosc !== '') {
                produkty.push({ produkt_id, ilosc });
            }
        });

        const nazwa = container.querySelector('input[name="nazwa"]').value;

        fetch(`/wsad-template/${templateId}/full-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ nazwa, produkty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Zapisano zmiany.");
                location.reload();
            } else {
                alert("Coś poszło nie tak.");
            }
        });
    });
});

document.querySelectorAll('.remove-product-btn').forEach(button => {
    button.addEventListener('click', () => {
        const row = button.closest('tr');
        row.remove();
    });
});

function setupAutocomplete($input) {
    $input.on('input', function() {
        const val = this.value.toLowerCase();
        const suggestions = window._produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val));
        closeAutocompleteList();

        if (!val) return;

        const list = $('<div class="autocomplete-items bg-gray-700 text-white border border-gray-600 rounded absolute z-50"></div>');
        list.css({
            top: $input.position().top + $input.outerHeight(),
            left: $input.position().left,
            width: $input.outerWidth()
        });

        suggestions.forEach(item => {
            const itemDiv = $('<div class="cursor-pointer px-2 py-1 hover:bg-gray-600"></div>');
            itemDiv.text(item.tw_nazwa);
            itemDiv.on('click', () => {
                $input.val(item.tw_nazwa);
                $input.attr('data-produkt-id', item.id);
                $input.prop('disabled', true);
                closeAutocompleteList();
            });
            list.append(itemDiv);
        });

        $input.parent().append(list);
    });

    function closeAutocompleteList() {
        $input.parent().find('.autocomplete-items').remove();
    }

    $(document).on('click', (e) => {
        if (!$(e.target).is($input)) {
            closeAutocompleteList();
        }
    });
}

$(document).ready(function() {
    $('.template-details').on('click', '.dodaj-produkt', function() {
        const $container = $(this).closest('.template-details');
        const $tbody = $container.find('.products-table');

        const newRow = $(`
            <tr class="border-t border-gray-700 relative">
                <td class="px-2 py-1 relative">
                    <input type="text" class="product-name-input bg-gray-700 text-white px-2 py-1 rounded w-full" placeholder="Nowy produkt" autocomplete="off" data-produkt-id="">
                </td>
                <td class="px-2 py-1">
                    <input type="number" min="1" value="1" class="product-amount-input bg-gray-700 text-white px-2 py-1 rounded border border-gray-600 w-20">
                </td>
                <td class="px-2 py-1 text-right">
                    <button type="button" class="remove-product-btn text-red-800 hover:text-red-600 text-sm">
                        🗑️
                    </button>
                </td>
            </tr>
        `);

        $tbody.append(newRow);

        newRow.find('.remove-product-btn').on('click', function() {
            $(this).closest('tr').remove();
        });

        setupAutocomplete(newRow.find('.product-name-input'));
    });

    $('.product-name-input').each(function() {
        setupAutocomplete($(this));
    });
});