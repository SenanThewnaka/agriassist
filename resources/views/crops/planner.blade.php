@extends('layouts.app')

@section('content')
<section class="py-20 px-6 min-h-screen bg-emerald-50 dark:bg-[#06120c] relative">
    <!-- Background element -->
    <div
        class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-[100px] -z-10 pointer-events-none">
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="reveal text-center mb-16 sm:mb-24 space-y-4">
            <div
                class="inline-flex items-center justify-center space-x-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 px-4 py-1.5 rounded-full text-xs font-black tracking-widest uppercase mb-4 shadow-sm border border-emerald-200 dark:border-emerald-800">
                <i data-lucide="map" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ __('Yield Forecaster') }}</span>
            </div>
            <h1
                class="text-5xl md:text-6xl lg:text-7xl font-black tracking-tighter text-emerald-950 dark:text-white leading-tight">
                Crop <span class="text-emerald-700 dark:text-emerald-400">{{ __('Planner') }}</span>
            </h1>
            <p
                class="text-emerald-700/80 dark:text-emerald-300/70 max-w-2xl mx-auto text-lg sm:text-xl font-medium leading-relaxed">
                {{ __('Generate highly accurate cultivation timelines optimized for Sri Lankan agricultural zones.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <!-- Form Section (Column 1-5) -->
            <div class="lg:col-span-5 reveal" style="transition-delay: 100ms">
                <div
                    class="bg-white dark:bg-[#081811] border-4 border-emerald-100 dark:border-emerald-900/50 p-8 sm:p-10 rounded-[2.5rem] shadow-2xl relative overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors duration-500">
                    <form id="plannerForm" class="space-y-8 relative z-10">
                        @csrf
                        <div class="space-y-6">
                            <!-- Crop Select -->
                            <div class="relative">
                                <label for="crop_id"
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-500 mb-2 block ml-1">{{ __('Cultivation Target') }}</label>
                                <div class="relative">
                                    <select id="crop_id" name="crop_id"
                                        class="w-full pl-6 pr-12 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer font-bold text-lg text-emerald-950 dark:text-emerald-50">
                                        <option value="">-- {{ __('Main Crop') }} --</option>
                                        @foreach($crops as $crop)
                                        <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-emerald-600 dark:text-emerald-500">
                                        <i data-lucide="chevron-down" class="w-6 h-6"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Variety Select -->
                            <div class="relative">
                                <label for="crop_variety_id"
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-500 mb-2 block ml-1">{{ __('Specific Variety') }}</label>
                                <div class="relative">
                                    <select id="crop_variety_id" name="crop_variety_id" disabled
                                        class="w-full pl-6 pr-12 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer font-bold text-lg text-emerald-950 dark:text-emerald-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">-- {{ __('Seed Type') }} --</option>
                                    </select>
                                    <div
                                        class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-emerald-600 dark:text-emerald-500">
                                        <i data-lucide="sprout" class="w-6 h-6"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative">
                                    <label for="planting_date"
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-500 mb-2 block ml-1">{{ __('Planting Date') }}</label>
                                    <input type="date" id="planting_date" name="planting_date" required
                                        value="{{ date('Y-m-d') }}"
                                        class="w-full px-5 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all font-bold text-lg text-emerald-950 dark:text-emerald-50">
                                </div>
                                <div class="relative">
                                    <label for="location"
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-500 mb-2 block ml-1">{{ __('Zone (Optional)') }}</label>
                                    <input type="text" id="location" name="location"
                                        placeholder="{{ __('e.g. Dry Zone') }}"
                                        class="w-full px-5 py-5 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all font-bold text-lg text-emerald-950 dark:text-emerald-50">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn"
                            class="group w-full py-6 sm:py-7 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-[2rem] font-black text-xl shadow-2xl hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center space-x-3 border-b-4 border-emerald-900 dark:border-emerald-800">
                            <span>{{ __('Calculate Harvest') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-6 h-6 group-hover:translate-x-2 transition-transform"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Result Section (Column 6-12) -->
            <div class="lg:col-span-7 h-full">
                <!-- Initial State -->
                <div id="initialState"
                    class="reveal h-full flex flex-col items-center justify-center p-12 sm:p-20 bg-white dark:bg-[#081811] border-4 border-dashed border-emerald-200 dark:border-emerald-900/50 rounded-[3rem] text-center"
                    style="transition-delay: 300ms">
                    <div
                        class="w-24 h-24 bg-emerald-100 dark:bg-[#06120c] rounded-full flex items-center justify-center mb-8 border-4 border-emerald-200 dark:border-emerald-800 shadow-sm">
                        <i data-lucide="calendar-days" class="w-10 h-10 text-emerald-600 dark:text-emerald-500"></i>
                    </div>
                    <h3 class="text-3xl font-black mb-4 text-emerald-950 dark:text-white tracking-tight">{{ __('Timeline Pending') }}</h3>
                    <p class="text-emerald-700/80 dark:text-emerald-400/80 text-lg max-w-sm font-semibold">{{ __('Select your crop parameters on the left to map out the entire seasonal cycle.') }}</p>
                </div>

                <!-- Result Card (Hidden by default) -->
                <div id="resultCard"
                    class="hidden space-y-8 animate-in fade-in slide-in-from-bottom-10 duration-700 origin-top">
                    <div
                        class="bg-white dark:bg-[#081811] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl rounded-[3rem] p-8 sm:p-12 relative overflow-hidden">

                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-12 relative z-10">
                            <div>
                                <span id="resCategory"
                                    class="px-4 py-1.5 bg-emerald-100 dark:bg-[#0a1e15] text-emerald-700 dark:text-emerald-500 text-xs font-black uppercase tracking-widest rounded-full mb-3 inline-block border border-emerald-200 dark:border-emerald-800">{{ __('Selected Crop') }}</span>
                                <h2 id="resCropVariety"
                                    class="text-4xl sm:text-5xl font-black tracking-tighter text-emerald-950 dark:text-white leading-none">
                                    Rice - BG 300</h2>
                            </div>
                            <div
                                class="px-6 py-4 bg-emerald-950 dark:bg-[#06120c] rounded-[1.5rem] border-2 border-emerald-800 text-center shadow-inner">
                                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-1">
                                    {{ __('Maturity Cycle') }}</p>
                                <p class="text-3xl font-black text-white"><span id="resDuration">90</span> <span
                                        class="text-sm font-bold opacity-70">{{ __('Days') }}</span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <div
                                class="p-6 bg-emerald-50 dark:bg-[#0a1e15] rounded-[2rem] border-2 border-emerald-200 dark:border-emerald-800/50 flex items-center space-x-6 shadow-sm">
                                <div
                                    class="w-14 h-14 bg-white dark:bg-[#06120c] shadow-md rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-500 border border-emerald-100 dark:border-emerald-900 border-b-4">
                                    <i data-lucide="log-in" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-500 mb-1">
                                        {{ __('Sowing Date') }}</p>
                                    <p id="resPlantDate"
                                        class="text-xl sm:text-2xl font-black tracking-tight text-emerald-950 dark:text-white">
                                        May 10, 2026</p>
                                </div>
                            </div>

                            <div
                                class="p-6 bg-emerald-900 dark:bg-emerald-950 rounded-[2rem] border-2 border-emerald-800 flex items-center space-x-6 shadow-md text-white">
                                <div
                                    class="w-14 h-14 bg-emerald-800 shadow-inner rounded-2xl flex items-center justify-center border-2 border-emerald-600 text-amber-400">
                                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400 mb-1">
                                        {{ __('Estimated Harvest') }}</p>
                                    <p id="resHarvestDate"
                                        class="text-xl sm:text-2xl font-black tracking-tight text-white">Aug 08, 2026
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Advice Section -->
                        <div id="adviceContainer" class="mt-8 relative z-10">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Progress Visualization -->
                    <div
                        class="bg-white dark:bg-[#081811] border-4 border-emerald-100 dark:border-emerald-900 rounded-[2.5rem] p-8 sm:p-10 shadow-xl overflow-hidden relative">
                        <h4
                            class="text-lg font-black tracking-tight text-emerald-950 dark:text-white mb-6 flex items-center">
                            <i data-lucide="activity" class="w-5 h-5 mr-3 text-emerald-600 dark:text-emerald-400"></i>
                            Maturation Model
                        </h4>

                        <div
                            class="relative h-6 bg-emerald-100 dark:bg-emerald-950 rounded-full overflow-hidden mb-5 border-2 border-emerald-200 dark:border-emerald-900/50 shadow-inner">
                            <div class="absolute left-0 top-0 h-full bg-emerald-200 dark:bg-emerald-900/40 w-full">
                            </div>
                            <div class="absolute left-0 top-0 h-full bg-emerald-600 transition-all duration-[1500ms] ease-out w-1/3"
                                id="progressBar"></div>
                        </div>

                        <div
                            class="flex justify-between text-[9px] sm:text-[10px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest">
                            <span>{{ __('Sow') }}</span>
                            <span class="hidden sm:inline">{{ __('Germin') }}</span>
                            <span>{{ __('Veg') }}</span>
                            <span class="hidden sm:inline">{{ __('Flower') }}</span>
                            <span class="text-emerald-950 dark:text-white">{{ __('Harvest') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loading" class="hidden h-full flex flex-col items-center justify-center p-16">
                    <div class="relative w-24 h-24 mb-8">
                        <div class="absolute inset-0 border-4 border-emerald-200 dark:border-emerald-900 rounded-full">
                        </div>
                        <div
                            class="absolute inset-0 border-4 border-emerald-600 rounded-full border-t-transparent animate-spin">
                        </div>
                        <i data-lucide="cpu"
                            class="absolute inset-0 m-auto w-8 h-8 text-emerald-700 dark:text-emerald-500 animate-pulse"></i>
                    </div>
                    <p class="text-emerald-800 dark:text-emerald-400 font-black text-xl animate-pulse tracking-tight">
                        {{ __('Running Projections...') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cropSelect = document.getElementById('crop_id');
        const varietySelect = document.getElementById('crop_variety_id');
        const plannerForm = document.getElementById('plannerForm');

        const initialState = document.getElementById('initialState');
        const resultCard = document.getElementById('resultCard');
        const loading = document.getElementById('loading');

        // Result elements
        const resCropVariety = document.getElementById('resCropVariety');
        const resDuration = document.getElementById('resDuration');
        const resPlantDate = document.getElementById('resPlantDate');
        const resHarvestDate = document.getElementById('resHarvestDate');
        const adviceContainer = document.getElementById('adviceContainer');
        const progressBar = document.getElementById('progressBar');

        // Variety Fetching
        cropSelect.addEventListener('change', async function () {
            const cropId = this.value;
            varietySelect.innerHTML = '<option value="">-- {{ __('Seed Type') }} --</option>';
            varietySelect.disabled = true;

            if (cropId) {
                try {
                    const response = await fetch(`/crops/${cropId}/varieties`);
                    const data = await response.json();

                    if (data.length > 0) {
                        data.forEach(variety => {
                            const option = document.createElement('option');
                            option.value = variety.id;
                            option.dataset.days = variety.growth_days;
                            option.dataset.season = variety.season;
                            option.textContent = `${variety.variety_name} (${variety.growth_days} days)`;
                            varietySelect.appendChild(option);
                        });
                        varietySelect.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        });

        // Calculation & Submission
        plannerForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            initialState.classList.add('hidden');
            resultCard.classList.add('hidden');
            progressBar.style.width = '0%';
            loading.classList.remove('hidden');

            const formData = new FormData(this);
            const varietyId = formData.get('crop_variety_id');
            const plantDateStr = formData.get('planting_date');

            try {
                const response = await fetch('/api/crop-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        crop_variety_id: varietyId,
                        planting_date: plantDateStr
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Update UI
                    resCropVariety.textContent = `${data.crop} - ${data.variety}`;
                    resDuration.textContent = data.growth_days;

                    const formatOptions = { month: 'long', day: 'numeric', year: 'numeric' };
                    resPlantDate.textContent = new Date(data.planting_date).toLocaleDateString('en-US', formatOptions);
                    resHarvestDate.textContent = new Date(data.estimated_harvest).toLocaleDateString('en-US', formatOptions);

                    // Seasonal Advice Logic
                    const varietyOption = varietySelect.options[varietySelect.selectedIndex];
                    const season = varietyOption.dataset.season;
                    const plantMonth = new Date(data.planting_date).getMonth() + 1;

                    let adviceHtml = '';
                    const isMaha = (plantMonth >= 9 && plantMonth <= 10);
                    const isYala = (plantMonth >= 4 && plantMonth <= 5);

                    if (season === 'maha' && !isMaha) {
                        adviceHtml = `
                            <div class="p-6 bg-amber-50 dark:bg-[#1a1305] border-2 border-amber-200 dark:border-amber-900/50 rounded-[1.5rem] flex items-center space-x-5 animate-in zoom-in duration-500 shadow-sm">
                                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-950 border border-amber-300 dark:border-amber-800 rounded-[1.2rem] flex items-center justify-center text-amber-600 dark:text-amber-500 shrink-0">
                                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <p class="text-amber-950 dark:text-amber-400 font-black tracking-tight text-lg leading-tight">Maha Season Alignment</p>
                                    <p class="text-amber-800/80 dark:text-amber-500/80 font-bold mt-1">This seed thrives optimally when planted during the Maha season (Sept - Oct).</p>
                                </div>
                            </div>`;
                    } else if (season === 'yala' && !isYala) {
                        adviceHtml = `
                            <div class="p-6 bg-amber-50 dark:bg-[#1a1305] border-2 border-amber-200 dark:border-amber-900/50 rounded-[1.5rem] flex items-center space-x-5 animate-in zoom-in duration-500 shadow-sm">
                                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-950 border border-amber-300 dark:border-amber-800 rounded-[1.2rem] flex items-center justify-center text-amber-600 dark:text-amber-500 shrink-0">
                                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <p class="text-amber-950 dark:text-amber-400 font-black tracking-tight text-lg leading-tight">Yala Season Alignment</p>
                                    <p class="text-amber-800/80 dark:text-amber-500/80 font-bold mt-1">This seed is genetically optimized for the Yala season (April - May).</p>
                                </div>
                            </div>`;
                    } else {
                        adviceHtml = `
                            <div class="p-6 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-200 dark:border-emerald-800/50 rounded-[1.5rem] flex items-center space-x-5 animate-in zoom-in duration-500 shadow-sm">
                                <div class="w-14 h-14 bg-emerald-100 dark:bg-[#081811] border border-emerald-300 dark:border-emerald-700 rounded-[1.2rem] flex items-center justify-center text-emerald-600 dark:text-emerald-500 shrink-0">
                                    <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <p class="text-emerald-950 dark:text-white font-black tracking-tight text-lg leading-tight">Optimal Alignment</p>
                                    <p class="text-emerald-800/80 dark:text-emerald-400/80 font-bold mt-1">Perfect timing. Your schedule correlates precisely with the required climate phase.</p>
                                </div>
                            </div>`;
                    }

                    adviceContainer.innerHTML = adviceHtml;
                    lucide.createIcons();

                    // Show results
                    setTimeout(() => {
                        loading.classList.add('hidden');
                        resultCard.classList.remove('hidden');

                        setTimeout(() => {
                            progressBar.style.width = '100%';
                        }, 50);
                    }, 500);
                }
            } catch (error) {
                console.error('Error:', error);
                loading.classList.add('hidden');
                initialState.classList.remove('hidden');
            }
        });
    });
</script>
@endsection