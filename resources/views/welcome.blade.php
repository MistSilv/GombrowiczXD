<!-- strona główna aplikacji -->
<x-layout>
    <h1 class="text-2xl font-bold mb-6 text-center text-white">Wybierz automat</h1>

    

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-2 mt-3">
        @foreach ($automaty as $automat) <!-- iteracja po dostępnych automatach -->
            <a href="{{ route('wsady.create', ['automat_id' => $automat->id]) }}"
               class="border p-4 rounded-2xl shadow hover:bg-slate-900 transition flex flex-col items-center">
                    <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="" class="rounded-xl"> 
                <h2 class="text-xl font-bold text-center text-white">{{ $automat->nazwa }}</h2>
                <p class="text-sm text-center text-white">{{ $automat->lokalizacja }}</p>
            </a>
        @endforeach     
    </div>

    <!-- Przycisk do instalacji PWA -->
    <button id="installPWA"
    class="fixed bottom-4 right-4 bg-slate-800 hover:bg-red-900 text-white px-4 py-2 mt-3 rounded shadow-lg z-50 hidden">
    Zainstaluj aplikację
    </button>

<!-- Skrypt do obsługi instalacji PWA -->
@vite('resources/js/app.js')

<div class="mt-9 flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 flex-wrap">
    @auth
        @if(auth()->user()->isAdmin())
            <a href="{{ route('register') }}"
               class="px-5 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-lg inline-flex items-center justify-center transition">
                Dodaj użytkownika
            </a>
        @endif
    @endauth

    <a href="{{ route('produkty.zamowienie.formularz') }}"
       class="px-5 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-lg inline-flex items-center justify-center transition">
        Nowe zamówienie
    </a>

    <a href="{{ route('produkty.create.wlasny') }}"
       class="px-5 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-lg inline-flex items-center justify-center transition">
        ➕ Dodaj produkt własny
    </a>

    <a href="{{ route('capybara.show') }}"
       class="px-5 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-lg inline-flex items-center justify-center transition">
        🐹 Zobacz Kapibarę
    </a>

    <a href="{{ route('wiadomosc.create') }}"
       class="px-5 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-lg inline-flex items-center justify-center transition">
        ➕ Skargi, Uwagi, Pytania
    </a>
</div>

</x-layout>