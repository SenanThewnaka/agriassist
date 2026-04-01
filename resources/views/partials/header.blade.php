<!-- Navigation -->
<div class="fixed w-full z-50 transition-all duration-300 bg-white/95 dark:bg-[#081811]/95 backdrop-blur-xl border-b-2 border-emerald-100 dark:border-emerald-900/50 shadow-sm"
    id="nav-container">
    <nav class="max-w-7xl mx-auto px-6 sm:px-8 h-20 flex justify-between items-center" id="navbar">

        <div class="flex items-center space-x-3">
            <div
                class="w-12 h-12 bg-emerald-700 dark:bg-emerald-600 rounded-xl flex items-center justify-center text-amber-300 shadow-md transform hover:rotate-6 transition-transform">
                <i data-lucide="leaf" class="w-7 h-7"></i>
            </div>
            <span class="text-2xl font-black tracking-tighter text-emerald-950 dark:text-white"
                data-t-key="AgriAssist">{{ __('AgriAssist')
                }}</span>
        </div>

        <div class="hidden md:flex items-center space-x-10">
            <a href="{{ route('home') }}" data-t-key="Home"
                class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{
                __('Home') }}</a>
            <a href="{{ route('detect') }}" data-t-key="Diagnostic Terminal"
                class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{
                __('Diagnostic Terminal') }}</a>
            <a href="{{ route('planner.index') }}" data-t-key="Crop Planner"
                class="font-bold text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors tracking-wide">{{
                __('Crop Planner') }}</a>

            <div class="h-6 w-1 bg-emerald-200 dark:bg-emerald-800/50 rounded-full"></div>

            <div class="relative group pb-4 -mb-4">
                <button
                    class="mt-4 p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-transform shadow-sm">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                </button>
                <div
                    class="absolute right-0 top-16 w-32 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 shadow-xl rounded-2xl overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                    <a href="{{ route('lang.switch', 'en') }}" @click.prevent="switchLanguageTo('en')"
                        class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900"
                        data-t-key="English">{{
                        __('English') }}</a>
                    <a href="{{ route('lang.switch', 'si') }}" @click.prevent="switchLanguageTo('si')"
                        class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900 border-y border-emerald-50 dark:border-emerald-900/50"
                        data-t-key="Sinhala">{{
                        __('Sinhala') }}</a>
                    <a href="{{ route('lang.switch', 'ta') }}" @click.prevent="switchLanguageTo('ta')"
                        class="block px-4 py-3 text-sm font-bold text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900"
                        data-t-key="Tamil">{{
                        __('Tamil') }}</a>
                </div>
            </div>

            <button @click="toggleDark()"
                class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-transform shadow-sm">
                <span x-show="!darkMode" x-cloak><i data-lucide="moon" class="w-5 h-5"></i></span>
                <span x-show="darkMode" x-cloak><i data-lucide="sun" class="w-5 h-5"></i></span>
            </button>

            <a href="{{ route('detect') }}"
                class="px-8 py-3.5 border-b-4 border-emerald-900 dark:border-emerald-800 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-2xl font-black shadow-lg shadow-emerald-700/30 hover:shadow-xl hover:-translate-y-1 active:scale-95 transition-all text-lg flex items-center space-x-2">
                <i data-lucide="zap" class="w-5 h-5 text-amber-300"></i>
                <span data-t-key="Scan Now">{{ __('Scan Now') }}</span>
            </a>
        </div>

        <div class="md:hidden flex items-center space-x-4">
            <button @click="toggleDark()"
                class="p-2.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400">
                <span x-show="!darkMode" x-cloak><i data-lucide="moon" class="w-5 h-5"></i></span>
                <span x-show="darkMode" x-cloak><i data-lucide="sun" class="w-5 h-5"></i></span>
            </button>
            <button @click="toggleMenu()"
                class="p-2 rounded-lg text-emerald-950 dark:text-white hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                <i data-lucide="menu" class="w-7 h-7" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-7 h-7" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="md:hidden absolute top-full left-0 w-full bg-white dark:bg-[#081811] shadow-[0_10px_20px_-10px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_20px_-10px_rgba(0,0,0,0.5)] border-t border-emerald-50 dark:border-emerald-900/30"
        x-cloak>
        <div class="px-6 py-6 space-y-5 flex flex-col items-center sm:items-start text-center sm:text-left">
            <a href="{{ route('home') }}" @click="mobileMenuOpen = false" data-t-key="Home"
                class="font-black tracking-tight text-2xl text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors w-full">{{
                __('Home') }}</a>
            <a href="{{ route('detect') }}" @click="mobileMenuOpen = false" data-t-key="Diagnostic Terminal"
                class="font-black tracking-tight text-2xl text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors w-full">{{
                __('Diagnostic Terminal') }}</a>
            <a href="{{ route('planner.index') }}" @click="mobileMenuOpen = false" data-t-key="Crop Planner"
                class="font-black tracking-tight text-2xl text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors w-full">{{
                __('Crop Planner') }}</a>

            <div
                class="border-t-2 border-emerald-100 dark:border-emerald-900/80 pt-6 mt-4 w-full flex flex-col items-center sm:items-start">
                <div class="flex space-x-3 w-full justify-center sm:justify-start">
                    <a href="{{ route('lang.switch', 'en') }}"
                        @click.prevent="switchLanguageTo('en'); mobileMenuOpen = false"
                        class="flex-1 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-800/50 text-emerald-800 dark:text-emerald-200 font-bold transition-colors shadow-sm text-center"
                        data-t-key="English">{{ __('English') }}</a>
                    <a href="{{ route('lang.switch', 'si') }}"
                        @click.prevent="switchLanguageTo('si'); mobileMenuOpen = false"
                        class="flex-1 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-800/50 text-emerald-800 dark:text-emerald-200 font-bold transition-colors shadow-sm text-center border-x-2 border-white dark:border-[#081811]"
                        data-t-key="Sinhala">{{ __('Sinhala') }}</a>
                    <a href="{{ route('lang.switch', 'ta') }}"
                        @click.prevent="switchLanguageTo('ta'); mobileMenuOpen = false"
                        class="flex-1 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-800/50 text-emerald-800 dark:text-emerald-200 font-bold transition-colors shadow-sm text-center"
                        data-t-key="Tamil">{{ __('Tamil') }}</a>
                </div>
            </div>

            <a href="{{ route('detect') }}" @click="mobileMenuOpen = false"
                class="mt-6 py-4 bg-emerald-700 hover:bg-emerald-600 border-b-4 border-emerald-900 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-2xl font-black shadow-lg shadow-emerald-700/30 active:scale-95 transition-all w-full flex items-center justify-center space-x-2 text-xl">
                <i data-lucide="zap" class="w-6 h-6 text-amber-300"></i>
                <span data-t-key="Scan Now">{{ __('Scan Now') }}</span>
            </a>
        </div>
    </div>
</div>