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
                <span class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-400">AI
                    Sustainable Farming</span>
            </div>
            <h1 class="text-6xl sm:text-7xl font-black text-emerald-950 dark:text-white tracking-tighter leading-none">
                Smart Farm <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Wizard</span>
            </h1>
            <p class="text-xl font-bold text-emerald-800/60 dark:text-emerald-400/60 max-w-2xl mx-auto leading-relaxed">
                Precision agriculture powered by AI. Detect your soil, get optimal crop suggestions, and generate your
                roadmap.
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
                    <span id="step-label-{{ $index + 1 }}"
                        class="mt-4 text-xs font-black uppercase tracking-widest text-emerald-900/40 dark:text-emerald-500/40 transition-colors duration-300">{{
                        $step }}</span>
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
                        <h3 class="text-3xl font-black text-emerald-950 dark:text-white mb-4">Precision Detection</h3>
                        <p class="text-emerald-800/70 dark:text-emerald-400/70 font-bold mb-6 leading-relaxed">Use
                            AI-powered geolocation to automatically identify your soil's composition and chemical
                            properties.</p>

                        {{-- Inline error banner (hidden by default) --}}
                        <div id="geoErrorBanner"
                            class="hidden mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl text-sm text-amber-800 dark:text-amber-300 font-semibold flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                            <span id="geoErrorText"></span>
                        </div>

                        <button id="detectLocationBtn"
                            class="group relative inline-flex items-center px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black text-lg hover:bg-emerald-700 transform active:scale-95 transition-all shadow-lg shadow-emerald-600/20 mb-6">
                            <i data-lucide="crosshair" class="w-6 h-6 mr-3 group-hover:animate-spin-slow"></i>
                            Detect My Soil
                        </button>

                        {{-- District picker fallback --}}
                        <div class="border-t border-emerald-100 dark:border-emerald-900 pt-5">
                            <p
                                class="text-xs font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-widest mb-3">
                                Or select your district</p>
                            <div class="relative">
                                <select id="districtPicker"
                                    class="district-select w-full appearance-none px-4 py-3 pr-10 rounded-2xl border-2 border-emerald-100 dark:border-emerald-800 font-bold focus:outline-none focus:border-emerald-500 transition-all cursor-pointer">
                                    <option value="" class="bg-white dark:bg-[#0d2018]">-- Pick district --</option>
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
                        <p
                            class="text-sm font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-[0.3em]">
                            Or Manual Selection</p>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach([
                            'Alluvial' => 'Alluvial',
                            'Red Soil' => 'Red Soil',
                            'Black Soil' => 'Black Soil',
                            'Sandy' => 'Sandy',
                            'Sandy Loam' => 'Sandy Loam',
                            'Lateritic' => 'Lateritic',
                            'Red Yellow Podzolic'=> 'Red Yellow Podzolic',
                            'Reddish Brown Earth'=> 'Reddish Brown Earth',
                            ] as $soilKey => $soilLabel)
                            <button type="button"
                                class="soil-btn p-5 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl text-left hover:border-emerald-500 transition-all relative group"
                                data-soil="{{ $soilKey }}">
                                <span class="block text-lg font-black text-emerald-950 dark:text-white mb-1">{{
                                    $soilLabel }}</span>
                                <span
                                    class="soil-check-mark hidden absolute top-4 right-4 w-6 h-6 bg-emerald-500 rounded-full items-center justify-center">
                                    <i data-lucide="check" class="w-4 h-4 text-white"></i>
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-8 border-t border-emerald-100 dark:border-emerald-900">
                    <button id="step1NextBtn" disabled
                        class="px-10 py-4 bg-emerald-950 dark:bg-emerald-500 text-white font-black rounded-2xl disabled:opacity-30 disabled:cursor-not-allowed hover:px-12 transition-all duration-300">
                        Next: Identify Crops
                    </button>
                </div>
            </div>

            <!-- Step 2: Recommendations -->
            <div id="step2" class="wizard-step hidden space-y-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-2">
                        <h3 class="text-4xl font-black text-emerald-950 dark:text-white">Smart Predictions</h3>
                        <p id="step2Subtitle" class="text-lg font-bold text-emerald-600">Analyzing your environment...
                        </p>
                    </div>

                    <div
                        class="flex items-center space-x-4 p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                        <select id="manualCropId"
                            class="bg-transparent border-none text-emerald-900 dark:text-white font-black focus:ring-0">
                            <option value="">Choose Different Crop</option>
                            @foreach(\App\Models\Crop::all() as $crop)
                            <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                            @endforeach
                        </select>
                        <select id="manualVarietyId" disabled
                            class="bg-transparent border-none text-emerald-900 dark:text-white font-black focus:ring-0 min-w-[150px]">
                            <option value="">-- Seed Type --</option>
                        </select>
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
                    <p class="text-emerald-800/60 dark:text-emerald-400/60 font-black animate-pulse">Running AI
                        simulations...</p>
                </div>

                <div id="suggestionsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                    <!-- Cards will be injected via JS -->
                </div>

                <div class="flex justify-between pt-8 border-t border-emerald-100 dark:border-emerald-900">
                    <button
                        class="px-8 py-4 text-emerald-900 dark:text-emerald-400 font-black hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-2xl transition-all"
                        onclick="showStep(1)">Back</button>
                </div>
            </div>

            <!-- Step 3: Roadmap -->
            <div id="step3" class="wizard-step hidden space-y-10">
                <div id="roadmapConfig"
                    class="max-w-xl mx-auto p-10 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[3rem] text-center space-y-8">
                    <div
                        class="w-20 h-20 bg-emerald-100 dark:bg-emerald-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="calendar" class="w-10 h-10 text-emerald-600"></i>
                    </div>
                    <h3 class="text-3xl font-black text-emerald-950 dark:text-white">Planning Your Harvest</h3>
                    <p class="text-emerald-800/70 dark:text-emerald-400/70 font-bold leading-relaxed">When do you plan
                        to start planting? We'll tailor each growth stage to the local seasonal shifts.</p>

                    <input type="date" id="roadmapDate"
                        class="w-full p-6 bg-emerald-50 dark:bg-emerald-900/30 border-2 border-emerald-100 dark:border-emerald-800 rounded-3xl text-xl font-black text-emerald-950 dark:white outline-none focus:border-emerald-500 transition-all text-center"
                        value="{{ date('Y-m-d') }}">

                    <button id="generateRoadmapBtn"
                        class="w-full py-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-3xl font-black text-xl shadow-xl shadow-emerald-600/30 transform active:scale-[0.98] transition-all">
                        Generate Cultivation Roadmap
                    </button>
                    <button id="backToStep2FromConfig"
                        class="w-full py-4 text-emerald-700 dark:text-emerald-500 font-bold hover:underline">Pick a
                        different crop</button>
                </div>

                <div id="roadmapLoading"
                    class="min-h-[400px] flex flex-col items-center justify-center space-y-6 hidden">
                    <div class="w-20 h-20 relative">
                        <div class="absolute inset-0 border-4 border-emerald-100 rounded-full"></div>
                        <div
                            class="absolute inset-0 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin">
                        </div>
                    </div>
                    <p class="text-emerald-800/60 font-black">Generating your personalized roadmap...</p>
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
                                    class="text-5xl font-black text-emerald-950 dark:text-white mb-8">Crop Name</h2>
                                <div id="roadmapContainer" class="space-y-0 relative">
                                    <!-- Timeline will be injected here -->
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="p-8 bg-emerald-950 text-white rounded-[2.5rem] shadow-2xl">
                                <h4 class="text-emerald-500 text-xs font-black uppercase tracking-widest mb-6">Quick
                                    Overview</h4>
                                <div class="space-y-6">
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1">Duration
                                        </div>
                                        <div class="text-2xl font-black"><span id="resDuration">0</span> Days</div>
                                    </div>
                                    <hr class="border-emerald-900">
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1">Start
                                            Date</div>
                                        <div id="resPlantDate" class="text-lg font-black">--</div>
                                    </div>
                                    <div>
                                        <div class="text-emerald-400/60 text-[10px] font-black uppercase mb-1">Harvest
                                            Estimate</div>
                                        <div id="resHarvestDate" class="text-lg font-black">--</div>
                                    </div>
                                </div>
                            </div>

                            <button onclick="window.print()"
                                class="w-full py-6 border-2 border-emerald-100 dark:border-emerald-900 rounded-3xl font-black text-emerald-950 dark:text-emerald-400 flex items-center justify-center hover:bg-white dark:hover:bg-emerald-900/30 transition-all">
                                <i data-lucide="printer" class="w-5 h-5 mr-3"></i> Save as PDF
                            </button>
                            <button id="restartWizardBtn"
                                class="w-full py-4 text-emerald-600 font-bold hover:underline">Start Over</button>
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
            'Permission Denied': "{{ __('Location permission denied. Please enable it in your browser settings.') }}",
            'Position Unavailable': "{{ __('Location information is unavailable.') }}",
            'Timeout': "{{ __('Geolocation request timed out.') }}",
            'Unknown Error': "{{ __('An unknown geolocation error occurred.') }}",
            'Detecting': "{{ __('Detecting...') }}",
            'Detected': "{{ __('Detected') }}: "
        }
    };
</script>
@vite(['resources/css/planner.css', 'resources/js/planner.js'])
@endsection
