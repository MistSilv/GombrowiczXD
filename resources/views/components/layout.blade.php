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
    <link rel="manifest" href="/build/manifest.webmanifest">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e293b">
</head>
<body class="bg-black font-sans min-h-screen">
    <nav class="bg-black text-white shadow-md px-6 py-4 flex justify-between items-center rounded-b-xl">
        <div class="flex items-center space-x-6">
            <a href="{{ url('/welcome') }}" class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                🏠 Strona główna
            </a>
            <div class="hidden md:flex items-center space-x-6">
                @auth
                    @if(!auth()->user()->isSerwis())
                        <a href="{{ route('zamowienia.index') }}"
                           class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                            📦 Zamówienia
                        </a>
                    @endif
                @endauth
                <a href="{{ route('straty.index') }}"
                   class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                    🗑️ Straty
                </a>
                <a href="{{ route('wsady.index') }}"
                   class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                    🛒 wsady
                </a>
            </div>
        </div>
        @auth
        <div class="flex items-center space-x-4">
            <div class="hidden md:block">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition">
                        🚪 Wyloguj
                    </button>
                </form>
            </div>
            <button class="burger-btn md:hidden focus:outline-none">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        @endauth
    </nav>
    <div class="mobile-menu md:hidden flex-col bg-gray-900 p-4 space-y-2">
        @auth
            @if(!auth()->user()->isSerwis())
                <a href="{{ route('zamowienia.index') }}"
                   class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
                    📦 Zamówienia
                </a>
            @endif
        @endauth
        <a href="{{ route('straty.index') }}"
           class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
            🗑️ Straty
        </a>
        <a href="{{ route('wsady.index') }}"
                   class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition block text-center">
                    🛒 wsady
                </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="bg-rose-950 hover:bg-red-900 text-white font-semibold px-4 py-2 rounded-lg transition w-full">
                🚪 Wyloguj
            </button>
        </form>
    </div>

    <main class="container mx-auto py-3 px-4">
        {{ $slot }}
    </main>

</body>
</html>