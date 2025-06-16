<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikacja Zamówień' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    <main class="container mx-auto py-6 px-4">
        {{ $slot }}
    </main>
</body>
</html>
