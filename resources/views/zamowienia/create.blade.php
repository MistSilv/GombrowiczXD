<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-6 bg-gray-900 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-white">
            Nowe zamówienie produkcyjne dla: <span class="text-rose-400">{{ $automat ? $automat->nazwa : '---' }}</span>
        </h1>

        <form action="{{ route('zamowienia.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($automat)
                <input type="hidden" name="automat_id" value="{{ $automat->id }}">
            @endif


            {{-- Wybór szablonu zamówienia własnego --}}
            @if(isset($wlasneSzablony) && $wlasneSzablony->count())
                <div class="mb-6">
                    <label for="wlasny_template_id" class="block mb-2 text-white font-semibold">Wybierz szablon produktów własnych:</label>
                    <select name="wlasny_template_id" id="wlasny_template_id" class="w-full max-w-xs p-2 rounded bg-gray-700 text-white">
                        <option value="">-- wybierz szablon --</option>
                        @foreach($wlasneSzablony as $template)
                            <option value="{{ $template->id }}">{{ $template->nazwa }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="zaladujWlasnySzablon" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Załaduj szablon</button>
                </div>
            @endif
            <!-- Lista produktów -->
            <div class="space-y-4" id="produkty-lista">
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end produkt-item">
                    <div class="w-full sm:w-auto flex-grow">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nazwa produktu</label>
                        <input
                            type="text"
                            name="produkty[0][tw_nazwa]"
                            class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400 autocomplete-input"
                            placeholder="Wpisz nazwę produktu"
                            required
                            autocomplete="off">
                        <input type="hidden" name="produkty[0][produkt_id]" class="produkt-id-hidden">
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Ilość</label>
                            <input
                                type="number"
                                name="produkty[0][ilosc]"
                                min="1" max="3000"
                                class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white "
                                placeholder="Ilość"
                                required
                                value="1">
                        </div>

                        <button type="button"
                                class="h-[42px] px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-item flex items-center justify-center">
                            Usuń
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dodaj produkt -->
            <div>
                <button type="button" id="dodaj-produkt" class="px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center gap-1">
                    + Dodaj produkt
                </button>
            </div>

            <!-- Złóż zamówienie -->
            <div>
                <div
                    <label class="block text-sm font-medium text-gray-300 mb-1">Data realizacji zamówienia:</label>
                    <select name="data_realizacji" required class="w-full px-3 py-2 mb-2 rounded-lg border border-gray-600 bg-gray-700 text-white ">
                        <option value="dzisiaj" selected>Na dzisiaj</option>
                        <option value="jutro">Na jutro</option>
                    </select>
                </div>
                <button 
                    type="submit" 
                    onclick="return confirm('Czy na pewno chcesz potwierdzić zamówienie?')"
                    class="px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">
                        Złóż zamówienie
                </button>
            </div>

            <!-- Dodatkowe linki -->
            <div class="pt-4 border-t border-gray-700 flex flex-col md:flex-row md:items-center gap-3">
                @if($automat)
                    <a href="{{ route('straty.create', ['automat_id' => $automat->id]) }}" 
                    class="inline-flex max-w-max px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">
                        Wprowadź straty
                    </a>

                    @auth
                        @if(!auth()->user()->isProdukcja())
                            <a href="{{ route('zamowienia.index', ['automat_id' => $automat->id]) }}" 
                            class="inline-flex max-w-max px-4 py-2 bg-emerald-900 hover:bg-emerald-600 text-white font-semibold rounded-lg transition-colors">
                                Lista zamówień tego automatu
                            </a>
                        @endif
                    @endauth

                    <a href="{{ route('wsady.create', ['automat_id' => $automat->id]) }}" 
                    class="inline-flex max-w-max px-4 py-2 bg-yellow-800 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors">
                        Powrót
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        window._produkty = @json($produkty->filter(fn($p) => $p->is_wlasny));
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/zamowienia-create.js') }}"></script>
</x-layout>
