
<x-layout>
    <h1 class="text-2xl font-bold mb-6">Wybierz automat</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($automaty as $automat)
            <a href="{{ route('zamowienia.create', ['automat_id' => $automat->id]) }}"
               class="border p-4 rounded-xl shadow hover:bg-blue-100 transition">
                <div class="mb-2 flex justify-center">
                    <img src="{{ asset('img/VMIco.png') }}"
                        alt="Vending Machine"
                        class="w-32 h-32 object-contain" />
                </div>
                <h2 class="text-xl font-semibold">{{ $automat->nazwa }}</h2>
                <p class="text-gray-600 text-sm">{{ $automat->lokalizacja }}</p>
            </a>
        @endforeach
    </div>
</x-layout>
