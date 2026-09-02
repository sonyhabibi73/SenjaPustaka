<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        /* Terapkan tema sebelum paint agar tidak berkedip */
        (function () {
            try {
                var stored = localStorage.getItem('senja-theme');
                var dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.theme = stored || (dark ? 'dark' : 'light');
            } catch (e) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f1922">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'Membaca') · SenjaPustaka</title>
    @vite(['resources/css/app.css', 'resources/js/reader.js'])
</head>
<body class="reader-body">

    @yield('reader-content')

</body>
</html>
