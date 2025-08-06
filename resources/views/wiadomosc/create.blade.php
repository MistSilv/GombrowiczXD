<x-layout>
    <div class="max-w-2xl mx-auto mt-12 px-6 py-8 bg-slate-800 rounded-2xl shadow-lg space-y-6">
        <h1 class="text-3xl font-bold text-white border-b border-slate-600 pb-2">Wyślij wiadomość na Discord</h1>

        @if (session('success'))
            <div class="bg-green-500/10 border border-green-500 text-green-200 px-4 py-3 rounded-lg text-sm shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('wiadomosc.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="tresc" class="block text-sm font-semibold text-gray-200 mb-1">Treść wiadomości</label>
                <textarea
                    name="tresc"
                    id="tresc"
                    rows="5"
                    required
                    placeholder="Wpisz treść wiadomości do wysłania na Discord..."
                    class="w-full px-4 py-3 rounded-xl bg-slate-700 text-white border border-slate-600  placeholder-gray-400 resize-none shadow-inner">{{ old('tresc') }}</textarea>

                @error('tresc')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-green-800 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                     Wyślij
                </button>
            </div>
        </form>
    </div>
</x-layout>
