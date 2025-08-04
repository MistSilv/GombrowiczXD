<x-layout>
    <div class="min-h-screen flex items-start justify-center bg-black px-4 py-4">
        <div class="w-full max-w-md space-y-4">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('register') }}"
                       class="block w-full px-5 py-3 bg-sky-900 hover:bg-sky-600 text-white font-bold rounded-xl text-center transition shadow-md">
                        ➕ Dodaj użytkownika
                    </a>
                @endif
            @endauth

            <a href="{{ route('produkty.zamowienie.formularz') }}"
               class="block w-full px-5 py-3 bg-emerald-900 hover:bg-emerald-600 text-white font-bold rounded-xl text-center transition shadow-md">
                🧾 Nowe zamówienie
            </a>

            <a href="{{ route('produkty.create.wlasny') }}"
               class="block w-full px-5 py-3 bg-lime-900 hover:bg-lime-600 text-white font-bold rounded-xl text-center transition shadow-md">
                ➕ Dodaj produkt własny
            </a>

            <a href="{{ route('capybara.show') }}"
               class="block w-full px-5 py-3 bg-sky-900 hover:bg-sky-600 text-white font-bold rounded-xl text-center transition shadow-md">
                🐹 Zobacz Kapibarę
            </a>

            <a href="{{ route('wiadomosc.create') }}"
               class="block w-full px-5 py-3 bg-emerald-900 hover:bg-emerald-600 text-white font-bold rounded-xl text-center transition shadow-md">
                📬 Skargi, Uwagi, Pytania
            </a>

            <a href="{{ route('automats.create') }}"
               class="block w-full px-5 py-3 bg-lime-900 hover:bg-lime-600 text-white font-bold rounded-xl text-center transition shadow-md">
                🤖 Dodaj Automat
            </a>

        </div>
    </div>
</x-layout>
