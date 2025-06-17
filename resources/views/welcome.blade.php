<x-layout>
    <h1 class="text-2xl font-bold mb-6 text-center text-white">Wybierz automat</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-2">
        @foreach ($automaty as $automat)
            <a href="{{ route('zamowienia.create', ['automat_id' => $automat->id]) }}"
               class="border p-4 rounded-2xl shadow hover:bg-slate-900 transition flex flex-col items-center">
                <div class="text-5xl mb-2">🧅</div>
                <h2 class="text-xl font-bold text-center text-white">{{ $automat->nazwa }}</h2>
                <p class="text-sm text-center text-white">{{ $automat->lokalizacja }}</p>
            </a>
        @endforeach

            @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('register') }}"
                    class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded mt-10">
                    create a new account
                </a>
            @endif
            @endauth
    </div>



</x-layout>