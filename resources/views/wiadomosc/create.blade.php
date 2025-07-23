<x-layout>
    <div class="max-w-2xl mx-auto mt-8 space-y-6">
        <h1 class="text-2xl font-semibold">Wyślij wiadomość na Discord</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('wiadomosc.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="tresc" class="block font-medium text-gray-700">Wiadomość:</label>
                <textarea name="tresc" id="tresc" rows="5" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('tresc') }}</textarea>

                @error('tresc')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Wyślij</button>
        </form>
    </div>
</x-layout>
