@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-20 min-h-screen">
    <div class="text-center mb-16 space-y-4 reveal">
        <div
            class="inline-flex items-center space-x-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 px-4 py-1.5 rounded-full text-xs font-black tracking-widest uppercase shadow-sm border border-emerald-200 dark:border-emerald-800">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ __('Analysis Complete') }}</span>
        </div>
        <h2 class="text-5xl md:text-6xl font-black tracking-tighter text-emerald-950 dark:text-white">{{ __('Diagnostic
            Report') }}</h2>
        <p class="text-emerald-700/80 dark:text-emerald-400/80 font-bold max-w-2xl mx-auto">
            {{ __('Analysis finalized via AgriAssist Expert Feedback v1.1') }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start printable-area">
        <!-- Image Gallery / Carousel -->
        <div class="space-y-6 reveal" style="transition-delay: 100ms" x-data="{ activeImage: 0 }">
            <div class="group relative">
                <div
                    class="absolute -inset-2 bg-emerald-500/10 rounded-[3rem] blur-2xl opacity-50 group-hover:opacity-100 transition duration-1000">
                </div>

                <div
                    class="relative bg-white dark:bg-[#081811] p-4 rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl overflow-hidden aspect-square z-10">
                    @foreach($diagnosis->image_paths as $index => $path)
                    <img src="{{ Storage::disk('public')->url($path) }}" alt="{{ __('Analyzed specimen') }}"
                        x-show="activeImage === {{ $index }}"
                        class="w-full h-full rounded-[2.2rem] object-cover shadow-inner transition-all duration-700">
                    @endforeach

                    <div class="absolute top-8 left-8">
                        <div
                            class="px-5 py-2.5 bg-emerald-950/90 text-emerald-400 font-extrabold rounded-[1.2rem] text-[10px] uppercase tracking-widest border-2 border-emerald-800 shadow-xl backdrop-blur-md flex items-center space-x-2">
                            <i data-lucide="microscope" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ __('Protocol #') }}{{ str_pad($diagnosis->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>

                    @if(count($diagnosis->image_paths) > 1)
                    <div class="absolute bottom-8 inset-x-0 flex justify-center space-x-3 no-print">
                        @foreach($diagnosis->image_paths as $index => $path)
                        <button @click="activeImage = {{ $index }}"
                            class="w-3 h-3 rounded-full transition-all duration-300 border border-emerald-900 shadow-sm"
                            :class="activeImage === {{ $index }} ? 'bg-emerald-500 scale-125 w-8' : 'bg-emerald-100 hover:bg-emerald-300'"></button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Thumbnail Grid -->
            @if(count($diagnosis->image_paths) > 1)
            <div class="grid grid-cols-5 gap-4 px-2 relative z-10 no-print">
                @foreach($diagnosis->image_paths as $index => $path)
                <button @click="activeImage = {{ $index }}"
                    class="relative rounded-[1.5rem] overflow-hidden aspect-square border-4 transition-all duration-300"
                    :class="activeImage === {{ $index }} ? 'border-emerald-600 scale-105 shadow-xl' : 'border-emerald-100 dark:border-emerald-900 opacity-60 hover:opacity-100'">
                    <img src="{{ Storage::disk('public')->url($path) }}" class="w-full h-full object-cover">
                    <div x-show="activeImage === {{ $index }}" class="absolute inset-0 bg-emerald-900/20"></div>
                </button>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Details Card -->
        <div class="space-y-8 reveal" style="transition-delay: 200ms">
            <div
                class="bg-white dark:bg-[#081811] p-10 rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl relative overflow-hidden group">

                <div
                    class="flex justify-between items-start mb-10 border-b-2 border-emerald-50 dark:border-emerald-900/50 pb-8">
                    <div class="space-y-2">
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-500 bg-emerald-50 dark:bg-[#0a1e15] inline-block px-3 py-1 rounded-lg border border-emerald-100 dark:border-emerald-800">
                            {{ __('Diagnostic Finding') }}</p>
                        <h3
                            class="text-4xl sm:text-5xl font-black tracking-tighter text-emerald-950 dark:text-white leading-none mt-2">
                            {{ $diagnosis->disease }}</h3>
                    </div>
                    <div
                        class="flex flex-col items-end bg-emerald-950 dark:bg-[#06120c] p-4 rounded-2xl border-2 border-emerald-800 shadow-inner">
                        <div class="text-3xl font-black text-amber-400">{{ number_format($diagnosis->confidence * 100,
                            1) }}%</div>
                        <div class="text-[9px] font-black tracking-widest text-emerald-500 uppercase mt-1">{{
                            __('Reliability') }}
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div
                        class="p-8 bg-emerald-50 dark:bg-emerald-950/30 rounded-[2rem] border-2 border-emerald-200 dark:border-emerald-800 relative shadow-sm">
                        <div
                            class="absolute -top-4 left-8 px-5 py-2 bg-emerald-700 text-white border-2 border-emerald-900 rounded-full text-[10px] font-black uppercase tracking-widest leading-none flex items-center space-x-2 shadow-md">
                            <i data-lucide="clipboard-plus" class="w-3 h-3 text-emerald-200"></i>
                            <span>{{ __('Treatment Protocol') }}</span>
                        </div>
                        <div class="flex items-start space-x-5 mt-2">
                            <i data-lucide="shield-alert"
                                class="w-8 h-8 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                            <p class="text-emerald-950 dark:text-emerald-100 leading-relaxed font-bold text-lg">
                                {{ $diagnosis->treatment }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div
                            class="p-6 bg-white dark:bg-[#06120c] rounded-2xl border-2 border-emerald-100 dark:border-emerald-900 text-center shadow-sm">
                            <i data-lucide="calendar"
                                class="w-8 h-8 mx-auto mb-3 text-emerald-600 dark:text-emerald-500"></i>
                            <p class="text-[10px] font-black tracking-widest text-emerald-500 uppercase">{{ __('Analyzed
                                On') }}</p>
                            <p class="text-lg font-black text-emerald-950 dark:text-white mt-1">{{
                                $diagnosis->created_at->format('M d, Y') }}</p>
                        </div>
                        <div
                            class="p-6 bg-white dark:bg-[#06120c] rounded-2xl border-2 border-emerald-100 dark:border-emerald-900 text-center shadow-sm">
                            <i data-lucide="images"
                                class="w-8 h-8 mx-auto mb-3 text-emerald-600 dark:text-emerald-500"></i>
                            <p class="text-[10px] font-black tracking-widest text-emerald-500 uppercase">{{ __('Data
                                Points') }}</p>
                            <p class="text-lg font-black text-emerald-950 dark:text-white mt-1">{{
                                count($diagnosis->image_paths) }} {{ __('Specimens') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('detect') }}"
                        class="flex-1 py-5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-[1.5rem] font-black text-lg shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center space-x-3 border-b-4 border-emerald-900">
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        <span>{{ __('Start New Analysis') }}</span>
                    </a>
                    <button onclick="window.print()"
                        class="px-8 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-[1.5rem] hover:bg-emerald-100 dark:hover:bg-emerald-900 transition-all flex items-center justify-center">
                        <i data-lucide="printer" class="w-6 h-6"></i>
                    </button>
                    <button
                        class="px-8 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-[1.5rem] hover:bg-emerald-100 dark:hover:bg-emerald-900 transition-all flex items-center justify-center">
                        <i data-lucide="share-2" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Warning Alert -->
            <div
                class="p-8 bg-amber-50 dark:bg-amber-950/20 border-l-8 border-amber-500 rounded-r-[2rem] flex items-start space-x-5 shadow-xl border-y-2 border-r-2 border-y-amber-200 border-r-amber-200 dark:border-y-amber-900 dark:border-r-amber-900">
                <div class="bg-amber-100 dark:bg-amber-900/50 p-3 rounded-xl shrink-0">
                    <i data-lucide="siren" class="w-6 h-6 text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <h4 class="font-black tracking-tight text-amber-900 dark:text-amber-400 text-lg mb-1">{{ __('Verify
                        Severe Cases') }}</h4>
                    lass="text-sm text-amber-800/80 dark:text-amber-500/80 font-bold leading-relaxed">
                   
                        {{ __('Automated diagnostic results. Please verify critical issues with a local agricultural authority.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection