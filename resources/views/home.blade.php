@extends('layouts.app')

@section('content')
<div class="overflow-hidden">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 sm:px-8 py-20 lg:py-32 relative">
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl -z-10">
        </div>
        <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
            <div class="flex-1 text-center lg:text-left space-y-10 reveal origin-bottom">

                <div
                    class="inline-flex items-center space-x-3 px-5 py-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-full text-amber-800 dark:text-amber-400 font-black text-xs uppercase tracking-[0.2em] border-2 border-amber-200 dark:border-amber-800/50 shadow-sm mx-auto lg:mx-0">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    <span>{{ __('Expert Analysis Live') }}</span>
                </div>

                <h1
                    class="text-6xl sm:text-7xl md:text-8xl lg:text-[7rem] font-black leading-[1.05] tracking-tighter text-emerald-950 dark:text-white">
                    {{ __('Defend Your') }} <br>
                    <span class="text-emerald-700 dark:text-emerald-400">{{ __('Harvest.') }}</span>
                </h1>

                <p
                    class="text-xl sm:text-2xl text-emerald-800/80 dark:text-emerald-400/80 max-w-2xl leading-relaxed mx-auto lg:mx-0 font-bold">
                    {{ __('Precision agriculture technology built specifically for the Sri Lankan farmer. Detect crop diseases instantly and secure your yield.') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-5 pt-4 justify-center lg:justify-start">
                    <a href="{{ route('detect') }}"
                        class="group w-full sm:w-auto px-10 py-6 sm:py-7 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-[2rem] font-black shadow-2xl shadow-emerald-700/40 hover:-translate-y-2 hover:shadow-emerald-700/60 transition-all duration-300 flex items-center justify-center space-x-4 border-b-4 border-emerald-900 dark:border-emerald-800 text-xl tracking-tight">
                        <i data-lucide="scan" class="w-6 h-6 text-amber-300"></i>
                        <span>{{ __('Diagnose Crops') }}</span>
                        <i data-lucide="arrow-right" class="w-6 h-6 group-hover:translate-x-2 transition-transform"></i>
                    </a>
                    <a href="#features"
                        class="w-full sm:w-auto px-10 py-6 sm:py-7 bg-white dark:bg-[#081811] border-4 border-emerald-100 dark:border-emerald-900/50 text-emerald-900 dark:text-emerald-200 rounded-[2rem] font-bold hover:border-emerald-300 dark:hover:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-[#0a1e15] shadow-sm hover:-translate-y-1 transition-all flex items-center justify-center space-x-3 text-xl tracking-tight">
                        <i data-lucide="info" class="w-6 h-6 opacity-60"></i>
                        <span>{{ __('Learn More') }}</span>
                    </a>
                </div>
            </div>

            <div class="flex-1 reveal w-full" style="transition-delay: 200ms">
                <div
                    class="relative group p-4 sm:p-6 bg-white dark:bg-[#081811] rounded-[3.5rem] border-4 border-emerald-100 dark:border-emerald-900/50 shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    <img src="{{ asset('images/hero.png') }}" alt="{{ __('Sri Lankan Farmer in Rice Paddy') }}"
                        class="rounded-[2.5rem] w-full h-[400px] lg:h-[600px] object-cover bg-emerald-50">

                    <!-- Floating Accuracy Badge -->
                    <div class="absolute -bottom-6 -left-6 bg-emerald-950 dark:bg-[#06120c] p-6 sm:p-8 rounded-[2rem] border-4 border-emerald-800 text-white shadow-2xl reveal hover:-translate-y-2 transition-transform"
                        style="transition-delay: 600ms">
                        <div class="flex items-center space-x-5">
                            <div
                                class="w-14 h-14 bg-emerald-800 rounded-full flex items-center justify-center text-amber-400 border-2 border-emerald-600">
                                <i data-lucide="target" class="w-7 h-7 animate-pulse"></i>
                            </div>
                            <p class="text-xs font-black uppercase tracking-widest text-emerald-400 mb-1">{{
                                __('Diagnostic Accuracy') }}</p>
                            <p class="text-4xl font-black text-white tracking-tighter">98.4%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Weather Dynamics Section -->
    <section class="bg-[#0a1e15] text-white py-24 sm:py-32 relative overflow-hidden" x-data="weatherApp()"
        x-init="init()">
        <!-- Decorative bg -->
        <div
            class="absolute -top-64 -right-64 bg-emerald-800/20 w-[800px] h-[800px] rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-6 sm:px-8 relative z-10 reveal">
            <!-- Critical Network Alerts -->
            <template x-if="criticalAlert">
                <div
                    class="mb-12 p-6 bg-red-950/80 border-l-8 border-red-600 rounded-r-[2rem] flex items-start space-x-5 shadow-2xl animate-in slide-in-from-top-4">
                    <div class="bg-red-600 p-3 rounded-xl shrink-0">
                        <i data-lucide="siren" class="w-8 h-8 text-white animate-pulse"></i>
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-red-100 tracking-tight" x-text="criticalAlert.title">{{
                            __('Level 4 Alert') }}</h4>
                        <p class="text-red-200 mt-2 font-bold text-lg" x-text="criticalAlert.message"></p>
                    </div>
                </div>
            </template>

            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-8">
                <div class="space-y-4">
                    <div
                        class="inline-flex items-center space-x-2 bg-emerald-900/50 px-4 py-2 rounded-full border border-emerald-800 text-emerald-400 font-black text-xs uppercase tracking-[0.2em]">
                        <i data-lucide="satellite" class="w-4 h-4"></i>
                        <span>{{ __('Advanced Telemetry') }}</span>
                    </div>
                    <h2 class="text-5xl md:text-7xl font-black tracking-tighter text-white leading-none">{{
                        __('Precision.') }}</h2>
                    <p class="text-emerald-200/90 max-w-2xl text-xl font-medium pt-4">{{ __('Predictive 7-day agricultural modeling to optimize your resource deployment and protect against imminent threats.') }}</p>
                </div>

                <!-- Location Badge -->
                <div
                    class="bg-emerald-950 px-6 py-4 rounded-[1.5rem] flex items-center space-x-4 border-2 border-emerald-800 shadow-xl self-start md:self-auto shrink-0">
                    <div class="w-3 h-3 bg-amber-400 rounded-full animate-pulse shadow-[0_0_15px_#fbbf24]"></div>
                    <span class="font-black tracking-wide text-lg text-emerald-100"
                        x-text="locationName || '{{ __('Syncing Coordinates...') }}'"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                <!-- Today's Stats Card -->
                <div
                    class="lg:col-span-8 bg-emerald-950 p-8 sm:p-12 rounded-[3.5rem] border-4 border-emerald-800 shadow-2xl relative">
                    <i data-lucide="thermometer-sun"
                        class="absolute top-8 right-8 w-32 h-32 text-emerald-900 opacity-50"></i>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 sm:gap-12 relative z-10">
                        <div class="space-y-2">
                            <p class="text-xs font-black text-emerald-500 uppercase tracking-widest">{{ __('Heat Peak')
                                }}
                            </p>
                            <div class="flex items-start">
                                <span class="text-5xl sm:text-6xl font-black tracking-tighter text-white"
                                    x-text="current.temp + '°'">--°</span>
                                <span class="text-xl font-bold text-amber-400 mt-1">C</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-emerald-500 uppercase tracking-widest">{{ __('Moisture')
                                }}
                            </p>
                            <span class="block text-4xl sm:text-5xl font-black tracking-tighter text-white"
                                x-text="current.humidity + '%'">--%</span>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-emerald-500 uppercase tracking-widest">{{ __('Rain Peak')
                                }}
                            </p>
                            <span class="block text-4xl sm:text-5xl font-black tracking-tighter text-white"
                                x-text="current.rain + '%'">--%</span>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-emerald-500 uppercase tracking-widest">{{ __('Max Wind')
                                }}
                            </p>
                            <div class="flex items-baseline space-x-1">
                                <span class="text-4xl sm:text-5xl font-black tracking-tighter text-white"
                                    x-text="current.wind">--</span>
                                <span class="text-sm font-bold text-emerald-400">km/h</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 mb-8 h-1 bg-emerald-900 rounded-full w-full"></div>

                    <div class="flex flex-wrap gap-4 relative z-10">
                        <template x-for="insight in insights" :key="insight.text">
                            <div class="px-5 py-3 rounded-full flex items-center space-x-3 text-sm font-black uppercase tracking-wider border-2"
                                :class="{
                                        'bg-emerald-900 border-emerald-700 text-emerald-300': insight.type === 'good',
                                        'bg-amber-950 border-amber-800 text-amber-400': insight.type === 'warning',
                                        'bg-red-950 border-red-800 text-red-400': insight.type === 'danger'
                                    }">
                                <i :data-lucide="insight.icon" class="w-5 h-5"></i>
                                <span x-text="insight.text"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Overall Verdict Card -->
                <div
                    class="lg:col-span-4 bg-amber-500 p-8 sm:p-12 rounded-[3.5rem] border-4 border-amber-400 text-amber-950 shadow-2xl flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-amber-400 rounded-full blur-3xl"></div>
                    <div class="space-y-6 relative z-10">
                        <div
                            class="w-16 h-16 bg-white rounded-[1.2rem] flex items-center justify-center border-2 border-amber-300 shadow-sm text-amber-600">
                            <i data-lucide="cpu" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-3xl sm:text-4xl font-black leading-none tracking-tighter">{{ __('Strategic Verdict')
                            }}</h3>
                        <p class="font-bold text-lg leading-relaxed text-amber-950/80" x-text="verdict">{{
                            __('Calibrating sensors for 7-day agricultural recommendation...') }}</p>
                    </div>

                    <a href="{{ route('detect') }}"
                        class="relative z-10 mt-10 w-full py-5 bg-amber-950 text-white rounded-[1.5rem] font-black text-center text-xl shadow-xl hover:-translate-y-1 hover:bg-black active:translate-y-0 transition-all flex items-center justify-center space-x-3">
                        <span>{{ __('Scan Crops Now') }}</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- 7-Day Action Planner Slider -->
            <div class="mt-8">
                <h3 class="text-2xl font-black text-emerald-400 mb-6 flex items-center space-x-3">
                    <i data-lucide="calendar-range" class="w-6 h-6"></i>
                    <span>{{ __('7-Day Action Plan') }}</span>
                </h3>

                <!-- Scrollable Container -->
                <div class="flex overflow-x-auto pb-8 snap-x snap-mandatory gap-6 scrollpane">
                    <template x-for="(day, index) in forecast" :key="index">
                        <div class="min-w-[280px] sm:min-w-[320px] snap-center bg-[#081811] rounded-[2.5rem] border-2 border-emerald-900 p-8 flex flex-col justify-between hover:border-emerald-700 transition-colors"
                            :style="`animation-delay: ${index * 100}ms; animation-fill-mode: forwards;`">

                            <!-- Header -->
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-emerald-500 font-black uppercase tracking-widest text-xs"
                                        x-text="index === 0 ? '{{ __('Today') }}' : (index === 1 ? '{{ __('Tomorrow') }}' : day.dayName)">
                                    </p>
                                    <p class="text-white font-black text-2xl" x-text="day.dateStr"></p>
                                </div>
                                <div
                                    class="w-12 h-12 bg-emerald-950 rounded-xl flex items-center justify-center border border-emerald-800 text-amber-400 shadow-inner">
                                    <i :data-lucide="day.icon" class="w-6 h-6"></i>
                                </div>
                            </div>

                            <!-- Metrics -->
                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <div class="bg-emerald-950/50 rounded-2xl p-4 border border-emerald-900/50">
                                    <p class="text-[10px] font-black uppercase text-emerald-600 tracking-widest mb-1">{{
                                        __('Temp Range') }}</p>
                                    <p class="text-white font-bold"><span x-text="day.tempMax"></span>° / <span
                                            class="text-emerald-500" x-text="day.tempMin"></span>°</p>
                                </div>
                                <div class="bg-emerald-950/50 rounded-2xl p-4 border border-emerald-900/50">
                                    <p class="text-[10px] font-black uppercase text-emerald-600 tracking-widest mb-1">{{
                                        __('Precipitation') }}</p>
                                    <p class="text-white font-bold" :class="{'text-amber-400': day.rain > 50}"><span
                                            x-text="day.rain"></span>%</p>
                                </div>
                            </div>

                            <!-- Action Tag -->
                            <div class="pt-6 border-t border-emerald-900/50 mt-auto">
                                <div class="p-4 rounded-2xl border-2 flex items-center space-x-3" :class="{
                                            'bg-emerald-900/50 border-emerald-700 text-emerald-200': day.action.type === 'spray',
                                            'bg-blue-900/50 border-blue-700 text-blue-200': day.action.type === 'fertilize',
                                            'bg-red-950/50 border-red-800 text-red-200': day.action.type === 'danger',
                                            'bg-[#06120c] border-emerald-900 text-emerald-600': day.action.type === 'neutral'
                                        }">
                                    <i :data-lucide="day.action.icon" class="w-6 h-6 shrink-0"></i>
                                    <p class="font-black text-sm leading-tight" x-text="day.action.text"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Loading State -->
                    <template x-if="forecast.length === 0">
                        <div class="w-full flex justify-center items-center py-20">
                            <i data-lucide="loader-2" class="w-12 h-12 text-emerald-500 animate-spin"></i>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="max-w-7xl mx-auto px-6 py-24 sm:py-32">
        <div class="text-center mb-16 sm:mb-24 reveal">
            <h2 class="text-5xl md:text-6xl font-black mb-6 tracking-tighter text-emerald-950 dark:text-white">{{
                __('Built for the Field') }}</h2>
            <p class="text-emerald-700/80 dark:text-emerald-400/80 max-w-2xl mx-auto text-xl font-bold">{{
                __('Resilient, extreme precision algorithms wrapped in an interface anyone can use.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-12">
            <div class="group p-8 sm:p-10 bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors reveal"
                style="transition-delay: 100ms">
                <div
                    class="w-20 h-20 bg-emerald-100 dark:bg-[#06120c] rounded-[1.5rem] flex items-center justify-center text-emerald-700 dark:text-emerald-500 mb-8 border-2 border-emerald-200 dark:border-emerald-800">
                    <i data-lucide="zap" class="w-10 h-10"></i>
                </div>
                <h3 class="text-3xl font-black tracking-tight text-emerald-950 dark:text-white mb-4">{{ __('Analysis Speed') }}</h3>
                <p class="text-emerald-800/80 dark:text-emerald-300/80 text-lg font-medium leading-relaxed">{{
                    __('Advanced algorithms deliver field diagnostics in under 1.5 seconds, specifically optimized for varied network conditions.') }}</p>
            </div>

            <div class="group p-8 sm:p-10 bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors reveal"
                style="transition-delay: 200ms">
                <div
                    class="w-20 h-20 bg-emerald-100 dark:bg-[#06120c] rounded-[1.5rem] flex items-center justify-center text-emerald-700 dark:text-emerald-500 mb-8 border-2 border-emerald-200 dark:border-emerald-800">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>
                <h3 class="text-3xl font-black tracking-tight text-emerald-950 dark:text-white mb-4">{{ __('Verified Prescriptions') }}</h3>
                <p class="text-emerald-800/80 dark:text-emerald-300/80 text-lg font-medium leading-relaxed">{{
                    __('Access expert-verified treatment paths tailored perfectly for Sri Lankan cash crops and native agricultural environments.') }}</p>
            </div>

            <div class="group p-8 sm:p-10 bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors reveal"
                style="transition-delay: 300ms">
                <div
                    class="w-20 h-20 bg-emerald-100 dark:bg-[#06120c] rounded-[1.5rem] flex items-center justify-center text-emerald-700 dark:text-emerald-500 mb-8 border-2 border-emerald-200 dark:border-emerald-800">
                    <i data-lucide="microscope" class="w-10 h-10"></i>
                </div>
                <h3 class="text-3xl font-black tracking-tight text-emerald-950 dark:text-white mb-4">{{ __('Offline Resiliency') }}</h3>
                <p class="text-emerald-800/80 dark:text-emerald-300/80 text-lg font-medium leading-relaxed">{{ __('Core diagnostic algorithms queue up scans when offline, instantly processing the moment network connection returns.') }}</p>
            </div>
        </div>
    </section>
</div>

<style>
    .scrollpane::-webkit-scrollbar {
        height: 6px;
    }

    .scrollpane::-webkit-scrollbar-track {
        background: rgba(4, 120, 87, 0.2);
        border-radius: 10px;
    }

    .scrollpane::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.6);
        border-radius: 10px;
    }
</style>
@endsection

@section('scripts')
<script>
    function weatherApp() {
        return {
            locationName: '',
            current: {
                temp: '--',
                humidity: '--',
                rain: '--',
                wind: '--'
            },
            insights: [],
            forecast: [],
            verdict: '{{ __('Acquiring atmospheric data...') }}',
            criticalAlert: null,
            translations: {
                coordinates: '{{ __('Coordinates:') }}',
                telemetryOffline: '{{ __('Telemetry offline.Please verify connectivity.') }}',
                defaultLocation: '{{ __('Colombo, SL(Default)') }}',
                gpsNotAvailable: '{{ __('GPS Not Available') }}',
                routineMaintenance: '{{ __('Routine Maintenance') }}',
                optimalSpraying: '{{ __('Optimal Spraying') }}',
                applyFertilizer: '{{ __('Apply Fertilizer') }}',
                secureEquipment: '{{ __('Secure Equipment') }}',
                heatStressRisk: '{{ __('Heat Stress Risk') }}',
                sprayWindowActive: '{{ __('Spray Window Active') }}',
                fungalRiskElevated: '{{ __('Fungal Risk Elevated') }}',
                conditionsOptimal: '{{ __('Conditions Optimal') }}',
                droughtTitle: '{{ __('Drought & Heat Stress Imminent') }}',
            droughtMsg: '{{ __('Multiple days in the 7-day forecast exceed safe temperature limits.Prepare heavy irrigation scaling and deploy shade nets over nurseries.') }}',
            droughtVerdict: '{{ __('Severe thermal stress approaching.Ensure water reserves are full and avoid transplanting seedlings.') }}',
            floodTitle: '{{ __('Flood/ Washout Protocol') }}',
                floodMsg: '{{ __('High precipitation volume expected over the next 72 hours.Suspend all chemical spraying and clear field drainage routes immediately.') }}',
                    floodVerdict: '{{ __('Heavy monsoon conditions predicted.Protect exposed inputs and secure loose infrastructure.') }}',
                        fungalVerdict: '{{ __('Extreme fungal breeding conditions right now.Scout all perimeters for exact disease footprints and run a rapid diagnosis scan.') }}',
                            optimalVerdict: '{{ __('Stable metrics.Excellent 7 - day conditions for executing precision agriculture protocols.Refer to the action planner below.') }}',
                                days: {
            Sun: '{{ __('Sun') }}',
                Mon: '{{ __('Mon') }}',
                    Tue: '{{ __('Tue') }}',
                        Wed: '{{ __('Wed') }}',
                            Thu: '{{ __('Thu') }}',
                                Fri: '{{ __('Fri') }}',
                                    Sat: '{{ __('Sat') }}'
        }
    },

            async init() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => this.fetchWeather(pos.coords.latitude, pos.coords.longitude),
                err => {
                    console.error(err);
                    this.fetchWeather(6.9271, 79.8612); // Colombo default
                    this.locationName = this.translations.defaultLocation;
                }
            );
        } else {
            this.fetchWeather(6.9271, 79.8612);
            this.locationName = this.translations.gpsNotAvailable;
        }
    },

            async fetchWeather(lat, lon) {
        try {
            // Fetch both current weather and daily 7-day forecast
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&hourly=relativehumidity_2m&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,windspeed_10m_max,weathercode&timezone=auto`;

            const res = await fetch(url);
            const data = await res.json();

            // Current setup
            this.current = {
                temp: Math.round(data.current_weather.temperature),
                wind: Math.round(data.current_weather.windspeed),
                humidity: data.hourly.relativehumidity_2m[0], // approximate current
                rain: data.daily.precipitation_probability_max[0]
            };

            this.locationName = this.locationName || `${this.translations.coordinates} ${lat.toFixed(2)}, ${lon.toFixed(2)}`;

            // Process daily forecast
            this.processForecast(data.daily);

        } catch (e) {
            this.verdict = this.translations.telemetryOffline;
            console.error("Weather fetch failed:", e);
        }
    },

    processForecast(daily) {
        const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        this.forecast = [];
        let extremeHeatCount = 0;

        for (let i = 0; i < 7; i++) {
            const dateObj = new Date(daily.time[i]);
            const tempMax = Math.round(daily.temperature_2m_max[i]);
            const tempMin = Math.round(daily.temperature_2m_min[i]);
            const rainProb = daily.precipitation_probability_max[i];
            const rainSum = daily.precipitation_sum[i];
            const windMax = daily.windspeed_10m_max[i];
            const code = daily.weathercode[i];

            // Logic Engine for Actions
            let action = { type: 'neutral', text: this.translations.routineMaintenance, icon: 'clipboard-list' };

            if (windMax < 15 && rainProb < 20) {
                action = { type: 'spray', text: this.translations.optimalSpraying, icon: 'spray-can' };
            }
            else if (rainSum >= 2 && rainSum <= 10 && (i === 0 || i === 1)) {
                action = { type: 'fertilize', text: this.translations.applyFertilizer, icon: 'leafy-green' };
            }
            else if (rainProb > 70 || windMax > 30) {
                action = { type: 'danger', text: this.translations.secureEquipment, icon: 'shield-alert' };
            }
            else if (tempMax > 33) {
                action = { type: 'danger', text: this.translations.heatStressRisk, icon: 'sun-dim' };
                extremeHeatCount++;
            }

            // Map WMO codes to Lucide icons
            let iconName = 'cloud';
            if (code === 0) iconName = 'sun';
            else if (code <= 3) iconName = 'cloud-sun';
            else if (code <= 48) iconName = 'cloud-fog';
            else if (code <= 57) iconName = 'cloud-drizzle';
            else if (code <= 67) iconName = 'cloud-rain';
            else if (code <= 77) iconName = 'snowflake';
            else if (code <= 82) iconName = 'cloud-showers-heavy';
            else if (code >= 95) iconName = 'cloud-lightning';

            this.forecast.push({
                dayName: this.translations.days[daysOfWeek[dateObj.getDay()]],
                dateStr: dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                tempMax, tempMin, rain: rainProb, wind: windMax,
                icon: iconName,
                action
            });
        }

        // Analyze entire week pattern for critical alerts
        this.generateStrategicVerdict(daily, extremeHeatCount);

        // Run icon injection for the newly templated elements
        this.$nextTick(() => window.lucide && window.lucide.createIcons());
    },

    generateStrategicVerdict(daily, heatCount) {
        this.insights = [];
        const current = this.current;

        // Active insights
        if (current.wind < 15 && current.rain < 20) {
            this.insights.push({ text: this.translations.sprayWindowActive, icon: 'spray-can', type: 'good' });
        }
        if (current.humidity > 80 && current.temp > 25) {
            this.insights.push({ text: this.translations.fungalRiskElevated, icon: 'biohazard', type: 'danger' });
        }

        this.criticalAlert = null;

        // Strategic Verdict
        const avgRain = daily.precipitation_probability_max.slice(0, 3).reduce((a, b) => a + b, 0) / 3;

        if (heatCount >= 3) {
            this.criticalAlert = {
                title: this.translations.droughtTitle,
                message: this.translations.droughtMsg
            };
            this.verdict = this.translations.droughtVerdict;
        } else if (avgRain > 70) {
            this.criticalAlert = {
                title: this.translations.floodTitle,
                message: this.translations.floodMsg
            };
            this.verdict = this.translations.floodVerdict;
        } else if (current.humidity > 85 && current.temp > 28) {
            this.verdict = this.translations.fungalVerdict;
        } else {
            this.insights.push({ text: this.translations.conditionsOptimal, icon: 'check-circle-2', type: 'good' });
            this.verdict = this.translations.optimalVerdict;
        }
    }
        }
    }
</script>
@endsection