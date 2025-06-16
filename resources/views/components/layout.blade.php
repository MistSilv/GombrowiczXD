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
        <a href="{{ url('/') }}" class="text-white font-bold text-lg hover:text-blue-900 transition flex items-center">
            Strona główna
        </a>
    </nav>
    <main class="container mx-auto py-6 px-4">
        {{ $slot }}
    </main>
</body>
</html>