<!-- strona rejestracji użytkownika -->
@if(session('success'))
    <div class="mb-4 text-green-700 text-center font-sbold">
        {{ session('success') }} <!-- komunikat o sukcesie rejestracji -->
    </div>
@endif

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Rejestracja</title>
</head>
<body class="mx-auto py-6 px-4 bg-black">
    <div class="max-w-md mx-auto mt-4 bg-gray-900 p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6 text-white text-center">Rejestracja użytkownika</h2>
    <form method="POST" action="{{ route('register') }}"> <!-- formularz rejestracji -->
        <!-- zabezpieczenie CSRF -->
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-200 mb-1">Imię i nazwisko</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
            @error('name')
                <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie imienia i nazwiska -->
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-200 mb-1">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
            @error('email')
                <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie emaila -->
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-200 mb-1">Hasło</label>
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

        <div class="mb-6"> <!-- pole wyboru roli użytkownika -->
            <label for="role" class="block text-gray-200 mb-1">Rola</label>
            <select id="role" name="role" required
                class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500">
                <option value="produkcja" {{ old('role') == 'produkcja' ? 'selected' : '' }}>Produkcja</option>
                <option value="serwis" {{ old('role') == 'serwis' ? 'selected' : '' }}>Serwis</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role')
                <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie roli -->
            @enderror
        </div>

        <button type="submit"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition">
            Zarejestruj
        </button>
    </form>
</div>
</body>
</html>

