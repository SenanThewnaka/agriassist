@extends('layouts.app')

@section('content')
{{-- Tailwind JIT safelist: classes applied only via JavaScript; must appear in a template to be included in the CSS
bundle --}}
<div
    class="hidden dark:bg-[#0A1A12] dark:border-emerald-900 dark:text-emerald-800 dark:text-emerald-400 dark:text-emerald-100 dark:text-emerald-500/40 dark:bg-[#0d2018] dark:border-emerald-800 dark:text-emerald-100">
</div>

<div
    class="min-h-screen bg-[#FDFCF9] dark:bg-[#050C08] py-12 px-4 sm:px-6 lg:px-8 border-t border-emerald-100/50 dark:border-emerald-900/30">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16 space-y-4">
            <div
                class="inline-flex items-center px-4 py-2 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800 animate-fade-in">
                <span class="relative flex h-3 w-3 mr-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-400">{{
                    __('AI Sustainable Farming') }}</span>
            </div>
            <h1 class="text-6xl sm:text-7xl font-black text-emerald-950 dark:text-white tracking-tighter leading-none" data-t-key="Smart Farm Wizard">
                {{ __('Smart Farm') }} <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">{{ __('Wizard')
                    }}</span>
            </h1>
            <p class="text-xl font-bold text-emerald-800/60 dark:text-emerald-400/60 max-w-2xl mx-auto leading-relaxed" data-t-key="Precision agriculture powered by AI. Detect your soil, get optimal crop suggestions, and generate your roadmap.">
                {{ __('Precision agriculture powered by AI. Detect your soil, get optimal crop suggestions, and generate your roadmap.') }}
            </p>
        </div>

        <!-- Wizard Progress -->
        <div class="relative mb-20 px-8">
            <div
                class="absolute top-1/2 left-0 w-full h-1 bg-emerald-100 dark:bg-emerald-900/50 -translate-y-1/2 rounded-full">
            </div>
            <div id="progressBar"
                class="absolute top-1/2 left-0 w-0 h-1 bg-gradient-to-r from-emerald-600 to-teal-400 -translate-y-1/2 rounded-full transition-all duration-700 ease-out z-10">
            </div>

            <div class="relative flex justify-between">
                @foreach(['Soil Type', 'Crop Selection', 'Cultivation Roadmap'] as $index => $step)
                <div class="flex flex-col items-center group">
                    <div id="step-dot-{{ $index + 1 }}"
                        class="step-dot-base w-14 h-14 rounded-2xl border-4 border-emerald-100 dark:border-emerald-900 flex items-center justify-center text-xl font-black text-emerald-200 dark:text-emerald-800 transition-all duration-500 z-20 group-hover:scale-110">
                        {{ $index + 1 }}
                    </div>
                    <span id="step-label-{{ $index + 1 }}" data-t-key="{{ $step }}"
                        class="mt-4 text-xs font-black uppercase tracking-widest text-emerald-900/40 dark:text-emerald-500/40 transition-colors duration-300">{{ __($step) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Wizard Steps Container -->
        <div class="relative">
            <!-- Step 1: Soil Detection -->
            <div id="step1" class="wizard-step space-y-10 animate-slide-up">
                <div class="grid md:grid-cols-2 gap-8">
                    <div
                        class="p-10 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[3rem] shadow-xl shadow-emerald-950/5 relative overflow-hidden group hover:border-emerald-500 transition-all duration-500">
                        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i data-lucide="map-pin" class="w-32 h-32 text-emerald-600"></i>
                        </div>
                        <h3 class="text-3xl font-black text-emerald-950 dark:text-white mb-4" data-t-key="Precision Detection">{{ __('Precision Detection') }}</h3>
                        <p class="text-emerald-800/70 dark:text-emerald-400/70 font-bold mb-6 leading-relaxed" data-t-key="Use AI-powered geolocation to automatically identify your soil's composition and chemical properties.">{{
                            __('Use AI-powered geolocation to automatically identify your soil\'s composition and chemical properties.') }}</p>

                        {{-- Inline error banner (hidden by default) --}}
                        <div id="geoErrorBanner"
                            class="hidden mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl text-sm text-amber-800 dark:text-amber-300 font-semibold flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                            <span id="geoErrorText"></span>
                        </div>

                        <button id="detectLocationBtn"
                            class="group relative inline-flex items-center px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black text-lg hover:bg-emerald-700 transform active:scale-95 transition-all shadow-lg shadow-emerald-600/20 mb-6">
                            <i data-lucide="crosshair" class="w-6 h-6 mr-3 group-hover:animate-spin-slow"></i>
                            <span data-t-key="Detect My Soil">{{ __('Detect My Soil') }}</span>
                        </button>

                        {{-- District picker fallback --}}
                        <div class="border-t border-emerald-100 dark:border-emerald-900 pt-5">
                            <p data-t-key="Or select your district"
                                class="text-xs font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-widest mb-3">
                                {{ __('Or select your district') }}</p>
                            <div class="relative">
                                <select id="districtPicker"
                                    class="district-select w-full appearance-none px-4 py-3 pr-10 rounded-2xl border-2 border-emerald-100 dark:border-emerald-800 font-bold focus:outline-none focus:border-emerald-500 transition-all cursor-pointer">
                                    <option value="" class="bg-white dark:bg-[#0d2018]" data-t-key="Pick district">{{ __('-- Pick district --') }}
                                    </option>
                                    @foreach(['Colombo','Gampaha','Kalutara','Kandy','Matale','Nuwara
                                    Eliya','Galle','Matara','Hambantota','Jaffna','Kilinochchi','Mannar','Vavuniya','Mullaitivu','Batticaloa','Ampara','Trincomalee','Kurunegala','Puttalam','Anuradhapura','Polonnaruwa','Badulla','Monaragala','Ratnapura','Kegalle']
                                    as $d)
                                    <option value="{{ $d }}" class="bg-white dark:bg-[#0d2018]">{{ $d }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-emerald-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center space-y-6">
                        <p data-t-key="Or Manual Selection"
                            class="text-sm font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-[0.3em]">
                            {{ __('Or Manual Selection') }}</p>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach([
                            'Reddish Brown Earths' => 'Reddish Brown Earths',
                            'Low Humic Gley' => 'Low Humic Gley (Paddy)',
                            'Non-Calcic Brown' => 'Non-Calcic Brown',
                            'Red-Yellow Podzolic' => 'Red-Yellow Podzolic',
                            'Red-Yellow Latosols' => 'Red-Yellow Latosols',
                            'Calcic Latosols' => 'Calcic Latosols',
                            'Alluvial' => 'Alluvial (River/Paddy)',
                            'Solodized Solonetz' => 'Solodized Solonetz',
                            'Regosols' => 'Regosols (Sandy)',
                            'Grumusols' => 'Grumusols (Clay)',
                            'Immature Brown Loams' => 'Immature Brown Loams',
                            'Bog & Half-Bog' => 'Bog & Half-Bog',
                            'Reddish Brown Latosolic' => 'Reddish Brown Latosolic',
                            'Rendzina' => 'Rendzina',
                            'Coastal Sands' => 'Coastal Sands',
                            'Sandy Loam' => 'Sandy Loam',
                            'Clay Loam' => 'Clay Loam',
                            ] as $soilKey => $soilLabel)
                            <button type="button"
                                class="soil-btn p-4 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl text-left hover:border-emerald-500 transition-all relative group"
                                data-soil="{{ $soilKey }}">
                                <span class="block text-sm sm:text-base font-black text-emerald-950 dark:text-white mb-1" data-t-key="{{ $soilKey }}">{{
                                    __($soilLabel) }}</span>
                                <span
                                    class="soil-check-mark hidden absolute top-4 right-4 w-5 h-5 bg-emerald-500 rounded-full items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white"></i>
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-8 border-t border-emerald-100 dark:border-emerald-900">
                    <button id="step1NextBtn" disabled data-t-key="Next: Identify Crops"
                        class="px-10 py-4 bg-emerald-950 dark:bg-emerald-500 text-white font-black rounded-2xl disabled:opacity-30 disabled:cursor-not-allowed hover:px-12 transition-all duration-300">
                        {{ __('Next: Identify Crops') }}
                    </button>
                </div>
            </div>

            <!-- Step 2: Recommendations -->
            <div id="step2" class="wizard-step hidden space-y-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-2">
                        <h3 class="text-4xl font-black text-emerald-950 dark:text-white" data-t-key="Smart Predictions">{{ __('Smart Predictions') }}
                        </h3>
                        <p id="step2Subtitle" class="text-lg font-bold text-emerald-600" data-t-key="Analyzing your environment...">{{ __('Analyzing your environment...') }}
                        </p>
                    </div>

                    <div
                        class="flex items-center space-x-4 p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                        <select id="manualCropId"
                            class="bg-white dark:bg-emerald-950 border-none text-emerald-900 dark:text-white font-black focus:ring-2 focus:ring-emerald-500 rounded-xl px-2 py-1 appearance-none cursor-pointer">
                            <option value="" class="bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white" data-t-key="Choose Different Crop">
                                {{ __('Choose Different Crop') }}</option>
                            @foreach(\App\Models\Crop::all() as $crop)
                            <option value="{{ $crop->id }}"
                                data-name="{{ $crop->name }}"
                                data-name-si="{{ $crop->name_si }}"
                                data-name-ta="{{ $crop->name_ta }}"
                                class="bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white">{{ $crop->name }}
                            </option>
                            @endforeach
                            <option value="other" class="bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white" data-t-key="Other (Custom Crop)">
                                {{ __('Other (Custom Crop)') }}</option>
                        </select>

                        <div id="customCropContainer" class="hidden">
                            <input type="text" id="customCropName" placeholder="{{ __('e.g. Dragon Fruit') }}"
                                class="bg-white dark:bg-emerald-950 border-2 border-emerald-100 dark:border-emerald-800 rounded-xl px-4 py-2 text-sm font-bold text-emerald-950 dark:text-white focus:outline-none focus:border-emerald-500 transition-all w-40">
                        </div>

                        <select id="manualVarietyId" disabled
                            class="bg-white dark:bg-emerald-950 border-none text-emerald-900 dark:text-white font-black focus:ring-2 focus:ring-emerald-500 rounded-xl px-2 py-1 appearance-none cursor-pointer min-w-[150px]">
                            <option value="" class="bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white" data-t-key="Seed Type">-- {{
                                __('Seed Type') }} --</option>
                        </select>

                        <div id="customVarietyContainer" class="hidden">
                            <input type="text" id="customVarietyName" placeholder="{{ __('e.g. Local Hybrid') }}"
                                class="bg-white dark:bg-emerald-950 border-2 border-emerald-100 dark:border-emerald-800 rounded-xl px-4 py-2 text-sm font-bold text-emerald-950 dark:text-white focus:outline-none focus:border-emerald-500 transition-all w-40">
                        </div>
                        <button id="manualProceedBtn" disabled
                            class="p-2 bg-emerald-600 text-white rounded-xl active:scale-90 transition-transform disabled:opacity-50">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div id="suggestionsLoading" class="min-h-[400px] flex flex-col items-center justify-center space-y-6">
                    <div
                        class="w-16 h-16 border-4 border-emerald-100 dark:border-emerald-900 border-t-emerald-600 rounded-full animate-spin">
                    </div>
                    <p class="text-emerald-800/60 dark:text-emerald-400/60 font-black animate-pulse" data-t-key="Running AI simulations...">{{ __('Running AI simulations...') }}</p>
                </div>

                <div id="suggestionsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                    <!-- Cards will be injected via JS -->
                </div>

                <div class="flex justify-between pt-8 border-t border-emerald-100 dark:border-emerald-900">
                    <button
                        class="px-8 py-4 text-emerald-900 dark:text-emerald-400 font-black hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-2xl transition-all"
                        onclick="showStep(1)" data-t-key="Back">{{ __('Back') }}</button>
                </div>
            </div>

            <!-- Step 3: Roadmap -->
            <div id="step3" class="wizard-step hidden space-y-10">
                <div id="roadmapConfig"
                    class="max-w-2xl mx-auto p-6 md:p-10 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[3rem] text-center space-y-8">
                    <div
                        class="w-16 h-16 bg-emerald-100 dark:bg-emerald-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar" class="w-8 h-8 text-emerald-600"></i>
                    </div>
                    <h3 class="text-3xl font-black text-emerald-950 dark:text-white" data-t-key="Planning Your Harvest">{{ __('Planning Your Harvest') }}
                    </h3>

                    <div class="space-y-4">
                        <p class="text-[10px] font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-widest text-center" data-t-key="Extent of Land">
                            {{ __('Extent of Land') }}</p>
                        <div class="flex">
                            <input type="number" id="landSize" step="0.1" value="1.0" class="w-2/3 px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-r-0 border-emerald-100 dark:border-emerald-900 rounded-l-2xl font-bold text-emerald-950 dark:text-white outline-none focus:border-emerald-500 transition-all">
                            <select id="landUnit" class="w-1/3 px-4 py-4 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-r-2xl font-black text-[10px] uppercase tracking-tighter text-emerald-700 dark:text-emerald-400 appearance-none outline-none focus:border-emerald-500">
                                <option value="Acres">{{ __('Acres') }}</option>
                                <option value="Hectares">{{ __('Hectares') }}</option>
                                <option value="Perches">{{ __('Perches') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-widest text-center" data-t-key="Choose Planning Method">
                            {{ __('Choose Planning Method') }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button id="methodManualBtn"
                                class="p-5 border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 rounded-3xl text-left transition-all group flex flex-col justify-between min-h-[110px] h-full">
                                <div class="flex items-start justify-between w-full mb-3 gap-3">
                                    <span class="block text-base md:text-lg font-black text-emerald-950 dark:text-white leading-tight flex-1" data-t-key="Manual Selection">{{ __('Manual Selection') }}</span>
                                    <div class="w-5 h-5 rounded-full border-4 border-emerald-500 bg-emerald-500 shrink-0 mt-1"></div>
                                </div>
                                <p class="text-[11px] font-bold text-emerald-800/60 dark:text-emerald-400/60 leading-snug" data-t-key="Select your own planting date">{{ __('Select your own planting date') }}</p>
                            </button>

                            <button id="methodAiBtn"
                                class="p-5 border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl text-left hover:border-emerald-300 transition-all group relative overflow-hidden flex flex-col justify-between min-h-[110px] h-full">
                                <div class="flex items-start justify-between w-full mb-3 gap-3">
                                    <span class="block text-base md:text-lg font-black text-emerald-950 dark:text-white leading-tight flex-1" data-t-key="AI Recommended">{{ __('AI Recommended') }}</span>
                                    <div class="w-5 h-5 rounded-full border-2 border-emerald-200 dark:border-emerald-800 shrink-0 mt-1"></div>
                                </div>
                                <p class="text-[11px] font-bold text-emerald-800/60 dark:text-emerald-400/60 leading-snug" data-t-key="Let AI find the best date based on 14-day weather forecast">{{ __('Let AI find the best date based on 14-day weather forecast') }}</p>
                            </button>
                        </div>
                    </div>

                    <div class="min-h-[180px] flex flex-col justify-center">
                        <div id="manualDateInput" class="space-y-4">
                            <p class="text-sm text-emerald-800/70 dark:text-emerald-400/70 font-bold leading-relaxed" data-t-key="When do you plan to start planting? We'll tailor each growth stage to the local seasonal shifts.">{{ __('When do you plan to start planting? We\'ll tailor each growth stage to the local seasonal shifts.') }}</p>
                            <input type="date" id="roadmapDate"
                                class="w-full p-5 bg-emerald-50 dark:bg-emerald-900/30 border-2 border-emerald-100 dark:border-emerald-800 rounded-2xl text-xl font-black text-emerald-950 dark:text-white outline-none focus:border-emerald-500 transition-all text-center"
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div id="aiDateRecommendation" class="hidden space-y-4 p-6 bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-800 rounded-3xl animate-in fade-in zoom-in duration-500">
                            <div id="aiDateLoading" class="flex flex-col items-center py-4 space-y-4">
                                <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
                                <p class="text-sm font-black text-emerald-800/60" data-t-key="Analyzing weather patterns...">{{ __('Analyzing weather patterns...') }}</p>
                            </div>
                            <div id="aiDateResult" class="hidden space-y-4">
                                <div>
                                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1" data-t-key="Recommended Date">{{ __('Recommended Date') }}</p>
                                    <div id="recDateDisplay" class="text-3xl font-black text-emerald-950 dark:text-white">--</div>
                                    <input type="hidden" id="recDateValue">
                                </div>
                                <div class="flex items-center justify-center gap-3 p-3 bg-white dark:bg-[#06120c] rounded-2xl border border-emerald-100 dark:border-emerald-800 mx-auto max-w-xs">
                                    <i id="recIcon" data-lucide="sun" class="w-5 h-5 text-amber-500"></i>
                                    <span id="recReason" class="text-xs font-bold text-emerald-800/80 dark:text-emerald-400/80" data-t-key="Best weather conditions for planting">{{ __('Best weather conditions for planting') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button id="generateRoadmapBtn" data-t-key="Generate Cultivation Roadmap"
                            class="w-full py-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-3xl font-black text-xl shadow-xl shadow-emerald-600/30 transform active:scale-[0.98] transition-all">
                            {{ __('Generate Cultivation Roadmap') }}
                        </button>
                        <button id="backToStep2FromConfig" data-t-key="Pick a different crop"
                            class="w-full py-2 text-emerald-700 dark:text-emerald-500 font-bold hover:underline">{{ __('Pick a different crop') }}</button>
                    </div>
                </div>

                <div id="roadmapLoading"
                    class="min-h-[400px] flex flex-col items-center justify-center space-y-6 hidden">
                    <div class="w-20 h-20 relative">
                        <div class="absolute inset-0 border-4 border-emerald-100 rounded-full"></div>
                        <div
                            class="absolute inset-0 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin">
                        </div>
                    </div>
                    <p class="text-emerald-800/60 font-black" data-t-key="Generating your personalized roadmap...">{{ __('Generating your personalized roadmap...') }}</p>
                </div>

                <!-- Final Result -->
                <div id="resultCard" class="hidden space-y-8">
                    <div class="grid lg:grid-cols-4 gap-6">
                        <div
                            class="lg:col-span-3 p-10 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-800 rounded-[3rem] shadow-xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-8">
                                <i data-lucide="sprout" class="w-24 h-24 text-emerald-500 opacity-10"></i>
                            </div>
                            <div class="relative z-10">
                                <h2 id="resCropVariety"
                                    class="text-5xl font-black text-emerald-950 dark:text-white mb-8" data-t-key="Crop Name">{{ __('Crop Name')
                                    }}</h2>
                                <div id="roadmapContainer" class="space-y-0 relative">
                                    <!-- Timeline will be injected here -->
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Resource Estimator Card -->
                            <div class="p-8 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2.5rem] shadow-xl relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                                    <i data-lucide="calculator" class="w-12 h-12 text-emerald-600"></i>
                                </div>
                                <h4 class="text-emerald-900 dark:text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em] mb-6" data-t-key="Resource Estimator">{{ __('Resource Estimator') }}</h4>
                                
                                <div class="space-y-5">
                                    <div class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                                        <div class="flex items-center space-x-3">
                                            <i data-lucide="package" class="w-4 h-4 text-emerald-600"></i>
                                            <span class="text-[10px] font-bold text-emerald-800/60 dark:text-emerald-400/60 uppercase" data-t-key="Seeds">{{ __('Seeds') }}</span>
                                        </div>
                                        <span class="font-black text-emerald-950 dark:text-white text-sm"><span id="estSeeds">0</span> kg</span>
                                    </div>

                                    <div class="space-y-3">
                                        <p class="text-[9px] font-black text-emerald-900/30 dark:text-emerald-500/30 uppercase tracking-widest" data-t-key="Fertilizer (Total)">{{ __('Fertilizer (Total)') }}</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="text-center p-2 bg-white dark:bg-[#0a1e15] border border-emerald-100 dark:border-emerald-900 rounded-xl">
                                                <p class="text-[8px] font-bold text-emerald-600 uppercase">Urea</p>
                                                <p class="font-black text-emerald-950 dark:text-white text-[10px]"><span id="estUrea">0</span>kg</p>
                                            </div>
                                            <div class="text-center p-2 bg-white dark:bg-[#0a1e15] border border-emerald-100 dark:border-emerald-900 rounded-xl">
                                                <p class="text-[8px] font-bold text-emerald-600 uppercase">TSP</p>
                                                <p class="font-black text-emerald-950 dark:text-white text-[10px]"><span id="estTsp">0</span>kg</p>
                                            </div>
                                            <div class="text-center p-2 bg-white dark:bg-[#0a1e15] border border-emerald-100 dark:border-emerald-900 rounded-xl">
                                                <p class="text-[8px] font-bold text-emerald-600 uppercase">MOP</p>
                                                <p class="font-black text-emerald-950 dark:text-white text-[10px]"><span id="estMop">0</span>kg</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-emerald-50 dark:border-emerald-900/50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold text-emerald-800/60 dark:text-emerald-400/60 uppercase" data-t-key="Est. Yield">{{ __('Est. Yield') }}</span>
                                            <span class="font-black text-emerald-950 dark:text-white text-sm"><span id="estYield">0</span> kg</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-amber-600 uppercase" data-t-key="Market Value">{{ __('Market Value') }}</span>
                                            <span class="font-black text-amber-500 text-sm">Rs. <span id="estRevenue">0</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8 bg-emerald-950 text-white rounded-[2.5rem] shadow-2xl">
                                <h4 class="text-emerald-500 text-xs font-black uppercase tracking-widest mb-6" data-t-key="Quick Overview">{{
                                    __('Quick Overview') }}</h4>
                                <div class="space-y-6">
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1" data-t-key="Duration">{{
                                            __('Duration') }}
                                        </div>
                                        <div class="text-2xl font-black"><span id="resDuration">0</span> <span data-t-key="Days">{{ __('Days') }}</span></div>
                                    </div>
                                    <hr class="border-emerald-900">
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1" data-t-key="Start Date">{{
                                            __('Start Date') }}</div>
                                        <div id="resPlantDate" class="text-lg font-black">--</div>
                                    </div>
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1" data-t-key="Harvest Estimate">{{
                                            __('Harvest Estimate') }}</div>
                                        <div id="resHarvestDate" class="text-lg font-black">--</div>
                                    </div>
                                </div>
                            </div>

                            <button onclick="window.print()"
                                class="w-full py-6 border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl font-black text-emerald-950 dark:text-emerald-400 flex items-center justify-center hover:bg-white dark:hover:bg-emerald-900/30 transition-all">
                                <i data-lucide="printer" class="w-5 h-5 mr-3"></i> <span data-t-key="Save as PDF">{{ __('Save as PDF') }}</span>
                            </button>

                            @auth
                                @if($userFarms->count() > 0)
                                <div class="relative w-full" id="savePlanContainer">
                                    <select id="saveFarmId" class="w-full mb-3 px-4 py-3 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white appearance-none outline-none focus:border-emerald-500">
                                        <option value="" disabled selected>{{ __('Select land to save...') }}</option>
                                        @foreach($userFarms as $farm)
                                            <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                        @endforeach
                                    </select>
                                    <button id="savePlanBtn"
                                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-lg shadow-xl shadow-emerald-600/30 transform active:scale-[0.98] transition-all flex items-center justify-center disabled:opacity-50">
                                        <i data-lucide="save" class="w-5 h-5 mr-3"></i> <span data-t-key="Save to Profile">{{ __('Save to Profile') }}</span>
                                    </button>
                                </div>
                                @else
                                <a href="{{ route('profile.show') }}"
                                    class="w-full py-4 border-2 border-amber-200 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-2xl font-black flex items-center justify-center hover:bg-amber-100 transition-all">
                                    <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> {{ __('Add Land to Save') }}
                                </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                    class="w-full py-4 border-2 border-emerald-600 text-emerald-700 dark:text-emerald-400 rounded-2xl font-black flex items-center justify-center hover:bg-emerald-50 transition-all">
                                    <i data-lucide="log-in" class="w-5 h-5 mr-2"></i> {{ __('Login to Save') }}
                                </a>
                            @endauth

                            <button id="restartWizardBtn" data-t-key="Start Over"
                                class="w-full py-4 text-emerald-600 font-bold hover:underline mt-4">{{ __('Start Over')
                                }}</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    window.__PLANNER_CONFIG = {
        csrf: "{{ csrf_token() }}",
        locale: "{{ app()->getLocale() }}",
        translations: {
            en: @json(json_decode(file_get_contents(lang_path('en.json')), true)),
            si: @json(json_decode(file_get_contents(lang_path('si.json')), true)),
            ta: @json(json_decode(file_get_contents(lang_path('ta.json')), true))
        }
    };
</script>
@vite(['resources/css/planner.css', 'resources/js/planner.js'])
@endsection