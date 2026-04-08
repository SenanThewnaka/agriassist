@extends('layouts.app')

@push('scripts')
<script>
    window.__AGRI_CONFIG.detectTranslations = {
        analysisFailed: @json(__('Analysis failed. Please try again.'))
    };

    window.addEventListener('agriassist-locale-changed', (e) => {
        const lang = e.detail.locale;
        const trans = window.__AGRI_CONFIG.translations[lang];

        window.__AGRI_CONFIG.detectTranslations = {
            analysisFailed: trans['Analysis failed. Please try again.'] || 'Analysis failed. Please try again.'
        };
    });
</script>
@vite(['resources/css/pages/detect.css', 'resources/js/pages/detect.js'])
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-20 reveal pb-32 sm:pb-20">
    <div class="text-center mb-12 sm:mb-16 space-y-4">
        <div
            class="inline-flex items-center justify-center space-x-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 px-4 py-1.5 rounded-full text-xs font-black tracking-widest uppercase mb-4 shadow-sm border border-emerald-200 dark:border-emerald-800">
            <i data-lucide="leaf" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span data-t-key="Field Analysis">{{ __('Field Analysis') }}</span>
        </div>
        <h2 class="text-5xl md:text-6xl font-black tracking-tighter text-emerald-950 dark:text-white"
            data-t-key="Crop Disease Scanner">{{ __('Crop Disease Scanner') }}</h2>
        <p class="text-emerald-700/80 dark:text-emerald-300/70 max-w-lg mx-auto leading-relaxed text-lg sm:text-xl font-medium"
            data-t-key="Upload clear photos of the affected plant or leaves. Select up to 5 images for a highly accurate field diagnosis.">
            {{ __('Upload clear photos of the affected plant or leaves. Select up to 5 images for a highly accurate field diagnosis.') }}
        </p>
    </div>

    @if(session('error'))
    <div
        class="mb-10 p-5 bg-red-50 dark:bg-[#2a0e0e] border-l-4 border-red-500 text-red-800 dark:text-red-400 rounded-r-2xl flex items-center space-x-4 shadow-sm animate-bounce">
        <i data-lucide="alert-triangle" class="w-6 h-6 shrink-0"></i>
        <span class="font-bold text-lg">{{ session('error') }}</span>
    </div>
    @endif

    @guest
    <div class="mb-10 p-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800/50 rounded-3xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl reveal">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center text-amber-950 shadow-lg shrink-0">
                <i data-lucide="user-circle" class="w-7 h-7"></i>
            </div>
            <div>
                <h4 class="font-black text-emerald-950 dark:text-white leading-tight" data-t-key="Guest Mode Active">{{ __('Guest Mode Active') }}</h4>
                <p class="text-emerald-800/70 dark:text-emerald-400/70 font-bold text-xs uppercase tracking-widest mt-1" data-t-key="Sign in to save your diagnostic history">{{ __('Sign in to save your diagnostic history') }}</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="px-6 py-3 bg-amber-400 hover:bg-amber-500 text-amber-950 font-black rounded-xl transition-all shadow-md active:scale-95 whitespace-nowrap text-sm" data-t-key="Login to Save">{{ __('Login to Save') }}</a>
    </div>
    @endguest

    <div x-data="uploadManager()" class="relative" x-cloak x-init="init()">
        <form action="{{ route('analyze') }}" method="POST" enctype="multipart/form-data"
            @submit="handleSubmit($event)">
            @csrf
            <!-- The Real Input (Hidden) -->
            <input type="file" name="images[]" id="actual-input" class="hidden" multiple accept="image/*">

            @auth
                @if($userFarms = auth()->user()->farms)
                    @if($userFarms->count() > 0)
                    <div class="mb-8 p-6 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl shadow-xl reveal">
                        <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-3" data-t-key="Which land is this scan for?">{{ __('Which land is this scan for?') }}</label>
                        <div class="relative">
                            <select name="farm_id" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white appearance-none outline-none focus:border-emerald-500">
                                <option value="" data-t-key="General Scan (No specific land)">{{ __('General Scan (No specific land)') }}</option>
                                @foreach($userFarms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->farm_name }} ({{ $farm->district }})</option>
                                @endforeach
                            </select>
                            <i data-lucide="map-pin" class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-600/40"></i>
                        </div>
                        <p class="text-[10px] text-emerald-600/60 mt-3 font-bold uppercase tracking-tighter italic">
                            <i data-lucide="sparkles" class="w-3 h-3 inline mr-1"></i>
                            <span data-t-key="AI will use your land's soil and location data for higher accuracy.">{{ __("AI will use your land's soil and location data for higher accuracy.") }}</span>
                        </p>
                    </div>
                    @endif
                @endif
            @endauth

            <div @click="$refs.tempInput.click()" @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)"
                :class="{ 'border-amber-500 bg-amber-50/20 dark:bg-[#1a1305] scale-[1.01] shadow-2xl': isDragging, 'border-emerald-200 dark:border-emerald-800/80': !isDragging }"
                class="relative group cursor-pointer border-[3px] border-dashed rounded-[2.5rem] sm:rounded-[3.5rem] p-8 sm:p-14 text-center transition-all duration-300 bg-white/60 dark:bg-[#081811] hover:border-emerald-500 dark:hover:border-emerald-500 shadow-xl hover:shadow-2xl hover:bg-white dark:hover:bg-[#0a1e15]">

                <!-- Temporary Input for picking files -->
                <input type="file" x-ref="tempInput" class="hidden" multiple accept="image/*"
                    @change="handleFileSelect($event)">

                <!-- Preview Grid -->
                <template x-if="previews.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-5 mb-8">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div
                                class="relative group/item rounded-[1.5rem] overflow-hidden aspect-square border-4 border-white dark:border-[#0a1e15] shadow-lg transform transition duration-300 hover:-translate-y-2 hover:shadow-2xl hover:border-amber-400">
                                <img :src="preview" class="w-full h-full object-cover bg-emerald-50 dark:bg-[#06120c]">
                                <button type="button" @click.stop="removeImage(index)"
                                    class="absolute top-3 right-3 bg-red-600 hover:bg-red-500 text-white rounded-full p-2 opacity-0 group-hover/item:opacity-100 transition-all duration-200 shadow-xl active:scale-90 touch-manipulation z-20">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent pt-10 pb-3 text-xs text-white font-black uppercase tracking-widest text-center shadow-[inset_0_-10px_20px_rgba(0,0,0,0.5)] z-10">
                                    <span data-t-key="Photo">{{ __('Photo') }}</span> <span x-text="index + 1"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Add More Button -->
                        <template x-if="previews.length < 5">
                            <div
                                class="flex flex-col items-center justify-center border-[3px] border-dashed border-emerald-200 dark:border-emerald-800/80 rounded-[1.5rem] aspect-square transition-all duration-300 text-emerald-600/60 dark:text-emerald-400/50 hover:text-emerald-800 dark:hover:text-emerald-200 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:border-emerald-400 dark:hover:border-emerald-600 active:scale-95 touch-manipulation">
                                <i data-lucide="camera"
                                    class="w-8 h-8 mb-2 opacity-80 group-hover:scale-110 transition-transform"></i>
                                <span class="text-xs font-black uppercase tracking-wider" data-t-key="Add Photo">{{
                                    __('Add Photo') }}</span>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Initial/Empty Prompt -->
                <div x-show="previews.length === 0" class="space-y-6 sm:space-y-8 py-4 sm:py-8">
                    <div
                        class="w-28 h-28 bg-gradient-to-br from-emerald-600 to-emerald-800 dark:from-emerald-500 dark:to-emerald-700 rounded-full flex items-center justify-center mx-auto text-white shadow-[0_0_40px_rgba(16,185,129,0.2)] dark:shadow-[0_0_40px_rgba(16,185,129,0.1)] group-hover:scale-110 group-hover:shadow-[0_0_60px_rgba(16,185,129,0.3)] transition-all duration-500 ease-out border-4 border-white dark:border-[#0a1e15]">
                        <i data-lucide="scan-line" class="w-12 h-12"></i>
                    </div>
                    <div class="space-y-3">
                        <p class="text-3xl font-black text-emerald-950 dark:text-white tracking-tight"
                            data-t-key="Tap to add photos">{{ __('Tap to add photos') }}</p>
                        <p class="text-emerald-700/80 dark:text-emerald-400/80 font-semibold text-lg max-w-sm mx-auto"
                            data-t-key="Get clearer results by adding 2-5 photos from different angles">
                            {{ __('Get clearer results by adding 2-5 photos from different angles') }}</p>
                    </div>
                </div>

                <!-- Analysis Overlay -->
                <div x-show="analyzing"
                    class="absolute inset-0 bg-[#081811]/90 rounded-[2.5rem] sm:rounded-[3.5rem] flex items-center justify-center backdrop-blur-md z-40 transition-opacity duration-500">
                    <div class="text-center text-white space-y-8 w-full max-w-md px-6">
                        <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                            <!-- Outer pulsing rings -->
                            <div
                                class="absolute inset-0 border-4 border-emerald-500 rounded-full animate-[ping_2s_cubic-bezier(0,0,0.2,1)_infinite] opacity-30">
                            </div>
                            <div
                                class="absolute inset-2 border-4 border-amber-400 rounded-full animate-[ping_2s_cubic-bezier(0,0,0.2,1)_infinite] opacity-60 delay-300">
                            </div>

                            <div
                                class="bg-emerald-600 p-6 rounded-full shadow-[0_0_60px_rgba(16,185,129,0.8)] z-10 relative overflow-hidden border-2 border-emerald-400">
                                <i data-lucide="microscope" class="w-12 h-12 text-white animate-pulse"></i>
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-transparent via-white/30 to-transparent -translate-y-full animate-[shimmer_1.5s_infinite]">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-3xl font-black tracking-tight text-white mb-2"
                                data-t-key="Analyzing Crops...">{{ __('Analyzing Crops...') }}
                            </p>
                            <p
                                class="text-emerald-300 font-bold uppercase tracking-widest text-sm flex items-center justify-center space-x-2">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span data-t-key="Running field diagnostics">{{ __('Running field diagnostics')
                                    }}</span>
                            </p>
                        </div>
                        <!-- Progress bar -->
                        <div class="h-2 w-full bg-[#0a1e15] rounded-full overflow-hidden border border-emerald-900/50">
                            <div
                                class="h-full bg-gradient-to-r from-amber-400 to-emerald-400 rounded-full animate-[indeterminate_1.5s_infinite] w-1/2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar (Appears below image box) -->
            <div x-show="!analyzing && !resultHtml && previews.length > 0" class="relative mt-8 sm:mt-12 z-40">
                <div class="max-w-4xl mx-auto">
                    <button type="submit"
                        class="w-full py-5 sm:py-6 lg:py-7 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-[1.5rem] sm:rounded-[2rem] font-black shadow-2xl shadow-emerald-700/40 hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center justify-center space-x-4 text-xl sm:text-2xl tracking-tight border-b-4 border-emerald-900 dark:border-emerald-800">
                        <i data-lucide="zap" class="w-7 h-7 sm:w-8 sm:h-8 text-amber-300"></i>
                        <span data-t-key="Scan & Diagnose">{{ __('Scan & Diagnose') }}</span>
                    </button>
                </div>
            </div>

            <!-- Result Container -->
            <div id="analysis-result-container" x-show="resultHtml" x-html="resultHtml"
                class="mt-8 sm:mt-16 bg-transparent w-full transition-all duration-700 origin-top"
                x-transition:enter="ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-12 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-cloak></div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="mt-16 sm:mt-24 grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 max-w-3xl mx-auto">
        <div class="p-6 bg-amber-50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-900/30">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white">
                    <i data-lucide="sun" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-black text-amber-900 dark:text-amber-400" data-t-key="Avoid Shadows">{{
                    __('Avoid Shadows') }}</h3>
            </div>
            <p class="text-amber-800/80 dark:text-amber-400/60 font-semibold leading-relaxed"
                data-t-key="Ensure photos are well-lit. Avoid strong shadows across the leaves for the highest diagnostic confidence.">
                {{ __('Ensure photos are well-lit. Avoid strong shadows across the leaves for the highest diagnostic confidence.') }}
            </p>
        </div>

        <div
            class="p-6 bg-emerald-50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100 dark:border-emerald-900/30">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white">
                    <i data-lucide="maximize" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-black text-emerald-900 dark:text-emerald-400" data-t-key="Capture Details">{{
                    __('Capture Details') }}</h3>
            </div>
            <p class="text-emerald-800/80 dark:text-emerald-400/60 font-semibold leading-relaxed"
                data-t-key="Get close enough so the affected spots fill the majority of the photo. Blurry photos reduce accuracy.">
                {{ __('Get close enough so the affected spots fill the majority of the photo. Blurry photos reduce accuracy.') }}
            </p>
        </div>
    </div>
</div>

@endsection