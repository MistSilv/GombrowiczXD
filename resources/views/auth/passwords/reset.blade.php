<!-- strona do zmiany hasła - formularz -->
<x-layoutL>
    <div class="max-w-md mx-auto mt-10 bg-gray-900 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 text-white text-center">Ustaw nowe hasło</h2>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}"> <!-- ukryte pole z tokenem resetowania hasła -->
            <input type="hidden" name="email" value="{{ $email }}"> <!-- ukryte pole z adresem email użytkownika -->
            <div class="mb-4">
                <label for="password" class="block text-gray-200 mb-1">Nowe hasło</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
                @error('password')
                    <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie hasła -->
                @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-200 mb-1">Powtórz hasło</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition">
                Zmień hasło
            </button>
        </form>
    </div>
</x-layoutL>