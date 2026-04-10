(function () {
    let currentStep = 1;
    let currentSoilType = '';
    let selectedVarietyId = '';
    let lastSuggestionsData = null;
    let lastRoadmapData = null;
    let planningMethod = 'manual'; // 'manual' or 'ai'
    let aiRecommendedDate = null;

    // data from blade template
    const config = window.__PLANNER_CONFIG || {};
    const csrf = config.csrf;
    let locale = config.locale || 'en';
    let translations = config.translations || {};

    window.switchLanguage = window.switchLanguageTo = function (newLang) {
        locale = newLang;
        // Update static UI elements for immediate feedback
        updateStaticTranslations();

        // Re-render current data if available
        if (currentStep === 2 && lastSuggestionsData) {
            renderSuggestions(lastSuggestionsData);
        } else if (currentStep === 3 && lastRoadmapData) {
            if (lastRoadmapData.is_ai) {
                const genBtn = document.getElementById('generateRoadmapBtn');
                if (genBtn) genBtn.click();
            } else {
                renderRoadmap(lastRoadmapData);
            }
        }

        // Notify backend in background to keep session in sync
        fetch("/lang/" + newLang, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(e => console.error('Failed to sync locale with backend:', e));
    };

    function t(key) {
        if (translations[locale] && translations[locale][key]) {
            return translations[locale][key];
        }
        return key;
    }

    function updateStaticTranslations() {
        // Update all elements with data-t-key
        document.querySelectorAll('[data-t-key]').forEach(el => {
            const key = el.getAttribute('data-t-key');
            if (key === 'Smart Farm Wizard') {
                const smartFarm = locale === 'si' ? 'ස්මාර්ට් ගොවිපල' : (locale === 'ta' ? 'ස්මාර්්ට பண்ணை' : 'Smart Farm');
                const wizard = locale === 'si' ? 'සහායකයා' : (locale === 'ta' ? 'வழிகாட்டி' : 'Wizard');
                el.innerHTML = `${smartFarm} <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">${wizard}</span>`;
            } else {
                el.innerHTML = t(key);
            }
        });

        // Update Step Labels
        ['Soil Type', 'Crop Selection', 'Cultivation Roadmap'].forEach((step, i) => {
            const label = document.getElementById('step-label-' + (i + 1));
            if (label) label.textContent = t(step);
        });

        const aiBadge = document.querySelector('.animate-fade-in span.text-xs');
        if (aiBadge) aiBadge.textContent = t('AI Sustainable Farming');

        const subtitle = document.querySelector('p.text-xl');
        if (subtitle) {
            subtitle.textContent = t('Precision agriculture powered by AI. Detect your soil, get optimal crop suggestions, and generate your roadmap.');
        }

        // Step 1
        const s1h3 = document.querySelector('#step1 h3');
        if (s1h3) s1h3.textContent = t('Precision Detection');
        const s1p = document.querySelector('#step1 p.font-bold');
        if (s1p) s1p.textContent = t("Use AI-powered geolocation to automatically identify your soil's composition and chemical properties.");
        const detectBtn = document.getElementById('detectLocationBtn');
        if (detectBtn) {
            const btnText = t('Detect My Soil');
            detectBtn.innerHTML = `<i data-lucide="crosshair" class="w-6 h-6 mr-3"></i> <span>${btnText}</span>`;
        }
        const districtLabel = document.querySelector('#districtPicker')?.closest('.border-t')?.querySelector('p');
        if (districtLabel) districtLabel.textContent = t('Or select your district');
        const districtDefault = document.querySelector('#districtPicker option[value=""]');
        if (districtDefault) districtDefault.textContent = t('-- Pick district --');
        const manualLabel = document.querySelector('#step1 p.uppercase');
        if (manualLabel) manualLabel.textContent = t('Or Manual Selection');
        const step1Next = document.getElementById('step1NextBtn');
        if (step1Next) step1Next.textContent = t('Next: Identify Crops');

        // Soil buttons
        document.querySelectorAll('.soil-btn').forEach(btn => {
            const span = btn.querySelector('span.text-lg');
            if (span) {
                const key = btn.dataset.soil;
                span.textContent = t(key);
            }
        });

        // Step 2
        const s2h3 = document.querySelector('#step2 h3');
        if (s2h3) s2h3.textContent = t('Smart Predictions');

        // Translate manualCropId options
        document.querySelectorAll('#manualCropId option').forEach(opt => {
            if (opt.value === "") {
                opt.textContent = t('Choose Different Crop');
            } else if (opt.value === "other") {
                opt.textContent = t('Other (Custom Crop)');
            } else {
                const name = locale === 'si' ? opt.dataset.nameSi : (locale === 'ta' ? opt.dataset.nameTa : opt.dataset.name);
                if (name) opt.textContent = name;
            }
        });

        const manualVarietyDefault = document.querySelector('#manualVarietyId option[value=""]');
        if (manualVarietyDefault) manualVarietyDefault.textContent = t('Seed Type');

        const loadingSuggestions = document.querySelector('#suggestionsLoading p');
        if (loadingSuggestions) loadingSuggestions.textContent = t('Running AI simulations...');

        // Step 3
        const s3h3 = document.querySelector('#roadmapConfig h3');
        if (s3h3) s3h3.textContent = t('Planning Your Harvest');
        const s3p = document.querySelector('#manualDateInput p');
        if (s3p) s3p.textContent = t("When do you plan to start planting? We'll tailor each growth stage to the local seasonal shifts.");
        const genBtn = document.getElementById('generateRoadmapBtn');
        if (genBtn) genBtn.textContent = t('Generate Cultivation Roadmap');
        const pickDiff = document.getElementById('backToStep2FromConfig');
        if (pickDiff) pickDiff.textContent = t('Pick a different crop');

        const loadingRoadmap = document.querySelector('#roadmapLoading p');
        if (loadingRoadmap) loadingRoadmap.textContent = t('Generating your personalized roadmap...');

        if (window.lucide) lucide.createIcons();
    }

    window.showStep = function (step) {
        currentStep = step;
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('hidden'));
        document.getElementById('step' + step).classList.remove('hidden');

        // update progress bar
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            const progress = ((step - 1) / 2) * 100;
            progressBar.style.width = progress + '%';
        }

        // Update Steps UI
        for (let i = 1; i <= 3; i++) {
            const dot = document.getElementById('step-dot-' + i);
            const label = document.getElementById('step-label-' + i);
            if (!dot || !label) continue;

            // Reset UI state
            dot.className = 'step-dot-base w-14 h-14 rounded-2xl border-4 border-emerald-100 dark:border-emerald-900 flex items-center justify-center text-xl font-black transition-all duration-500 z-20 group-hover:scale-110';
            label.className = 'mt-4 text-xs font-black uppercase tracking-widest transition-colors duration-300';

            if (i < step) {
                dot.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white');
                dot.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                label.classList.add('text-emerald-600');
            } else if (i === step) {
                dot.classList.add('border-emerald-600', 'text-emerald-600', 'scale-110');
                dot.innerHTML = i;
                label.classList.add('text-emerald-950', 'dark:text-white');
            } else {
                dot.classList.add('text-emerald-200', 'dark:text-emerald-800');
                dot.innerHTML = i;
                label.classList.add('text-emerald-900/40', 'dark:text-emerald-500/40');
            }
        }
        if (window.lucide) lucide.createIcons();
        if (step === 2 && !lastSuggestionsData) loadSuggestions();
    };

    async function loadSuggestions() {
        const grid = document.getElementById('suggestionsGrid');
        const loading = document.getElementById('suggestionsLoading');
        if (!grid || !loading) return;

        grid.innerHTML = '';
        grid.classList.add('hidden');
        loading.classList.remove('hidden');

        try {
            const res = await fetch('/api/smart-suggestions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    soil_type: currentSoilType,
                    month: new Date().getMonth() + 1,
                    temperature: 28,
                    locale: locale
                })
            });
            const data = await res.json();
            lastSuggestionsData = data;
            renderSuggestions(data);

            loading.classList.add('hidden');
            grid.classList.remove('hidden');
        } catch (e) {
            console.error('API Error:', e);
            loading.classList.add('hidden');
            grid.innerHTML = `<div class="col-span-full text-center p-20 text-red-500 font-bold">${t('Connection lost. Please try again.')}</div>`;
            grid.classList.remove('hidden');
        }
    }

    function renderSuggestions(data) {
        const grid = document.getElementById('suggestionsGrid');
        if (!grid) return;

        grid.innerHTML = '';
        const subtitle = document.getElementById('step2Subtitle');
        if (subtitle) {
            const suffix = t('Best crops for your region');
            subtitle.textContent = data.suggestions.length + ' ' + suffix;
        }

        if (!data.suggestions || data.suggestions.length === 0) {
            const title = t('No ideal matches found');
            const desc = t('Try selecting a different soil type or use the manual search above.');
            grid.innerHTML = `<div class="col-span-full p-20 text-center"><div class="text-6xl mb-4">🏜️</div><h3 class="text-2xl font-black text-emerald-950 dark:text-white">${title}</h3><p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold">${desc}</p></div>`;
        } else {
            data.suggestions.forEach(crop => {
                const name = locale === 'si' ? (crop.crop_name_si || crop.crop_name) : (locale === 'ta' ? (crop.crop_name_ta || crop.crop_name) : crop.crop_name);
                const variety = locale === 'si' ? (crop.variety_name_si || crop.variety_name) : (locale === 'ta' ? (crop.variety_name_ta || crop.variety_name) : crop.variety_name);

                const card = document.createElement('div');
                card.className = 'p-6 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2rem] cursor-pointer hover:border-emerald-400 transition-all duration-300 group';
                card.innerHTML = `
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-black text-emerald-950 dark:text-white capitalize">${name}</h3>
                            <p class="text-sm font-bold text-emerald-600">${variety} &middot; ${crop.growth_days} ${t('Days')}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-black text-emerald-700">${crop.suitability}%</div>
                            <div class="text-[9px] font-black uppercase text-emerald-500">${t('Match')}</div>
                        </div>
                    </div>
                    <div class="h-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-emerald-600" style="width:${crop.suitability}%"></div>
                    </div>
                `;
                card.addEventListener('click', () => {
                    const manualCrop = document.getElementById('manualCropId');
                    if (manualCrop) {
                        manualCrop.value = crop.crop_id;
                        manualCrop.dispatchEvent(new Event('change'));

                        setTimeout(() => {
                            const manualVariety = document.getElementById('manualVarietyId');
                            if (manualVariety) {
                                manualVariety.value = crop.variety_id;
                                manualVariety.dispatchEvent(new Event('change'));
                                manualVariety.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                manualVariety.classList.add('ring-4', 'ring-emerald-500/50');
                                setTimeout(() => manualVariety.classList.remove('ring-4', 'ring-emerald-500/50'), 2000);
                            }
                        }, 500);
                    }
                });
                grid.appendChild(card);
            });
        }
    }

    function renderRoadmap(data) {
        console.log('Roadmap Data:', data);
        const container = document.getElementById('roadmapContainer');
        if (!container) return;

        container.innerHTML = '<div class="absolute left-[22px] top-6 bottom-6 w-1 bg-gradient-to-b from-emerald-600 via-emerald-400 to-emerald-200 dark:from-emerald-700 dark:via-emerald-800 dark:to-emerald-950 rounded-full hidden sm:block"></div>';

        let cropName = locale === 'si' ? (data.crop_name_si || data.crop) : (locale === 'ta' ? (data.crop_name_ta || data.crop) : data.crop);
        let varietyName = locale === 'si' ? (data.variety_name_si || data.variety) : (locale === 'ta' ? (data.variety_name_ta || data.variety) : data.variety);

        // Ultimate fallback to prevent "undefined" display
        cropName = cropName || data.crop || t('Unknown Crop');
        varietyName = varietyName || data.variety || t('Standard Variety');

        // Update Resource Estimates
        if (data.estimates) {
            document.getElementById('estSeeds').textContent = data.estimates.seeds_kg || 0;
            document.getElementById('estUrea').textContent = data.estimates.urea_kg || 0;
            document.getElementById('estTsp').textContent = data.estimates.tsp_kg || 0;
            document.getElementById('estMop').textContent = data.estimates.mop_kg || 0;
            document.getElementById('estYield').textContent = data.estimates.expected_yield_kg || 0;
            document.getElementById('estRevenue').textContent = new Intl.NumberFormat().format(data.estimates.estimated_revenue || 0);
        }

        document.getElementById('resDuration').textContent = data.growth_days || 0;
        document.getElementById('resCropVariety').innerHTML = `
            ${cropName} <span class="text-emerald-500/40 mx-2">-</span> ${varietyName}
            ${data.is_ai ? `<span class="inline-flex items-center px-4 py-1.5 ml-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-800 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-400 animate-pulse">
                <i data-lucide="cpu" class="w-3 h-3 mr-2"></i> ${t('AI Roadmap')}
            </span>` : ''}
        `;

        const dateLocale = 'en-LK';
        const pDate = new Date(data.planting_date);
        const hDate = new Date(data.estimated_harvest);
        document.getElementById('resPlantDate').textContent = pDate.toLocaleDateString(dateLocale, { dateStyle: 'long' });
        document.getElementById('resHarvestDate').textContent = hDate.toLocaleDateString(dateLocale, { dateStyle: 'long' });

        // Update Pest Alerts
        const pestContainer = document.getElementById('pestAlertsContainer');
        if (pestContainer) {
            pestContainer.innerHTML = '';
            if (data.pest_alerts && data.pest_alerts.length > 0) {
                pestContainer.classList.remove('hidden');
                data.pest_alerts.forEach(alert => {
                    const alertEl = document.createElement('div');
                    alertEl.className = 'p-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-100 dark:border-red-900 rounded-[2rem] flex items-start space-x-4 mb-4 animate-in fade-in slide-in-from-top-4 duration-500';
                    alertEl.innerHTML = `
                        <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-xl shrink-0">
                            <i data-lucide="shield-alert" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-red-950 dark:text-red-200 text-lg uppercase tracking-tight">${t('Active Alert')}: ${alert.pest_name}</h4>
                            <p class="text-sm text-red-800/80 dark:text-red-300/70 font-bold mb-3">${alert.message}</p>
                            <div class="flex items-center text-[10px] font-black uppercase tracking-widest text-red-600">
                                <i data-lucide="info" class="w-3 h-3 mr-1"></i> ${t('Action Required')}: ${alert.recommended_action}
                            </div>
                        </div>
                    `;
                    pestContainer.appendChild(alertEl);
                });
            } else {
                pestContainer.classList.add('hidden');
            }
        }

        const overviewH4 = document.querySelector('#resultCard h4');
        if (overviewH4) overviewH4.textContent = t('Quick Overview');
        const durationLabel = document.querySelector('#resultCard div[class*="text-[10px]"]:nth-of-type(1)');
        if (durationLabel) durationLabel.textContent = t('Duration');

        const savePdfBtn = document.querySelector('button[onclick="window.print()"]');
        if (savePdfBtn) savePdfBtn.innerHTML = `<i data-lucide="printer" class="w-5 h-5 mr-3"></i> ${t('Save as PDF')}`;
        const startOverBtn = document.getElementById('restartWizardBtn');
        if (startOverBtn) startOverBtn.textContent = t('Start Over');

        // Weather Risk Intelligence
        let weatherForecast = [];
        const fetchWeatherIntelligence = async () => {
            const cached = localStorage.getItem('agriassist_cached_location');
            if (!cached) return;
            const loc = JSON.parse(cached);
            try {
                const url = `https://api.open-meteo.com/v1/forecast?latitude=${loc.lat}&longitude=${loc.lon}&daily=precipitation_sum&timezone=auto&forecast_days=14`;
                const res = await fetch(url);
                const wData = await res.json();
                weatherForecast = wData.daily.time.map((time, idx) => ({
                    date: time,
                    rain: wData.daily.precipitation_sum[idx]
                }));
            } catch (e) { console.error('Weather intelligence failed', e); }
        };

        const drawStages = () => {
            data.stages.forEach((stage, i) => {
                const stageName = locale === 'si' ? (stage.name_si || stage.name) : (locale === 'ta' ? (stage.name_ta || stage.name) : stage.name);
                const stageAdvice = locale === 'si' ? (stage.advice_si || stage.advice) : (locale === 'ta' ? (stage.advice_ta || stage.advice) : stage.advice);
                const stageDesc = locale === 'si' ? (stage.description_si || stage.description) : (locale === 'ta' ? (stage.description_ta || stage.description) : stage.description);

                const stageEl = document.createElement('div');
                stageEl.className = 'relative flex flex-col sm:flex-row sm:space-x-8 pb-12 last:pb-0 group/stage';
                const nextStage = data.stages[i + 1];
                const nextDay = nextStage ? nextStage.days_from_start - 1 : data.growth_days;

                const dayLabel = locale === 'si' ? 'දිනය' : (locale === 'ta' ? 'நாள்' : 'Day');
                const dayRange = stage.days_from_start === nextDay ? `${dayLabel} ${stage.days_from_start}` : `${dayLabel} ${stage.days_from_start} - ${nextDay}`;

                const sDate = new Date(stage.date);
                const formattedDate = sDate.toLocaleDateString('en-LK', { dateStyle: 'medium' });
                const dateStr = stage.date; // YYYY-MM-DD

                // Check for weather risk (if task is within 14 day forecast)
                const dayWeather = weatherForecast.find(w => w.date === dateStr);
                const isRisky = dayWeather && dayWeather.rain > 10; // > 10mm is heavy for spraying/fertilizing

                stageEl.innerHTML = `
                    <div class="flex sm:flex-col items-center mb-4 sm:mb-0">
                        <div class="w-12 h-12 rounded-2xl bg-white dark:bg-emerald-950 border-4 border-emerald-100 dark:border-emerald-900 group-hover/stage:border-emerald-500 flex items-center justify-center text-emerald-950 dark:text-white font-black z-10 transition-colors duration-300 shadow-sm ${isRisky ? 'border-amber-500' : ''}">
                            ${i + 1}
                        </div>
                        <div class="ml-4 sm:ml-0 sm:mt-2 text-[10px] font-black text-emerald-500/40 uppercase tracking-widest hidden sm:block">${dayLabel} ${stage.days_from_start}</div>
                    </div>
                    <div class="flex-1 bg-white dark:bg-[#0d2018] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2.5rem] p-8 group-hover/stage:border-emerald-500 transition-all duration-500 shadow-xl shadow-emerald-950/5 relative overflow-hidden">
                        ${isRisky ? `
                        <div class="absolute top-0 right-0 px-6 py-2 bg-amber-500 text-amber-950 text-[9px] font-black uppercase tracking-widest transform rotate-45 translate-x-8 translate-y-2 shadow-lg">
                            ${t('Weather Risk')}
                        </div>
                        ` : ''}
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                            <div>
                                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">
                                    ${formattedDate}
                                </div>
                                <h4 class="text-3xl font-black text-emerald-950 dark:text-white leading-none">${stageName}</h4>
                            </div>
                            <div class="flex items-center">
                                <span class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-black rounded-2xl border border-emerald-100 dark:border-emerald-800 uppercase tracking-widest">
                                    ${dayRange}
                                </span>
                            </div>
                        </div>
                        
                        ${isRisky ? `
                        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-100 dark:border-amber-900 rounded-2xl flex items-center gap-4 animate-pulse">
                            <i data-lucide="cloud-rain" class="w-5 h-5 text-amber-600"></i>
                            <p class="text-xs font-bold text-amber-900 dark:text-amber-200">${t('Heavy rain expected on this date (')} ${dayWeather.rain}mm). ${t('Consider delaying spraying or fertilizer by 1-2 days.')}</p>
                        </div>
                        ` : ''}

                        <div class="flex gap-6 mb-8 items-start">
                            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl flex items-center justify-center shrink-0">
                                <i data-lucide="${stage.icon || 'info'}" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <div class="space-y-4 flex-1">
                                <p class="text-lg font-bold text-emerald-900/80 dark:text-emerald-100 leading-relaxed">${stageAdvice}</p>
                                ${(stage.urea_kg > 0 || stage.tsp_kg > 0 || stage.mop_kg > 0) ? `
                                <div class="flex flex-wrap gap-2">
                                    ${stage.urea_kg > 0 ? `<span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-[10px] font-black rounded-lg border border-blue-100 dark:border-blue-800 uppercase tracking-tighter">Urea: ${Math.round(stage.urea_kg * (data.land_size_acres || 1) * 10) / 10}kg</span>` : ''}
                                    ${stage.tsp_kg > 0 ? `<span class="px-3 py-1.5 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 text-[10px] font-black rounded-lg border border-orange-100 dark:border-orange-800 uppercase tracking-tighter">TSP: ${Math.round(stage.tsp_kg * (data.land_size_acres || 1) * 10) / 10}kg</span>` : ''}
                                    ${stage.mop_kg > 0 ? `<span class="px-3 py-1.5 bg-purple-50 dark:bg-blue-900/20 text-purple-700 dark:text-purple-400 text-[10px] font-black rounded-lg border border-purple-100 dark:border-purple-800 uppercase tracking-tighter">MOP: ${Math.round(stage.mop_kg * (data.land_size_acres || 1) * 10) / 10}kg</span>` : ''}
                                </div>
                                ` : ''}
                            </div>
                        </div>

                        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100/50 dark:border-emerald-800/50 rounded-3xl p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
                                <div class="text-[10px] font-black text-emerald-900/40 dark:text-emerald-500/40 uppercase tracking-[0.2em]">${t('Key Targets')}</div>
                            </div>
                            <div class="text-sm text-emerald-900/70 dark:text-emerald-100/70 font-bold italic leading-relaxed pl-4">
                                ${stageDesc || t('Follow standard cultivation guidelines.')}
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(stageEl);
            });
            if (window.lucide) lucide.createIcons();
        };

        fetchWeatherIntelligence().then(drawStages);
    }

    async function fetchWeatherAndRecommendDate() {
        const loading = document.getElementById('aiDateLoading');
        const result = document.getElementById('aiDateResult');
        const display = document.getElementById('recDateDisplay');
        const hiddenInput = document.getElementById('recDateValue');
        const reason = document.getElementById('recReason');
        const icon = document.getElementById('recIcon');

        loading.classList.remove('hidden');
        result.classList.add('hidden');

        try {
            // Get user location or default to Colombo
            let lat = 6.9271, lon = 79.8612;

            // Check for cached location first
            const cached = localStorage.getItem('agriassist_cached_location');
            let positionAcquired = false;

            if (cached) {
                const data = JSON.parse(cached);
                const isFresh = (Date.now() - data.timestamp) < (24 * 60 * 60 * 1000);
                if (isFresh) {
                    lat = data.lat;
                    lon = data.lon;
                    positionAcquired = true;
                    console.log("Planner: Using cached location");
                }
            }

            if (!positionAcquired && navigator.geolocation) {
                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    });
                }).catch(() => null);
                if (pos) {
                    lat = pos.coords.latitude;
                    lon = pos.coords.longitude;
                }
            }

            const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,precipitation_probability_max,precipitation_sum&timezone=auto&forecast_days=14`;
            const res = await fetch(weatherUrl);
            const data = await res.json();

            let bestDayIndex = 0;
            let bestScore = -1000;
            let bestReason = t('Optimal Conditions');
            let bestIcon = 'sun';

            for (let i = 0; i < 14; i++) {
                let score = 100;
                const temp = data.daily.temperature_2m_max[i];
                const prob = data.daily.precipitation_probability_max[i];
                const sum = data.daily.precipitation_sum[i];

                // Penalize extreme heat
                if (temp > 35) score -= 50;
                else if (temp > 32) score -= 20;

                // Penalize heavy rain
                if (sum > 20) score -= 60;
                else if (sum > 10) score -= 30;
                else if (prob > 70) score -= 20;

                // Bonus for moderate conditions
                if (temp >= 24 && temp <= 30 && sum < 5) score += 20;

                if (score > bestScore) {
                    bestScore = score;
                    bestDayIndex = i;
                }
            }

            const recDate = new Date(data.daily.time[bestDayIndex]);
            const tempAtRec = data.daily.temperature_2m_max[bestDayIndex];
            const rainAtRec = data.daily.precipitation_sum[bestDayIndex];

            if (rainAtRec > 10) {
                bestReason = t('Wait for Rain');
                bestIcon = 'cloud-rain';
            } else if (tempAtRec > 33) {
                bestReason = t('High Heat Alert');
                bestIcon = 'thermometer';
            } else {
                bestReason = t('Optimal Conditions');
                bestIcon = 'check-circle';
            }

            aiRecommendedDate = data.daily.time[bestDayIndex];
            const dateLocale = 'en-LK';
            display.textContent = recDate.toLocaleDateString(dateLocale, { dateStyle: 'long' });
            hiddenInput.value = aiRecommendedDate;
            reason.textContent = bestReason;
            icon.setAttribute('data-lucide', bestIcon);

            loading.classList.add('hidden');
            result.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();

        } catch (e) {
            console.error('Weather analysis failed:', e);
            loading.classList.add('hidden');
            // Fallback to today
            const today = new Date().toISOString().split('T')[0];
            display.textContent = new Date().toLocaleDateString('en-LK', { dateStyle: 'long' });
            hiddenInput.value = today;
            result.classList.remove('hidden');
        }
    }

    function initPlanner() {
        const detectBtn = document.getElementById('detectLocationBtn');
        const step1NextBtn = document.getElementById('step1NextBtn');

        detectBtn?.addEventListener('click', async function () {
            const btn = this;
            const originalText = btn.innerHTML;

            if (!navigator.geolocation) {
                alert(t('Geolocation is not supported or is blocked by your browser. Please select soil manually.'));
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></div> ${t('Detecting...')}`;

            const handleSuccess = async (lat, lon) => {
                try {
                    const res = await fetch('/api/soil-type', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ lat, lon })
                    });
                    const data = await res.json();
                    currentSoilType = data.soil_type;

                    let matched = false;
                    document.querySelectorAll('.soil-btn').forEach(soilBtn => {
                        const normalised = soilBtn.dataset.soil.toLowerCase().replace(/[_ -]/g, '');
                        const detected = currentSoilType.toLowerCase().replace(/[_ -]/g, '');
                        if (normalised === detected) {
                            soilBtn.click();
                            matched = true;
                        }
                    });
                    if (!matched && step1NextBtn) {
                        step1NextBtn.disabled = false;
                    }

                    btn.innerHTML = `<i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> ${t('Detected')}: ${currentSoilType}`;
                    btn.classList.remove('bg-emerald-600');
                    btn.classList.add('bg-emerald-500');
                    if (window.lucide) lucide.createIcons();
                } catch (e) {
                    console.error('Soil Detection API Error:', e);
                    alert(t('Could not determine soil type. Please select manually.'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    if (window.lucide) lucide.createIcons();
                }
            };

            const fallbackToIp = async () => {
                try {
                    const res = await fetch('https://get.geojs.io/v1/ip/geo.json');
                    const data = await res.json();
                    if (data.latitude && data.longitude) {
                        console.log("Geolocation fallback: Using IP location");
                        await handleSuccess(parseFloat(data.latitude), parseFloat(data.longitude));
                        return true;
                    }
                } catch (e) {
                    console.error("IP Geolocation fallback failed:", e);
                }
                return false;
            };

            try {
                navigator.geolocation.getCurrentPosition(
                    (pos) => handleSuccess(pos.coords.latitude, pos.coords.longitude),
                    async (err) => {
                        console.warn('Location access failed. Attempting IP fallback...', err);
                        const fallbackSuccess = await fallbackToIp();
                        if (!fallbackSuccess) {
                            let msg = t('Location access failed. Please select soil manually.');
                            switch (err.code) {
                                case 1: msg = t('Permission Denied'); break;
                                case 2: msg = t('Position Unavailable'); break;
                                case 3: msg = t('Timeout'); break;
                            }
                            alert(msg);
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            if (window.lucide) lucide.createIcons();
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } catch (e) {
                console.error('Geolocation call failed:', e);
                alert('Geolocation failed unexpectedly. Please select soil manually.');
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (window.lucide) lucide.createIcons();
            }
        });

        document.querySelectorAll('.soil-btn').forEach(b => {
            b.addEventListener('click', function () {
                document.querySelectorAll('.soil-btn').forEach(btn => {
                    btn.classList.remove('border-emerald-500', 'bg-emerald-100', 'dark:bg-emerald-900/30');
                    const mark = btn.querySelector('.soil-check-mark');
                    if (mark) { mark.classList.add('hidden'); mark.classList.remove('flex'); }
                });

                this.classList.add('border-emerald-500', 'bg-emerald-100', 'dark:bg-emerald-900/30');
                const mark = this.querySelector('.soil-check-mark');
                if (mark) { mark.classList.remove('hidden'); mark.classList.add('flex'); }

                currentSoilType = this.dataset.soil;
                if (step1NextBtn) step1NextBtn.disabled = false;
            });
        });

        step1NextBtn?.addEventListener('click', () => showStep(2));

        const manualCrop = document.getElementById('manualCropId');
        const manualVariety = document.getElementById('manualVarietyId');
        const manualProceed = document.getElementById('manualProceedBtn');
        const customCropContainer = document.getElementById('customCropContainer');
        const customVarietyContainer = document.getElementById('customVarietyContainer');
        const customCropInput = document.getElementById('customCropName');
        const customVarietyInput = document.getElementById('customVarietyName');

        manualCrop?.addEventListener('change', async function () {
            const cropId = this.value;

            manualVariety.innerHTML = '<option value="" class="bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white">' + t('Seed Type') + '</option>';
            manualVariety.disabled = true;
            manualProceed.disabled = true;
            customCropContainer?.classList.add('hidden');
            customVarietyContainer?.classList.add('hidden');
            manualVariety.classList.remove('hidden');

            if (cropId === 'other') {
                customCropContainer?.classList.remove('hidden');
                manualVariety.classList.add('hidden');
                selectedVarietyId = 'other';
                manualProceed.disabled = !customCropInput?.value.trim();
                return;
            }

            if (!cropId) return;

            try {
                const res = await fetch('/crops/' + cropId + '/varieties');
                const varieties = await res.json();
                varieties.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    const vName = locale === 'si' ? (v.variety_name_si || v.variety_name) : (locale === 'ta' ? (v.variety_name_ta || v.variety_name) : v.variety_name);
                    opt.textContent = vName;
                    opt.className = "bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white";
                    manualVariety.appendChild(opt);
                });

                const otherOpt = document.createElement('option');
                otherOpt.value = 'other';
                otherOpt.textContent = t('Other (Custom Seed)');
                otherOpt.className = "bg-white dark:bg-emerald-950 text-emerald-900 dark:text-white";
                manualVariety.appendChild(otherOpt);

                manualVariety.disabled = false;
            } catch (e) {
                console.error('Failed to load varieties:', e);
            }
        });

        customCropInput?.addEventListener('input', function () {
            if (manualCrop.value === 'other') {
                manualProceed.disabled = !this.value.trim();
            }
        });

        manualVariety?.addEventListener('change', function () {
            const val = this.value;
            customVarietyContainer?.classList.add('hidden');

            if (val === 'other') {
                customVarietyContainer?.classList.remove('hidden');
                selectedVarietyId = 'other';
                manualProceed.disabled = !customVarietyInput?.value.trim();
            } else {
                selectedVarietyId = val;
                manualProceed.disabled = !selectedVarietyId;
            }
        });

        customVarietyInput?.addEventListener('input', function () {
            if (manualVariety.value === 'other') {
                manualProceed.disabled = !this.value.trim();
            }
        });

        manualProceed?.addEventListener('click', () => {
            if (selectedVarietyId) showStep(3);
        });

        const methodManualBtn = document.getElementById('methodManualBtn');
        const methodAiBtn = document.getElementById('methodAiBtn');
        const manualDateInput = document.getElementById('manualDateInput');
        const aiDateRecommendation = document.getElementById('aiDateRecommendation');

        methodManualBtn?.addEventListener('click', () => {
            planningMethod = 'manual';
            methodManualBtn.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');

            const manCircle = methodManualBtn.querySelector('div.rounded-full');
            if (manCircle) {
                manCircle.classList.remove('border-2', 'border-emerald-200', 'dark:border-emerald-800');
                manCircle.classList.add('border-4', 'border-emerald-500', 'bg-emerald-500');
            }

            methodAiBtn.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
            const aiCircle = methodAiBtn.querySelector('div.rounded-full');
            if (aiCircle) {
                aiCircle.classList.remove('border-4', 'border-emerald-500', 'bg-emerald-500');
                aiCircle.classList.add('border-2', 'border-emerald-200', 'dark:border-emerald-800');
            }

            manualDateInput.classList.remove('hidden');
            aiDateRecommendation.classList.add('hidden');
        });

        methodAiBtn?.addEventListener('click', () => {
            planningMethod = 'ai';
            methodAiBtn.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');

            const aiCircle = methodAiBtn.querySelector('div.rounded-full');
            if (aiCircle) {
                aiCircle.classList.remove('border-2', 'border-emerald-200', 'dark:border-emerald-800');
                aiCircle.classList.add('border-4', 'border-emerald-500', 'bg-emerald-500');
            }

            methodManualBtn.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
            const manCircle = methodManualBtn.querySelector('div.rounded-full');
            if (manCircle) {
                manCircle.classList.remove('border-4', 'border-emerald-500', 'bg-emerald-500');
                manCircle.classList.add('border-2', 'border-emerald-200', 'dark:border-emerald-800');
            }

            manualDateInput.classList.add('hidden');
            aiDateRecommendation.classList.remove('hidden');

            if (!aiRecommendedDate) {
                fetchWeatherAndRecommendDate();
            }
        });

        const genRoadmapBtn = document.getElementById('generateRoadmapBtn');
        genRoadmapBtn?.addEventListener('click', async () => {
            if (!selectedVarietyId) return;

            const roadmapConfig = document.getElementById('roadmapConfig');
            const loading = document.getElementById('roadmapLoading');
            const resultCard = document.getElementById('resultCard');

            roadmapConfig.classList.add('hidden');
            loading.classList.remove('hidden');

            try {
                const customName = document.getElementById('customCropName')?.value;
                const customVariety = document.getElementById('customVarietyName')?.value;
                const plantingDate = planningMethod === 'ai' ? document.getElementById('recDateValue').value : document.getElementById('roadmapDate').value;

                const landSize = document.getElementById('landSize')?.value || 1.0;
                const landUnit = document.getElementById('landUnit')?.value || 'Acres';
                
                // Get cached location for weather/pest intelligence
                const cached = localStorage.getItem('agriassist_cached_location');
                let lat = null, lon = null;
                if (cached) {
                    const loc = JSON.parse(cached);
                    lat = loc.lat;
                    lon = loc.lon;
                }

                const bodyJson = {
                    crop_variety_id: selectedVarietyId,
                    planting_date: plantingDate,
                    land_size: landSize,
                    land_unit: landUnit,
                    lat: lat,
                    lon: lon,
                    district: document.getElementById('districtPicker')?.value,
                    lang: locale
                };

                if (manualCrop.value === 'other') {
                    bodyJson.custom_crop_name = customName;
                } else {
                    bodyJson.manual_crop_id = manualCrop.value;
                }

                if (manualVariety.value === 'other') {
                    bodyJson.custom_variety_name = customVariety;
                }

                const res = await fetch('/api/crop-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(bodyJson)
                });

                const data = await res.json();
                lastRoadmapData = data;
                renderRoadmap(data);

                loading.classList.add('hidden');
                resultCard.classList.remove('hidden');
            } catch (e) {
                console.error('Roadmap generation failed:', e);
                loading.classList.add('hidden');
                roadmapConfig.classList.remove('hidden');
                alert(t('Error generating roadmap. Please try again.'));
            }
        });

        document.getElementById('restartWizardBtn')?.addEventListener('click', () => {
            location.reload();
        });

        document.getElementById('backToStep2FromConfig')?.addEventListener('click', () => {
            showStep(2);
        });

        // Save Plan Logic
        const savePlanBtn = document.getElementById('savePlanBtn');
        const saveFarmId = document.getElementById('saveFarmId');
        
        savePlanBtn?.addEventListener('click', async () => {
            if (!saveFarmId || !saveFarmId.value) {
                alert(t('Please select a land to save this plan to.'));
                return;
            }

            if (!lastRoadmapData) return;

            const originalBtnHtml = savePlanBtn.innerHTML;
            savePlanBtn.disabled = true;
            savePlanBtn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 mr-3 animate-spin"></i> ${t('Saving...')}`;
            if (window.lucide) lucide.createIcons();

            try {
                const res = await fetch('/api/save-crop-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') // Fallback
                    },
                    body: JSON.stringify({
                        farm_id: saveFarmId.value,
                        crop: lastRoadmapData.crop,
                        variety: lastRoadmapData.variety,
                        planting_date: lastRoadmapData.planting_date,
                        estimated_harvest: lastRoadmapData.estimated_harvest,
                        stages: lastRoadmapData.stages
                    })
                });

                const result = await res.json();
                if (result.success) {
                    savePlanBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                    savePlanBtn.classList.add('bg-emerald-500', 'cursor-not-allowed');
                    savePlanBtn.innerHTML = `<i data-lucide="check-circle" class="w-5 h-5 mr-3"></i> ${t('Plan Saved')}`;
                    if (window.showToast) window.showToast('Cultivation roadmap saved securely.', 'success');
                } else {
                    throw new Error(result.message || 'Failed to save.');
                }
            } catch (e) {
                console.error(e);
                alert(t('An error occurred while saving the plan.'));
                savePlanBtn.disabled = false;
                savePlanBtn.innerHTML = originalBtnHtml;
            }
            if (window.lucide) lucide.createIcons();
        });

        const districtPicker = document.getElementById('districtPicker');
        districtPicker?.addEventListener('change', async function () {
            const district = this.value;
            if (!district) return;

            try {
                const res = await fetch('/api/soil-by-district?district=' + district);
                const data = await res.json();
                currentSoilType = data.soil_type;

                let matched = false;
                document.querySelectorAll('.soil-btn').forEach(soilBtn => {
                    const normalised = soilBtn.dataset.soil.toLowerCase().replace(/[_ -]/g, '');
                    const detected = currentSoilType.toLowerCase().replace(/[_ -]/g, '');
                    if (normalised === detected) {
                        soilBtn.click();
                        matched = true;
                    }
                });
                if (!matched && step1NextBtn) {
                    step1NextBtn.disabled = false;
                }
            } catch (e) {
                console.error('District soil lookup failed:', e);
            }
        });

        updateStaticTranslations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlanner);
    } else {
        initPlanner();
    }
})();
