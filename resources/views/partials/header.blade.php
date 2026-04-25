<!-- Navigation -->
<div class="fixed w-full z-50 transition-all duration-500 bg-white/95 dark:bg-[#081811]/95 backdrop-blur-xl border-b-2 border-emerald-100/50 dark:border-emerald-900/30 shadow-sm"
    id="nav-container">
    <nav class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-12 h-20 flex justify-between items-center" id="navbar">

        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                <div class="w-11 h-11 bg-emerald-700 dark:bg-emerald-600 rounded-xl flex items-center justify-center text-amber-300 shadow-lg transform group-hover:rotate-12 group-hover:scale-110 transition-all duration-300">
                    <i data-lucide="leaf" class="w-6 h-6"></i>
                </div>
                <span class="text-2xl font-black tracking-tighter text-emerald-950 dark:text-white group-hover:text-emerald-600 transition-colors" data-t-key="AgriAssist">
                    {{ __('AgriAssist') }}
                </span>
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-8 xl:space-x-10">
            <div class="flex items-center space-x-1">
                @foreach([
                    ['route' => 'home', 'label' => 'Home'],
                    ['route' => 'detect', 'label' => 'Diagnostic Terminal'],
                    ['route' => 'planner.index', 'label' => 'Crop Planner'],
                    ['route' => 'marketplace.index', 'label' => 'Marketplace']
                ] as $nav)
                    <a href="{{ route($nav['route']) }}" data-t-key="{{ $nav['label'] }}"
                        class="px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 {{ request()->routeIs($nav['route']) ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' : 'text-emerald-900/40 dark:text-emerald-500/40 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20' }}">
                        {{ __($nav['label']) }}
                    </a>
                @endforeach
            </div>

            <div class="h-8 w-px bg-emerald-100 dark:bg-emerald-900 mx-2"></div>

            <div class="flex items-center space-x-3">
                <!-- Language Dropdown -->
                <div class="relative group">
                    <button class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400 hover:border-emerald-500 transition-all shadow-sm">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                    </button>
                    <div class="absolute right-0 top-full pt-4 w-40 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                        <div class="bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 shadow-2xl rounded-2xl overflow-hidden backdrop-blur-xl">
                            @foreach(['en' => 'English', 'si' => 'Sinhala', 'ta' => 'Tamil'] as $code => $name)
                                <a href="{{ route('lang.switch', $code) }}" @click.prevent="switchLanguageTo('{{ $code }}')"
                                    class="flex items-center px-4 py-3 text-sm font-bold {{ app()->getLocale() == $code ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/40' : 'text-emerald-900 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900/50' }} transition-colors"
                                    data-t-key="{{ $name }}">{{ __($name) }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button @click="toggleDark()"
                    class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400 hover:border-emerald-500 transition-all shadow-sm">
                    <span x-show="!darkMode" x-cloak><i data-lucide="moon" class="w-5 h-5"></i></span>
                    <span x-show="darkMode" x-cloak><i data-lucide="sun" class="w-5 h-5"></i></span>
                </button>

                @auth
                    <div class="relative group">
                        <button class="flex items-center space-x-3 pl-2 pr-4 py-1.5 rounded-2xl bg-emerald-950 dark:bg-emerald-900/20 border-2 border-emerald-900 dark:border-emerald-800 text-white hover:scale-[1.02] transition-all shadow-lg">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xs">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="font-black text-[11px] uppercase tracking-[0.2em] max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 opacity-50 group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                        <div class="absolute right-0 top-full pt-4 w-56 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 shadow-2xl rounded-2xl overflow-hidden">
                                <a href="{{ route('profile.show') }}"
                                    class="flex items-center space-x-3 px-5 py-4 text-sm font-bold text-emerald-950 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900/50 border-b border-emerald-50 dark:border-emerald-900/50">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-500"></i>
                                    <span data-t-key="My Profile">{{ __('My Profile') }}</span>
                                </a>
                                <a href="{{ route('marketplace.messages.index') }}"
                                    class="flex items-center space-x-3 px-5 py-4 text-sm font-bold text-emerald-950 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-900/50 border-b border-emerald-50 dark:border-emerald-900/50">
                                    <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                                    <span data-t-key="Messages">{{ __('Messages') }}</span>
                                </a>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center space-x-3 px-5 py-4 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span data-t-key="Sign Out">{{ __('Sign Out') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('login') }}" class="px-5 py-2.5 text-[11px] font-black uppercase tracking-[0.2em] text-emerald-950 dark:text-emerald-400 hover:text-emerald-600 transition-colors" data-t-key="Sign In">{{ __('Sign In') }}</a>
                        <a href="{{ route('register') }}" class="px-6 py-2.5 bg-emerald-100 dark:bg-emerald-900/40 border-2 border-emerald-200 dark:border-emerald-800 rounded-xl font-black text-emerald-800 dark:text-emerald-400 text-[11px] uppercase tracking-[0.2em] hover:bg-emerald-200 dark:hover:bg-emerald-900 transition-all" data-t-key="Join">{{ __('Join') }}</a>
                    </div>
                @endauth

                <a href="{{ route('detect') }}"
                    class="ml-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black shadow-lg shadow-emerald-700/20 hover:shadow-xl hover:-translate-y-0.5 active:scale-95 transition-all text-sm flex items-center space-x-2">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-300"></i>
                    <span data-t-key="Scan Now" class="uppercase tracking-widest">{{ __('Scan Now') }}</span>
                </a>
            </div>
        </div>

        <!-- Medium/Small Tablet View -->
        <div class="lg:hidden flex items-center space-x-3">
            <button @click="toggleDark()"
                class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400 shadow-sm">
                <span x-show="!darkMode" x-cloak><i data-lucide="moon" class="w-5 h-5"></i></span>
                <span x-show="darkMode" x-cloak><i data-lucide="sun" class="w-5 h-5"></i></span>
            </button>
            <button @click="toggleMenu()"
                class="p-2.5 rounded-xl bg-emerald-950 text-white shadow-lg hover:scale-105 active:scale-95 transition-all">
                <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </nav>

    <!-- Mobile/Tablet Menu Overlay -->
    <div x-show="mobileMenuOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-8 scale-95" 
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
        class="lg:hidden absolute top-full left-4 right-4 mt-2 bg-white/95 dark:bg-[#081811]/95 backdrop-blur-2xl shadow-2xl rounded-[2.5rem] border-2 border-emerald-100 dark:border-emerald-900 overflow-hidden z-50"
        x-cloak>
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    ['route' => 'home', 'label' => 'Home', 'icon' => 'home'],
                    ['route' => 'detect', 'label' => 'Diagnostic Terminal', 'icon' => 'camera'],
                    ['route' => 'planner.index', 'label' => 'Crop Planner', 'icon' => 'calendar'],
                    ['route' => 'marketplace.index', 'label' => 'Marketplace', 'icon' => 'shopping-bag']
                ] as $nav)
                    <a href="{{ route($nav['route']) }}" @click="mobileMenuOpen = false"
                        class="flex items-center space-x-4 p-4 rounded-2xl {{ request()->routeIs($nav['route']) ? 'bg-emerald-600 text-white' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-900 dark:text-emerald-100' }} transition-all">
                        <i data-lucide="{{ $nav['icon'] }}" class="w-5 h-5 opacity-70"></i>
                        <span class="font-black uppercase tracking-widest text-xs">{{ __($nav['label']) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="h-px bg-emerald-100 dark:bg-emerald-900"></div>

            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                @auth
                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center font-black text-emerald-600">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="font-black uppercase tracking-widest text-xs text-emerald-950 dark:text-white">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="flex space-x-2 w-full sm:w-auto">
                        <a href="{{ route('profile.show') }}" class="flex-1 sm:flex-none px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 text-[10px] font-black uppercase tracking-widest text-center">{{ __('Profile') }}</a>
                        <form action="{{ route('logout') }}" method="POST" class="flex-1 sm:flex-none m-0">
                            @csrf
                            <button type="submit" class="w-full px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 text-[10px] font-black uppercase tracking-widest">{{ __('Sign Out') }}</button>
                        </form>
                    </div>
                @else
                    <div class="flex space-x-3 w-full">
                        <a href="{{ route('login') }}" class="flex-1 py-4 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-950 dark:text-emerald-400 rounded-2xl font-black uppercase tracking-widest text-center text-xs border-2 border-emerald-100 dark:border-emerald-900">{{ __('Sign In') }}</a>
                        <a href="{{ route('register') }}" class="flex-1 py-4 bg-emerald-950 text-white rounded-2xl font-black uppercase tracking-widest text-center text-xs shadow-lg">{{ __('Join') }}</a>
                    </div>
                @endauth
            </div>

            <div class="flex space-x-2">
                @foreach(['en' => 'EN', 'si' => 'සිං', 'ta' => 'த'] as $code => $label)
                    <a href="{{ route('lang.switch', $code) }}"
                        @click.prevent="switchLanguageTo('{{ $code }}'); mobileMenuOpen = false"
                        class="flex-1 py-3 rounded-xl {{ app()->getLocale() == $code ? 'bg-emerald-600 text-white' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-900/40 dark:text-emerald-500/40 hover:text-emerald-600' }} font-black text-center text-xs transition-all">{{ $label }}</a>
                @endforeach
            </div>

            <a href="{{ route('detect') }}" @click="mobileMenuOpen = false"
                class="block py-5 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-center shadow-xl shadow-emerald-700/30 active:scale-95 transition-all text-sm">
                <i data-lucide="zap" class="w-4 h-4 inline-block mr-2 text-amber-300"></i>
                {{ __('Start Diagnostic Scan') }}
            </a>
        </div>
    </div>
</div>