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
    </div>


    <button id="installPwaBtn" style="display:none;" class="bg-blue-800 text-white px-4 py-2 rounded">
    Zainstaluj aplikację
    </button>

</x-layout>