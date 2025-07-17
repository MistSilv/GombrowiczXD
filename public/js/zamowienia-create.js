/*
document.addEventListener('DOMContentLoaded', function () {
    let index = 1;
    const produkty = window._produkty || [];
    const produktyMap = new Map();
    produkty.forEach(p => produktyMap.set(p.id, p.tw_nazwa));

    function aktualizujDostepneProdukty() {
        const uzyteProdukty = new Set();
        document.querySelectorAll('#produkty-lista select').forEach(select => {
            if (select.value) uzyteProdukty.add(parseInt(select.value));
        });

        document.querySelectorAll('#produkty-lista select').forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">-- wybierz produkt --</option>';
            produkty.forEach(p => {
                if (p.is_wlasny && (!uzyteProdukty.has(p.id) || p.id == parseInt(currentValue))) {
                    const selected = p.id == parseInt(currentValue) ? 'selected' : '';
                    select.innerHTML += `<option value="${p.id}" data-is-wlasny="1" ${selected}>${p.tw_nazwa}</option>`;
                }
            });
        });

        const btnDodaj = document.getElementById('dodaj-produkt');
        if (btnDodaj) {
            if (uzyteProdukty.size >= produkty.length) {
                btnDodaj.disabled = true;
                btnDodaj.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btnDodaj.disabled = false;
                btnDodaj.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        return uzyteProdukty;
    }

    function focusIlePole(produktItem) {
        const iloscInput = produktItem.querySelector('input[type="number"]');
        if (iloscInput) {
            iloscInput.focus();
            iloscInput.select();
        }
    }

    document.getElementById('dodaj-produkt').addEventListener('click', function() {
        const container = document.getElementById('produkty-lista');
        const uzyteProdukty = aktualizujDostepneProdukty();

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(function(produkt) {
            if (produkt.is_wlasny && !uzyteProdukty.has(produkt.id)) {
                options += `<option value="${produkt.id}" data-is-wlasny="1">${produkt.tw_nazwa}</option>`;
            }
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full produkty-select" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required>
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;
        container.appendChild(newItem);
        index++;

        const select = newItem.querySelector('select');
        select.focus();

        select.addEventListener('change', () => {
            focusIlePole(newItem);
            aktualizujDostepneProdukty();
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.produkt-item').remove();
            aktualizujDostepneProdukty();
        }
    });

    document.querySelectorAll('.produkty-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const produktItem = e.target.closest('.produkt-item');
            focusIlePole(produktItem);
            aktualizujDostepneProdukty();
        });
    });

    window.dodajProduktDoListy = function(produktId, nazwaProduktu, ilosc = 1) {
        const container = document.getElementById('produkty-lista');
        const uzyteProdukty = aktualizujDostepneProdukty();

        const istniejącySelect = [...container.querySelectorAll('select')].find(s => s.value == produktId);
        if (istniejącySelect) {
            const inputIlosc = istniejącySelect.closest('.produkt-item').querySelector('input[type="number"]');
            inputIlosc.value = parseInt(inputIlosc.value) + ilosc;
            focusIlePole(istniejącySelect.closest('.produkt-item'));
            return;
        }

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(function(produkt) {
            if (produkt.is_wlasny && (!uzyteProdukty.has(produkt.id) || produkt.id == produktId)) {
                const selected = produkt.id == produktId ? 'selected' : '';
                options += `<option value="${produkt.id}" data-is-wlasny="1" ${selected}>${produkt.tw_nazwa}</option>`;
            }
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full produkty-select" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" max="3000" class="form-input w-24 text-black" placeholder="Ilość" required value="${ilosc}">
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;
        container.appendChild(newItem);

        const selectNowy = newItem.querySelector('select');
        selectNowy.value = produktId;

        focusIlePole(newItem);

        selectNowy.addEventListener('change', () => {
            focusIlePole(newItem);
            aktualizujDostepneProdukty();
        });

        index++;
        aktualizujDostepneProdukty();
    };

    // Wyszukiwanie produktu po nazwie z podpowiedziami
    $(document).ready(function() {
        $('#szukaj-produkt').on('input', function() {
            const val = $(this).val().toLowerCase();
            const lista = $('#lista-podpowiedzi');
            lista.empty().hide();

            if (val.length < 2) {
                return;
            }

            const uzyteProdukty = aktualizujDostepneProdukty();
            const dopasowane = produkty.filter(p => 
                p.is_wlasny && 
                p.tw_nazwa.toLowerCase().includes(val) && 
                !uzyteProdukty.has(p.id)
            );

            if (dopasowane.length === 0) {
                return;
            }

            dopasowane.forEach(p => {
                const li = $('<li></li>')
                    .text(p.tw_nazwa)
                    .addClass('p-2 cursor-pointer hover:bg-gray-200')
                    .attr('data-id', p.id)
                    .on('click', function() {
                        window.dodajProduktDoListy(p.id, p.tw_nazwa);
                        $('#szukaj-produkt').val('');
                        lista.hide();
                    });
                lista.append(li);
            });

            lista.show();
        });

        $(document).on('click', function(event) {
            if (!$(event.target).closest('#szukaj-produkt, #lista-podpowiedzi').length) {
                $('#lista-podpowiedzi').hide();
            }
        });
    });

    aktualizujDostepneProdukty();
});
*/

document.addEventListener('DOMContentLoaded', () => {
    let index = 1;
    const produkty = window._produkty || [];

    function focusIloscInput(item) {
        const iloscInput = item.querySelector('input[type="number"]');
        if (iloscInput) {
            iloscInput.focus();
            iloscInput.select();
        }
    }

    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        const container = document.getElementById('produkty-lista');

        const istniejącySelect = [...container.querySelectorAll('select')].find(s => s.value == produktId);
        if (istniejącySelect) {
            const inputIlosc = istniejącySelect.closest('.produkt-item').querySelector('input[type="number"]');
            inputIlosc.value = parseInt(inputIlosc.value) + ilosc;
            focusIloscInput(istniejącySelect.closest('.produkt-item'));
            return;
        }

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(p => {
            if (p.is_wlasny) {
                const selected = produktId == p.id ? 'selected' : '';
                options += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
            }
        });

        const newItem = document.createElement('div');
        newItem.className = 'flex items-center gap-2 mb-2 produkt-item';
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full produkty-select" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" max="3000" class="form-input w-24 text-black" value="${ilosc}" required>
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;

        container.appendChild(newItem);
        index++;

        const select = newItem.querySelector('select');
        if (produktId) select.value = produktId;

        focusIloscInput(newItem);

        select.addEventListener('change', () => focusIloscInput(newItem));
    }

    document.getElementById('dodaj-produkt').addEventListener('click', () => dodajProduktDoListy());

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.produkt-item').remove();
        }
    });

    // Wyszukiwarka
    const szukajInput = document.getElementById('szukaj-produkt');
    const listaPodpowiedzi = document.getElementById('lista-podpowiedzi');

    szukajInput.addEventListener('input', () => {
        const val = szukajInput.value.toLowerCase();
        listaPodpowiedzi.innerHTML = '';
        if (val.length < 2) {
            listaPodpowiedzi.style.display = 'none';
            return;
        }

        const dopasowane = produkty.filter(p =>
            p.is_wlasny && p.tw_nazwa.toLowerCase().includes(val)
        );

        if (!dopasowane.length) {
            listaPodpowiedzi.style.display = 'none';
            return;
        }

        dopasowane.forEach(p => {
            const li = document.createElement('li');
            li.textContent = p.tw_nazwa;
            li.className = 'cursor-pointer px-2 py-1 hover:bg-gray-200';
            li.setAttribute('data-id', p.id);
            li.addEventListener('click', () => {
                dodajProduktDoListy(p.id, p.tw_nazwa);
                szukajInput.value = '';
                listaPodpowiedzi.style.display = 'none';
                listaPodpowiedzi.innerHTML = '';
            });
            listaPodpowiedzi.appendChild(li);
        });

        listaPodpowiedzi.style.display = 'block';
    });

    document.addEventListener('click', e => {
        if (!listaPodpowiedzi.contains(e.target) && e.target !== szukajInput) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
        }
    });
});
