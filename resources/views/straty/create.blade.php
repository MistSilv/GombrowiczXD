<x-layout>
    <h1 class="text-2xl font-semibold mb-4">Wprowadź stratę dla automatu: {{ $automat ? $automat->nazwa : 'Nie wybrano' }}</h1>

    <form method="POST" action="{{ route('straty.store') }}" class="bg-white p-6 rounded shadow-md max-w-xl">
        @csrf

        <input type="hidden" name="automat_id" value="{{ $automat->id ?? '' }}">

        <div class="mb-4">
            <label for="data_straty" class="block font-medium mb-1">Data straty:</label>
            <input
                type="date"
                id="data_straty"
                name="data_straty"
                value="{{ old('data_straty', date('Y-m-d')) }}"
                required
                class="w-full border border-gray-300 rounded px-3 py-2"
            >
            @error('data_straty')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="opis" class="block font-medium mb-1">Opis straty (opcjonalnie):</label>
            <textarea
                id="opis"
                name="opis"
                rows="3"
                class="w-full border border-gray-300 rounded px-3 py-2"
            >{{ old('opis') }}</textarea>
        </div>

        <h2 class="text-xl font-semibold mb-2">Produkty</h2>
        <div id="produkty-list" class="space-y-4 mb-4">
            <div class="produkt-row flex gap-2 items-center">
                <select
                    name="produkty[0][produkt_id]"
                    required
                    class="border border-gray-300 rounded px-3 py-2 flex-1"
                >
                    <option value="">-- wybierz produkt --</option>
                    @foreach($produkty as $produkt)
                        <option value="{{ $produkt->id }}" {{ old('produkty.0.produkt_id') == $produkt->id ? 'selected' : '' }}>
                            {{ $produkt->tw_nazwa }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="number"
                    name="produkty[0][ilosc]"
                    min="1"
                    value="{{ old('produkty.0.ilosc', 1) }}"
                    required
                    class="w-20 border border-gray-300 rounded px-3 py-2"
                >

                <button type="button" class="remove-produkty bg-red-500 text-white rounded px-3 py-1 hover:bg-red-600">
                    Usuń
                </button>
            </div>
        </div>

        <button
            type="button"
            id="add-produkt"
            class="mb-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            Dodaj produkt
        </button>

        <div>
            <button
                type="submit"
                class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700"
            >
                Zapisz stratę
            </button>
        </div>
    </form>

    <script>
        document.getElementById('add-produkt').addEventListener('click', function() {
            const produktyList = document.getElementById('produkty-list');
            const count = produktyList.children.length;
            const newRow = document.createElement('div');
            newRow.classList.add('produkt-row', 'flex', 'gap-2', 'items-center');
            newRow.innerHTML = `
                <select name="produkty[\${count}][produkt_id]" required class="border border-gray-300 rounded px-3 py-2 flex-1">
                    <option value="">-- wybierz produkt --</option>
                    @foreach($produkty as $produkt)
                        <option value="{{ $produkt->id }}">{{ $produkt->tw_nazwa }}</option>
                    @endforeach
                </select>
                <input type="number" name="produkty[\${count}][ilosc]" min="1" value="1" required class="w-20 border border-gray-300 rounded px-3 py-2">
                <button type="button" class="remove-produkty bg-red-500 text-white rounded px-3 py-1 hover:bg-red-600">Usuń</button>
            `;
            produktyList.appendChild(newRow);
        });

        document.getElementById('produkty-list').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-produkty')) {
                e.target.parentElement.remove();
            }
        });
    </script>
</x-layout>
