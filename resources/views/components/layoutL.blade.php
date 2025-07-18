<!-- strona główna aplikacji z nagłówkiem i nawigacją dla opcji resetowania -->
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Zamówień' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="/build/manifest.webmanifest">
    <meta name="theme-color" content="#1e293b">
    @livewireStyles
</head>
<body class="bg-black font-sans min-h-screen">
    <nav class="bg-black text-white shadow-md px-6 py-4 flex justify-between items-center rounded-b-xl">
        <div class="flex items-center space-x-4 absolute top-0 left-0 ml-3 mt-3">
            <a href="{{ url('/login') }}" class="text-white font-bold text-lg hover:text-blue-900 transition flex items-center">
                🡨 Powrót
            </a>
        </div>
    </nav>
    
    <main class="container mx-auto py-6 px-4">
        {{ $slot }} 
    </main>
    
@livewireScripts
</body>
</html>