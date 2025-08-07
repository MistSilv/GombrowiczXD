<x-layout>
    <h1 class="text-2xl font-bold mb-6 text-center text-white">Wybierz automat do utworzenia szablonu wsadu</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-2 mt-3">
        @foreach ($automaty as $automat)
            <a href="{{ route('wsad-template.show', ['automat' => $automat->id]) }}"
               class="border p-4 rounded-2xl shadow hover:bg-slate-900 transition flex flex-col items-center">
                
                <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="" class="rounded-xl">

                <h2 class="text-xl font-bold text-center text-white">{{ $automat->nazwa }}</h2>
                <p class="text-sm text-center text-white">{{ $automat->lokalizacja }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 flex justify-center">
        {{ $automaty->links() }}
    </div>
</x-layout>
