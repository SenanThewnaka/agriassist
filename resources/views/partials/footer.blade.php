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
                <li><a href="{{ route('detect') }}" class="hover:text-amber-400 transition-colors">{{
                        __('Diagnostics') }}</a>
                </li>
                <li><a href="{{ route('planner.index') }}" class="hover:text-amber-400 transition-colors">{{
                        __('Crop Planner') }}</a></li>
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
        <p class="text-emerald-600 font-bold tracking-wide">{!! __('&copy; 2026 AgriAssist Sri Lanka. Built with
            precision for the modern farmer.') !!}</p>
    </div>
</footer>