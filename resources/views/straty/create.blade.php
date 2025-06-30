<!-- strona do wprowadzania strat dla automatu -->
<x-layout>
    <h1 class="text-2xl font-bold mb-4 text-center text-white">Wprowadź stratę dla automatu: {{ $automat ? $automat->nazwa : 'Nie wybrano' }}</h1>

    <form method="POST" action="{{ route('straty.store') }}" class="bg-gray-900 p-6 rounded shadow-md max-w-xl mx-auto w-full">
        @csrf

        <input type="hidden" name="automat_id" value="{{ $automat->id ?? '' }}"> <!-- ukryte pole z ID automatu -->

        <div class="mb-4"> 
            <label for="data_straty" class="block font-medium mb-1 text-gray-200">Data straty:</label> <!-- etykieta dla pola daty straty -->
            <input 
                type="date"
                id="data_straty"
                name="data_straty"
                value="{{ old('data_straty', date('Y-m-d')) }}"
                required
                class="w-full border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >
            @error('data_straty')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p> <!-- komunikat o błędzie daty straty -->
            @enderror
        </div>

        <div class="mb-6">
            <label for="opis" class="block font-medium mb-1 text-gray-200">Opis straty (opcjonalnie):</label> <!-- etykieta dla pola opisu straty -->
            <textarea
                id="opis"
                name="opis"
                rows="3"
                class="w-full border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >{{ old('opis') }}</textarea>
        </div>

        <h2 class="text-xl font-semibold mb-2 text-white">Produkty</h2> <!-- nagłówek sekcji produktów -->
        <div id="produkty-list" class="space-y-4 mb-4">
            <div class="produkt-row flex flex-col sm:flex-row gap-2 items-stretch sm:items-center"> <!-- kontener dla pojedynczego produktu -->
                <select
                    name="produkty[0][produkt_id]"
                    required
                    class="border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 flex-1 focus:outline-none focus:border-blue-500">
                    <option value="">-- wybierz produkt --</option>
                    @foreach($produkty as $produkt)
                        <option value="{{ $produkt->id }}" {{ old('produkty.0.produkt_id') == $produkt->id ? 'selected' : '' }}>
                            {{ $produkt->tw_nazwa }} 
                        </option>
                    @endforeach
                </select>

                <!-- pole do wprowadzania ilości produktu -->
                <input
                    type="number"
                    name="produkty[0][ilosc]"
                    min="1"
                    value="{{ old('produkty.0.ilosc', 1) }}"
                    required
                    class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500">

                <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">
                    X
                </button>
            </div>
        </div>

        <button
            type="button"
            id="add-produkt"
            class="mb-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full sm:w-auto">
            Dodaj produkt
        </button>

        <div>
            <button
                type="submit"
                class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 transition w-full sm:w-auto">
                Zapisz stratę
            </button>
        </div>
    </form>

   <script>
    // przycisk dodawania nowego produktu
    document.getElementById('add-produkt').addEventListener('click', function () {

        const produktyList = document.getElementById('produkty-list'); // Kontener na listę produktów
        const count = produktyList.children.length; // Licznik aktualnych produktów na liście

        // Tworzenie nowego wiersza z produktem
        const newRow = document.createElement('div');
        newRow.classList.add('produkt-row', 'flex', 'flex-col', 'sm:flex-row', 'gap-2', 'items-stretch', 'sm:items-center');

        // Wypełnienie nowego wiersza HTML-em z polami wyboru i ilości
        newRow.innerHTML = `
            <select name="produkty[\${count}][produkt_id]" required class="border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 flex-1 focus:outline-none focus:border-blue-500">
                <option value="">-- wybierz produkt --</option>
                @foreach($produkty as $produkt)
                    <option value="{{ $produkt->id }}">{{ $produkt->tw_nazwa }}</option>
                @endforeach
            </select>
            <input type="number" name="produkty[\${count}][ilosc]" min="1" value="1" required class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500">
            <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">X</button>
        `;

        // Dodanie nowego wiersza do listy produktów
        produktyList.appendChild(newRow);
    });

    // usuwanie produktu z listy
    document.getElementById('produkty-list').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-produkty')) {
            e.target.parentElement.remove(); 
        }
    });
    </script>
</x-layout>