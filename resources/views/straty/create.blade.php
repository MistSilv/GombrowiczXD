<x-layout>
    <h1 class="text-2xl font-bold mb-4 text-center text-white">
        Wprowadź stratę dla automatu: {{ $automat ? $automat->nazwa : 'Nie wybrano' }}
    </h1>

    <form method="POST" action="{{ route('straty.store') }}" class="bg-gray-900 p-6 rounded shadow-md max-w-xl mx-auto w-full">
        @csrf

        <input type="hidden" name="automat_id" value="{{ $automat->id ?? '' }}">

        <div class="mb-4"> 
            <label for="data_straty" class="block font-medium mb-1 text-gray-200">Data straty:</label>
            <input 
                type="date"
                id="data_straty"
                name="data_straty"
                value="{{ old('data_straty', date('Y-m-d')) }}"
                required
                class="w-full border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >
            @error('data_straty')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="opis" class="block font-medium mb-1 text-gray-200">Opis straty (opcjonalnie):</label>
            <textarea
                id="opis"
                name="opis"
                rows="3"
                class="w-full border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
            >{{ old('opis') }}</textarea>
        </div>

        <h2 class="text-xl font-semibold mb-2 text-white">Produkty</h2>
        <div id="produkty-list" class="space-y-4 mb-4">
            <div class="produkt-row flex flex-col sm:flex-row gap-2 items-stretch sm:items-center relative">
                <input
                    type="text"
                    name="produkty[0][tw_nazwa]"
                    required
                    placeholder="Wpisz nazwę produktu"
                    autocomplete="off"
                    class="form-input w-full text-white bg-gray-800 border border-gray-700 rounded px-3 py-2 autocomplete-input"
                    value="{{ old('produkty.0.tw_nazwa') ?? '' }}"
                    readonly
                />
                <input
                    type="hidden"
                    name="produkty[0][produkt_id]"
                    class="produkt-id-hidden"
                    value="{{ old('produkty.0.produkt_id') ?? '' }}"
                />
                <input
                    type="number"
                    name="produkty[0][ilosc]"
                    min="1"
                    max="100"
                    value="{{ old('produkty.0.ilosc', 1) }}"
                    required
                    class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                >
                <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">
                    X
                </button>
            </div>
            @if(old('produkty'))
                @foreach(old('produkty') as $i => $produkt)
                    @continue($i === 0)
                    <div class="produkt-row flex flex-col sm:flex-row gap-2 items-stretch sm:items-center relative">
                        <input
                            type="text"
                            name="produkty[{{ $i }}][tw_nazwa]"
                            required
                            placeholder="Wpisz nazwę produktu"
                            autocomplete="off"
                            class="form-input w-full text-white bg-gray-800 border border-gray-700 rounded px-3 py-2 autocomplete-input"
                            value="{{ $produkt['tw_nazwa'] ?? '' }}"
                        />
                        <input
                            type="hidden"
                            name="produkty[{{ $i }}][produkt_id]"
                            class="produkt-id-hidden"
                            value="{{ $produkt['produkt_id'] ?? '' }}"
                        />
                        <input
                            type="number"
                            name="produkty[{{ $i }}][ilosc]"
                            min="1"
                            max="100"
                            value="{{ $produkt['ilosc'] ?? 1 }}"
                            required
                            class="w-full sm:w-20 border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                        >
                        <button type="button" class="remove-produkty bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition">
                            X
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="mb-4 relative">
            <input
                type="text"
                id="szukaj-produkt"
                placeholder="Szukaj produktu..."
                class="w-full border border-gray-700 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                autocomplete="off"
            >
            <ul id="lista-podpowiedzi" class="absolute bg-white text-black max-h-40 overflow-auto border border-gray-600 rounded w-full z-50 hidden"></ul>
        </div>


        <div>
            <button
                type="submit"
                onclick="return confirm('Czy na pewno chcesz potwierdzić aktualnie wykonywaną operację?')"
                class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 transition w-full sm:w-auto">
                Zapisz stratę
            </button>
        </div>
    </form>

    <script>
        window._produkty = @json($produkty);
    </script>

    <script src="{{ asset('js/straty-create.js') }}"></script>
</x-layout>
