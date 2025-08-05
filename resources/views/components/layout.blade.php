<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Zamówień' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            safelist: [
                'active', 
                'mobile-menu-active',
                'burger-active'
        ]
        }
    </script>
    <link href="/css/burger.css" rel="stylesheet">
    <script src="/js/burger.js" defer></script>
    <link rel="manifest" href="/manifest.webmanifest">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e293b">
</head>
<body class="bg-black font-sans min-h-screen">
    <nav class="bg-black text-white shadow-md px-6 py-4 flex justify-between items-center rounded-b-xl">
        <div class="flex items-center space-x-2">
            <a href="{{ url('/welcome') }}" class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                🏠 Strona główna
            </a>
            <div class="hidden lg:flex items-center space-x-2">
                @auth
                    @if(!auth()->user()->isSerwis())
                        <a href="{{ route('zamowienia.index') }}"
                           class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                            📦 Zamówienia
                        </a>
                    @endif
                @endauth
                <a href="{{ route('produkty.zamowienie.formularz') }}"
                class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                    🧾 Nowe zamówienie
                </a>
                <a href="{{ route('function.page') }}"
                    class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                    ⚙️ Panel Funkcji
                </a>
            </div>
        </div>
        @auth
        <div class="flex items-center space-x-4">
            <div class="hidden lg:block">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="bg-red-800 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                        🚪 Wyloguj
                    </button>
                </form>
            </div>
            <button class="burger-btn lg:hidden focus:outline-none text-white">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        @endauth
    </nav>
    <div class="mobile-menu lg:hidden flex-col bg-inherited p-4 space-y-2">
         <a href="{{ route('zamowienia.index') }}"
                   class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
                    📦 Zamówienia
                </a>
          <a href="{{ route('produkty.zamowienie.formularz') }}"
                class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
                    🧾 Nowe zamówienie
                </a>
                 <a href="{{ route('function.page') }}"
                    class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
                    ⚙️ Panel Funkcji
                </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="bg-red-800 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg transition w-full">
                🚪 Wyloguj
            </button>
        </form>
    </div>

    <main class="px-4">
        {{ $slot }}
    </main>

</body>
</html>