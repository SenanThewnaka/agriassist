<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="themeApp()" :class="{ 'dark': darkMode }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ __('AgriAssist - Professional agricultural diagnostic suite for Sri Lankan farmers. Protect your harvest with expert plant disease analysis and seasonal planning.') }}">
    <meta name="keywords" content="agriculture, plant disease, farming, crop planner, paddy, tea, coconut, potato, banana, vegetables, fruits">
    <meta property="og:title" content="AgriAssist - Professional Plant Diagnostics">
    <meta property="og:description"
        content="{{ __('Expert-level diagnostic feedback for Sri Lankan farmers. Analyze crop diseases and plan your harvest with precision.') }}">
    <meta property="og:type" content="website">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('AgriAssist - Professional Plant Diagnostics') }}</title>

    <!-- Localization Engine -->
    <script>
        window.__AGRI_CONFIG = {
            locale: @json(app()->getLocale()),
            translations: {
                en: @json(json_decode(file_get_contents(lang_path('en.json')), true)),
                si: @json(json_decode(file_get_contents(lang_path('si.json')), true)),
                ta: @json(json_decode(file_get_contents(lang_path('ta.json')), true))
            }
        };

        window.switchLanguageTo = async function(lang) {
            if (!['en', 'si', 'ta'].includes(lang)) return;

            console.log('Switching language to:', lang);
            window.__AGRI_CONFIG.locale = lang;
            document.documentElement.lang = lang;
            localStorage.setItem('agriassist_locale', lang);

            // Update all elements with data-t-key
            document.querySelectorAll('[data-t-key]').forEach(el => {
                const key = el.getAttribute('data-t-key');
                const translation = window.__AGRI_CONFIG.translations[lang][key] || key;
                
                if (el.tagName === 'INPUT' && el.placeholder) {
                    el.placeholder = translation;
                } else if (el.tagName === 'SELECT') {
                    // Update all options that have a data-t-key
                    Array.from(el.options).forEach(opt => {
                        const optKey = opt.getAttribute('data-t-key') || (opt.value === "" ? key : null);
                        if (optKey) {
                            opt.text = window.__AGRI_CONFIG.translations[lang][optKey] || optKey;
                        }
                    });
                } else {
                    // Use innerHTML for paragraphs or divs that might have formatted text/breaks
                    const simpleTags = ['SPAN', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'A', 'BUTTON', 'LABEL'];
                    if (simpleTags.includes(el.tagName)) {
                        el.innerText = translation;
                    } else {
                        el.innerHTML = translation;
                    }
                }
            });

            // Update meta description
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc) {
                const descKey = 'AgriAssist - Professional agricultural diagnostic suite for Sri Lankan farmers. Protect your harvest with expert plant disease analysis and seasonal planning.';
                metaDesc.content = window.__AGRI_CONFIG.translations[lang][descKey] || metaDesc.content;
            }

            // CRITICAL: Notify server and WAIT for session to update before triggering events
            try {
                const response = await fetch(`/lang/${lang}?json=1`);
                const data = await response.json();
                console.log('Server locale synced:', data.locale);
            } catch (err) {
                console.error('Failed to sync locale with server:', err);
            }

            // Trigger events for specific pages to update their custom components
            window.dispatchEvent(new CustomEvent('agriassist-locale-changed', { detail: { locale: lang } }));
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    
    <!-- Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body
    class="bg-emerald-50 dark:bg-[#06120c] text-emerald-950 dark:text-emerald-50 transition-colors duration-500 font-sans selection:bg-amber-300 selection:text-emerald-950">

    @include('partials.header')

    <main class="relative pt-20">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.toast')
    @include('partials.consent_banner')

    @yield('scripts')
    @stack('scripts')
    
    <script>
        window.showToast = function(message, type = 'success', title = null) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type, title }
            }));
        };

        // Check for session flash messages
        @if(session('status'))
            window.addEventListener('DOMContentLoaded', () => showToast("{{ session('status') }}", 'success'));
        @endif
        @if(session('error'))
            window.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));
        @endif
        @if($errors->any())
            window.addEventListener('DOMContentLoaded', () => showToast("{{ $errors->first() }}", 'error'));
        @endif
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>