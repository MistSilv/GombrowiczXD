<x-layout>
    <h1 class="text-2xl text-white mb-4">Dodaj produkt własny</h1>

    @if (session('success'))
    <div class="bg-green-600 text-white p-6 mb-4 rounded max-w-lg w-full shadow-lg">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('produkty.store.wlasny') }}" class="space-y-4 max-w-lg bg-gray-800 p-6 rounded-xl shadow-lg">
        @csrf

        <div>
            <label for="tw_nazwa" class="block text-white font-semibold mb-1">Nazwa produktu:</label>
            <input type="text" name="tw_nazwa" id="tw_nazwa" required class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label for="tw_idabaco" class="block text-white font-semibold mb-1">ID Abaco (opcjonalnie):</label>
            <input type="text" name="tw_idabaco" id="tw_idabaco" class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <input type="hidden" name="is_wlasny" value="1">

        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-bold px-4 py-2 rounded w-full">Zapisz</button>
    </form>

    <form method="POST" action="{{ route('produkty.store.niewlasny') }}" class="space-y-4 max-w-lg bg-gray-800 p-6 rounded-xl shadow-lg mt-10">
        @csrf

        <div>
            <label for="tw_nazwa_niewlasny" class="block text-white font-semibold mb-1">Nazwa produktu:</label>
            <input type="text" name="tw_nazwa" id="tw_nazwa_niewlasny" required
                   class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label for="tw_idabaco_niewlasny" class="block text-white font-semibold mb-1">ID Abaco (opcjonalnie):</label>
            <input type="text" name="tw_idabaco" id="tw_idabaco_niewlasny" required
                   class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label for="ean_codes" class="block text-white font-semibold mb-1">EAN:</label>
            <input type="text" name="ean_codes" id="ean_codes" maxlength="13" required
                     placeholder="Wprowadź EAN (maksymalnie 13 znaków)"
                   class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <input type="hidden" name="is_wlasny" value="0">

        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-bold px-4 py-2 rounded w-full">
            Zapisz produkt niewłasny
        </button>
    </form>
</x-layout>