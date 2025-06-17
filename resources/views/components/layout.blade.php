<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Zamówień' }}</title>
    <meta name="viewport" content="width=device-width, initial-sale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black">
    <nav class="bg-black px-4 py-3 flex items-center shadow mb-6">
        <a href="{{ url('/welcome') }}" class="text-white font-bold text-lg hover:text-blue-900 transition flex items-center absolute top-0 left-0 ml-3 mt-3">
            Strona główna
        </a>
        <a href="{{ url('/') }}" class="text-white font-bold text-lg hover:text-blue-900 transition flex items-center absolute top-0 right-0 mr-3 mt-3">
            Wyloguj
        </a>
    </nav>
    <main class="container mx-auto py-6 px-4">
        {{ $slot }}
    </main>
</body>
</html>