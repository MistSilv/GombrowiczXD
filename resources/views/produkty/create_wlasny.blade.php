<x-layout>
    <div class="flex flex-col items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Produkt własny -->
        <div class="w-full max-w-md">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl p-6 space-y-6 border border-gray-700">
                <h1 class="text-3xl font-bold text-center text-white">Dodaj produkt własny</h1>

                @if (session('success-wlasny'))
                <div class="bg-green-600 text-white p-4 rounded-lg shadow-lg text-center">
                    {{ session('success-wlasny') }}
                </div>
                @endif

                <form method="POST" action="{{ route('produkty.store.wlasny') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="tw_nazwa" class="block text-white font-semibold">Nazwa produktu:</label>
                        <input type="text" name="tw_nazwa" id="tw_nazwa" required 
                               class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div class="space-y-2">
                        <label for="tw_idabaco" class="block text-white font-semibold">ID Abaco (opcjonalnie):</label>
                        <input type="text" name="tw_idabaco" id="tw_idabaco" 
                               class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <input type="hidden" name="is_wlasny" value="1">

                    <button type="submit" class="w-full bg-rose-950 hover:bg-red-900 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                        Zapisz produkt własny
                    </button>
                </form>
            </div>
        </div>

        <!-- Produkt niewłasny -->
        <div class="w-full max-w-md">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl p-6 space-y-6 border border-gray-700"">
                <h1 class="text-3xl font-bold text-center text-white">Dodaj produkt niewłasny</h1>

                @if (session('success-niewlasny'))
                <div class="bg-green-600 text-white p-4 rounded-lg shadow-lg text-center">
                    {{ session('success-niewlasny') }}
                </div>
                @endif

                <form method="POST" action="{{ route('produkty.store.niewlasny') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="tw_nazwa_niewlasny" class="block text-white font-semibold">Nazwa produktu:</label>
                        <input type="text" name="tw_nazwa" id="tw_nazwa_niewlasny" required
                                class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div class="space-y-2">
                        <label for="tw_idabaco_niewlasny" class="block text-white font-semibold">ID Abaco (opcjonalnie):</label>
                        <input type="text" name="tw_idabaco" id="tw_idabaco_niewlasny"
                                class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div class="space-y-2">
                        <label for="ean_codes" class="block text-white font-semibold">EAN:</label>
                        <input type="text" name="ean_codes" id="ean_codes" maxlength="13" required
                               placeholder="Wprowadź EAN (maksymalnie 13 znaków)"
                                class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <input type="hidden" name="is_wlasny" value="0">

                    <button type="submit" class="w-full bg-rose-950 hover:bg-red-900 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                        Zapisz produkt niewłasny
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout>