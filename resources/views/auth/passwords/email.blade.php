<x-layoutL>
    <div class="max-w-md mx-auto mt-10 bg-gray-900 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 text-white text-center">Resetowanie hasła</h2>
        @if (session('status'))
            <div class="mb-4 text-green-400 text-center font-semibold">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-gray-200 mb-1">E-mail</label>
                <input id="email" type="email" name="email" required autofocus
                    class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
                @error('email')
                    <span class="text-red-400 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition">
                Wyślij link resetujący
            </button>
        </form>
    </div>
</x-layoutL>