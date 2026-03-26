<!DOCTYPE html>
<html lang="en" x-data="themeApp()" :class="{ 'dark': darkMode }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ __('AgriAssist - Professional agricultural diagnostic suite for Sri Lankan farmers. Protect your harvest with expert plant disease analysis and seasonal planning.') }}">
    <meta name="keywords" content="agriculture, sri lanka, plant disease, farming, crop planner, paddy, tea, coconut">
    <meta property="og:title" content="AgriAssist - Professional Plant Diagnostics">
    <meta property="og:description"
        content="{{ __('Expert-level diagnostic feedback for Sri Lankan farmers. Analyze crop diseases and plan your harvest with precision.') }}">
    <meta property="og:type" content="website">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('AgriAssist - Professional Plant Diagnostics') }}</title>

    <script>
        window.__AGRI_CONFIG = {
            locale: '{{ app()->getLocale() }}',
            supportedLocales: ['en', 'si', 'ta']
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body
    class="bg-emerald-50 dark:bg-[#06120c] text-emerald-950 dark:text-emerald-50 transition-colors duration-500 font-sans selection:bg-amber-300 selection:text-emerald-950">

    @include('partials.header')

    <main class="relative pt-20">
        @yield('content')
    </main>

    @include('partials.footer')

    @yield('scripts')
    @stack('scripts')
</body>

</html>