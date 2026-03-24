<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }" class="scroll-smooth">

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
    <title>AgriAssist - Professional Plant Diagnostics</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #047857;
            /* emerald-700 */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
            /* emerald-600 */
        }

        @media print {

            nav,
            footer,
            .no-print,
            button,
            a:not([href^="http"]) {
                display: none !important;
            }

            .printable-area {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
                background: white !important;
                color: black !important;
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>

<body
    class="bg-emerald-50 dark:bg-[#06120c] text-emerald-950 dark:text-emerald-50 transition-colors duration-500 font-sans selection:bg-amber-300 selection:text-emerald-950">

    <!-- Navigation -->
    <div class="fixed w-full z-50 transition-all duration-300 bg-white/95 dark:bg-[#081811]/95 backdrop-blur-xl border-b-2 border-emerald-100 dark:border-emerald-900/50 shadow-sm"
        id="nav-container">
        <nav class="max-w-7xl mx-auto px-6 sm:px-8 h-20 flex justify-between items-center" id="navbar">

            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-emerald-700 dark:bg-emerald-600 rounded-xl flex items-center justify-center text-amber-300 shadow-md transform hover:rotate-6 transition-transform">
                    <i data-lucide="leaf" class="w-7 h-7"></i>
                </div>
                <span class="text-2xl font-black tracking-tighter text-emerald-950 dark:text-white">{{ __('AgriAssist') }}</span>
            </div>

            <div class="hidden md:flex items-center space-x-10">
                <a href="{{ route('home') }}"
                    class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{ __('Home') }}</a>
                <a href="{{ route('detect') }}"
                    class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{ __('Diagnostic Terminal') }}</a>
                <a href="{{ route('planner.index') }}"
                    class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{ __('Crop Planner') }}</a>

                <div class="h-6 w-1 bg-emerald-200 dark:bg-emerald-800/50 rounded-full"></div>

                <div class="relative group pb-4 -mb-4">
                    <button
                        class="mt-4 p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-transform shadow-sm">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                    </button>
                    <div
                        class="absolute right-0 top-16 w-32 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 shadow-xl rounded-2xl overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                        <a href="{{ route('lang.switch', 'en') }}"
                            class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900">{{ __('English') }}</a>
                        <a href="{{ route('lang.switch', 'si') }}"
                            class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900 border-y border-emerald-50 dark:border-emerald-900/50">{{ __('Sinhala') }}</a>
                        <a href="{{ route('lang.switch', 'ta') }}"
                            class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900">{{ __('Tamil') }}</a>
                    </div>
                </div>

                <button @click="darkMode = !darkMode"
                    class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-transform shadow-sm">
                    <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
                    <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
                </button>

                <a href="{{ route('detect') }}"
                    class="px-8 py-3.5 border-b-4 border-emerald-900 dark:border-emerald-800 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-2xl font-black shadow-lg shadow-emerald-700/30 hover:shadow-xl hover:-translate-y-1 active:scale-95 transition-all text-lg flex items-center space-x-2">
                    <i data-lucide="zap" class="w-5 h-5 text-amber-300"></i>
                    <span>{{ __('Scan Now') }}</span>
                </a>
            </div>

            <div class="md:hidden flex items-center space-x-4">
                <button @click="darkMode = !darkMode"
                    class="p-2.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400">
                    <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
                    <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
                </button>
                <button class="p-2 rounded-lg text-emerald-950 dark:text-white"><i data-lucide="menu"
                        class="w-7 h-7"></i></button>
            </div>
        </nav>
    </div>

    <main class="relative pt-20">
        @yield('content')
    </main>

    <footer class="bg-[#081811] text-emerald-50 py-20 border-t-8 border-emerald-900">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="col-span-1 md:col-span-2 space-y-6">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 bg-emerald-800 rounded-xl flex items-center justify-center text-amber-400 border border-emerald-700">
                        <i data-lucide="leaf" class="w-6 h-6"></i>
                    </div>
                    <span class="text-3xl font-black tracking-tighter text-white">{{ __('AgriAssist') }}</span>
                </div>
                <p class="text-emerald-400/80 max-w-sm leading-relaxed font-semibold text-lg">
                    {{ __('Advancing agricultural technology for Sri Lankan farmers. Precise, fast, and actionable diagnostics to protect your harvest.') }}
                </p>
                <div class="flex space-x-4 pt-4">
                    <a href="#"
                        class="p-3 rounded-full bg-emerald-900 border border-emerald-800 hover:bg-emerald-800 transition-colors text-emerald-400"><i
                            data-lucide="twitter" class="w-5 h-5"></i></a>
                    <a href="#"
                        class="p-3 rounded-full bg-emerald-900 border border-emerald-800 hover:bg-emerald-800 transition-colors text-emerald-400"><i
                            data-lucide="facebook" class="w-5 h-5"></i></a>
                    <a href="#"
                        class="p-3 rounded-full bg-emerald-900 border border-emerald-800 hover:bg-emerald-800 transition-colors text-emerald-400"><i
                            data-lucide="linkedin" class="w-5 h-5"></i></a>
                </div>
            </div>

            <div>
                <h4 class="font-black text-xl mb-6 text-emerald-300 uppercase tracking-widest">{{ __('Platform') }}</h4>
                <ul class="space-y-4 font-bold text-emerald-500">
                    <li><a href="#" class="hover:text-amber-400 transition-colors">{{ __('How it Works') }}</a></li>
                    <li><a href="{{ route('detect') }}" class="hover:text-amber-400 transition-colors">{{ __('Diagnostics') }}</a>
                    </li>
                    <li><a href="{{ route('planner.index') }}" class="hover:text-amber-400 transition-colors">{{ __('Crop Planner') }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-xl mb-6 text-emerald-300 uppercase tracking-widest">{{ __('Support') }}</h4>
                <ul class="space-y-4 font-bold text-emerald-500">
                    <li><a href="#" class="hover:text-amber-400 transition-colors">{{ __('Farmer Guides') }}</a></li>
                    <li><a href="#" class="hover:text-amber-400 transition-colors">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="#" class="hover:text-amber-400 transition-colors">{{ __('Contact Us') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-20 pt-8 border-t-2 border-emerald-900 text-center">
            <p class="text-emerald-600 font-bold tracking-wide">{!! __('&copy; 2026 AgriAssist Sri Lanka. Built with precision for the modern farmer.') !!}</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Modern Reveal on scroll using IntersectionObserver
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
        });

        // Sticky nav shadow
        window.addEventListener('scroll', () => {
            const navContainer = document.getElementById('nav-container');
            if (window.scrollY > 20) {
                navContainer.classList.add('shadow-md');
                navContainer.classList.remove('shadow-sm');
            } else {
                navContainer.classList.remove('shadow-md');
                navContainer.classList.add('shadow-sm');
            }
        });
    </script>
    @yield('scripts')
</body>

</html>