@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-20">
    <div class="bg-white dark:bg-[#081811] p-10 sm:p-16 rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl reveal">
        <h1 class="text-5xl font-black tracking-tighter text-emerald-950 dark:text-white mb-8" data-t-key="Privacy Policy">{{ __('Privacy Policy') }}</h1>
        
        <div class="space-y-10 text-emerald-800/80 dark:text-emerald-400/80 font-medium leading-relaxed text-lg">
            <section class="space-y-4">
                <h2 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-widest text-sm" data-t-key="1. Data Minimization">{{ __('1. Data Minimization') }}</h2>
                <p data-t-key="AgriAssist only collects the data necessary to provide agricultural intelligence. This includes your name, email, and farm location.">{{ __('AgriAssist only collects the data necessary to provide agricultural intelligence. This includes your name, email, and farm location.') }}</p>
            </section>

            <section class="space-y-4">
                <h2 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-widest text-sm" data-t-key="2. Privacy Shield (Image Processing)">{{ __('2. Privacy Shield (Image Processing)') }}</h2>
                <p data-t-key="Before your photos are analyzed by our AI engines (Google Gemini & Meta Llama), our system automatically strips all EXIF metadata, including camera type and GPS location, to ensure your identity and precise location remain private.">{{ __('Before your photos are analyzed by our AI engines (Google Gemini & Meta Llama), our system automatically strips all EXIF metadata, including camera type and GPS location, to ensure your identity and precise location remain private.') }}</p>
            </section>

            <section class="space-y-4">
                <h2 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-widest text-sm" data-t-key="3. Geocoding Security">{{ __('3. Geocoding Security') }}</h2>
                <p data-t-key="All map searches and location lookups are proxied through our secure servers. Your IP address is never shared with third-party mapping providers like OpenStreetMap or Photon.">{{ __('All map searches and location lookups are proxied through our secure servers. Your IP address is never shared with third-party mapping providers like OpenStreetMap or Photon.') }}</p>
            </section>

            <section class="space-y-4">
                <h2 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-widest text-sm" data-t-key="4. Your Rights">{{ __('4. Your Rights') }}</h2>
                <p data-t-key="You have the right to access, export, or delete your diagnostic history at any time through your profile settings.">{{ __('You have the right to access, export, or delete your diagnostic history at any time through your profile settings.') }}</p>
            </section>
        </div>

        <div class="mt-16 pt-10 border-t border-emerald-50 dark:border-emerald-900/50">
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-3 text-emerald-700 dark:text-emerald-400 font-black hover:text-emerald-500 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span data-t-key="Back to Terminal">{{ __('Back to Terminal') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection
