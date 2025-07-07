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
                if (!uzyteProdukty.has(p.id) || p.id == parseInt(currentValue)) {
                    const selected = p.id == parseInt(currentValue) ? 'selected' : '';
                    select.innerHTML += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
                }
            });
        });

        const btnDodaj = document.getElementById('dodaj-produkt');
        if (uzyteProdukty.size >= produkty.length) {
            btnDodaj.disabled = true;
            btnDodaj.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnDodaj.disabled = false;
            btnDodaj.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        return uzyteProdukty;
    }

    function focusIloscDlaSelecta(selectElem) {
        const produktItem = selectElem.closest('.produkt-item');
        if (!produktItem) return;
        const inputIlosc = produktItem.querySelector('input[type="number"]');
        if (inputIlosc) {
            inputIlosc.focus();
            setTimeout(() => inputIlosc.select(), 100);
        }
    }

    document.getElementById('dodaj-produkt').addEventListener('click', () => {
        dodajProduktDoListy();
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.produkt-item').remove();
            aktualizujDostepneProdukty();
        }
    });

    // Skaner
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    function onScanSuccess(decodedText) {
        scanner.stop().then(() => {
            isScanning = false;
            $('#reader').hide();
            $('#scan-result').text(`Zeskanowano: ${decodedText}`);

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                    dodajProduktDoListy(data.produkt.id, data.produkt.tw_nazwa, parseInt(ilosc));
                } else {
                    alert("Nieprawidłowa ilość.");
                }
            })
            .catch(err => alert(err.message || 'Błąd przy sprawdzaniu kodu.'));
        });
    }

    document.getElementById('start-scan').addEventListener('click', () => {
        if (isScanning) return;
        Html5Qrcode.getCameras().then(devices => {
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
        }).catch(err => alert("Błąd pobierania kamer: " + err));
    });

    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        const container = document.getElementById('produkty-lista');

        if (produktId) {
            const istniejącySelect = [...container.querySelectorAll('select')].find(s => s.value == produktId);
            if (istniejącySelect) {
                const inputIlosc = istniejącySelect.closest('.produkt-item').querySelector('input[type="number"]');
                inputIlosc.value = parseInt(inputIlosc.value) + ilosc;
                inputIlosc.focus();
                setTimeout(() => inputIlosc.select(), 100);
                return;
            }
        }

        const uzyteProdukty = aktualizujDostepneProdukty();
        let options = '<option value="">-- wybierz produkt --</option>';

        produkty.forEach(p => {
            if (!uzyteProdukty.has(p.id) || (produktId && p.id == produktId)) {
                const selected = (produktId && p.id == produktId) ? 'selected' : '';
                options += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
            }
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" value="${ilosc}" class="form-input w-24 text-black" placeholder="Ilość" required>
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;

        container.appendChild(newItem);

        const selectNowy = newItem.querySelector('select');
        selectNowy.addEventListener('change', () => {
            focusIloscDlaSelecta(selectNowy);
            aktualizujDostepneProdukty();
        });

        const inputIloscNowy = newItem.querySelector('input[type="number"]');
        if (inputIloscNowy) {
            inputIloscNowy.focus();
            setTimeout(() => inputIloscNowy.select(), 100);
        }

        index++;
        aktualizujDostepneProdukty();
    }

    document.querySelectorAll('#produkty-lista select').forEach(select => {
        select.addEventListener('change', () => {
            focusIloscDlaSelecta(select);
            aktualizujDostepneProdukty();
        });
    });

    const szukajInput = document.getElementById('szukaj-produkt');
    const listaPodpowiedzi = document.getElementById('lista-podpowiedzi');

    szukajInput.addEventListener('input', () => {
        const val = szukajInput.value.toLowerCase();
        if (val.length < 2) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
            return;
        }

        const uzyteProdukty = aktualizujDostepneProdukty();
        const dopasowane = produkty.filter(p =>
            p.tw_nazwa.toLowerCase().includes(val) && !uzyteProdukty.has(p.id)
        );

        if (!dopasowane.length) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
            return;
        }

        listaPodpowiedzi.innerHTML = dopasowane.map(p =>
            `<li data-id="${p.id}" class="cursor-pointer px-2 py-1 hover:bg-gray-200">${p.tw_nazwa}</li>`
        ).join('');
        listaPodpowiedzi.style.display = 'block';
    });

    listaPodpowiedzi.addEventListener('click', e => {
        if (e.target.tagName.toLowerCase() === 'li') {
            const id = e.target.getAttribute('data-id');
            const nazwa = e.target.textContent;
            dodajProduktDoListy(id, nazwa);
            szukajInput.value = '';
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
        }
    });

    document.addEventListener('click', e => {
        if (!listaPodpowiedzi.contains(e.target) && e.target !== szukajInput) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
        }
    });

    aktualizujDostepneProdukty();
});