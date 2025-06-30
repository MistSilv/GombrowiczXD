<!-- strona główna aplikacji z nagłówkiem i nawigacją dla opcji resetowania -->
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Zamówień' }}</title>
    <meta name="viewport" content="width=device-width, initial-sale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="/build/manifest.webmanifest">
    <meta name="theme-color" content="#1e293b">
</head>
<body class="bg-black">
    <nav class="bg-black px-4 py-3 flex items-center shadow mb-6 relative">
        <div class="flex items-center space-x-4 absolute top-0 left-0 ml-3 mt-3">
            <a href="{{ url('/login') }}" class="text-white font-bold text-lg hover:text-blue-900 transition flex items-center">
                Powrót
            </a>
        </div>
    </nav>
    
    <main class="container mx-auto py-6 px-4">
        {{ $slot }} <!-- miejsce na zawartość strony -->
    </main>
</body>
</html>