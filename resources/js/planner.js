/**
 * AgriAssist Cultivation Planner (Wizard)
 * ---------------------------------------
 * Handles the multi-step lifecycle of precision agricultural planning, 
 * including soil detection, crop suggestion, and AI roadmap generation.
 * 
 * Uses Alpine.js-style global state orchestration for real-time UI transitions.
 */

(function () {
    // --- State Management ---
    let currentStep = 1;
    let currentSoilType = '';
    let selectedVarietyId = '';
    let lastSuggestionsData = null;
    let lastRoadmapData = null;
    let lastAiSeedSuggestions = null;
    let planningMethod = 'manual'; // enum: ['manual', 'ai']
    let aiRecommendedDate = null;
    let selectedLandId = null;

    // --- Configuration Manifest ---
    const config = window.__PLANNER_CONFIG || {};
    const csrf = config.csrf;
    let locale = config.locale || 'en';
    let translations = config.translations || {};

    /**
     * Globally accessible land selection handler for the "Quick Start" feature.
     * Pre-fills environmental telemetry from a registered land profile.
     * 
     * @param {Object} land - Eloquent-mapped Farm object.
     */
    window.selectLandQuickStart = function(land) {
        selectedLandId = land.id;
        currentSoilType = land.soil_type;
        
        // UI Synchronization: Simulate button click for visual consistency
        document.querySelectorAll('.soil-btn').forEach(soilBtn => {
            const normalised = soilBtn.dataset.soil.toLowerCase().replace(/[_ -]/g, '');
            const detected = currentSoilType.toLowerCase().replace(/[_ -]/g, '');
            if (normalised === detected) {
                soilBtn.click();
            }
        });

        // Land Geometry: Load registered acreage
        if (land.farm_size) {
            const parts = land.farm_size.split(' ');
            const sizeVal = parseFloat(parts[0]);
            const sizeUnit = parts[1] || 'Acres';
            
            const sizeInput = document.getElementById('landSize');
            const unitSelect = document.getElementById('landUnit');
            if (sizeInput) sizeInput.value = sizeVal;
            if (unitSelect) unitSelect.value = sizeUnit;
        }

        // Persistence: Pre-select this land in the final Step 3 save dropdown
        const saveFarmId = document.getElementById('saveFarmId');
        if (saveFarmId) saveFarmId.value = land.id;

        if (window.showToast) window.showToast(`Intelligence loaded for ${land.farm_name}`, 'info');

        // Transitions: Auto-advance to Step 2
        setTimeout(() => { showStep(2); }, 800);
    };

    /**
     * Switches the application locale and re-triggers rendering of current step data.
     * 
     * @param {string} newLang - ISO 639-1 language code.
     */
    window.switchLanguage = window.switchLanguageTo = function (newLang) {
        locale = newLang;
        updateStaticTranslations();

        if (currentStep === 2) {
            if (lastSuggestionsData) renderSuggestions(lastSuggestionsData);
            if (lastAiSeedSuggestions) renderAiVarieties(lastAiSeedSuggestions);
        } else if (currentStep === 3 && lastRoadmapData) {
            renderRoadmap(lastRoadmapData);
        }

        // Backend Sync: Ensure session locale persists across reloads
        fetch("/lang/" + newLang + "?json=1", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(e => console.error('Locale synchronization failed:', e));
    };

    /**
     * Localized string retriever.
     * 
     * @param {string} key - The dictionary key.
     * @returns {string} The translated string or the key if missing.
     */
    function t(key) {
        if (translations[locale] && translations[locale][key]) {
            return translations[locale][key];
        }
        return key;
    }

    /**
     * Reflects the current locale across all static DOM elements marked with [data-t-key].
     */
    function updateStaticTranslations() {
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

        // Component specific manual translations
        ['Soil Type', 'Crop Selection', 'Cultivation Roadmap'].forEach((step, i) => {
            const label = document.getElementById('step-label-' + (i + 1));
            if (label) label.textContent = t(step);
        });

        if (window.lucide) lucide.createIcons();
    }

    /**
     * Long-polling implementation to track the asynchronous roadmap generation progress.
     * 
     * @param {string} jobId - UUID of the generation task.
     */
    async function pollStatus(jobId) {
        const loadingStatus = document.getElementById('roadmapLoadingStatus');
        const roadmapConfig = document.getElementById('roadmapConfig');
        const loading = document.getElementById('roadmapLoading');
        const resultCard = document.getElementById('resultCard');
        const genBtn = document.getElementById('generateRoadmapBtn');

        let attempts = 0;
        let interval = 2000; // Starting backoff
        const maxTime = 180000; // 3 minute terminal timeout
        const startTime = Date.now();

        while (attempts < 60 && (Date.now() - startTime) < maxTime) {
            attempts++;
            
            // Progressive Status Messaging
            if (attempts > 6) loadingStatus.textContent = t('Finalizing Details...');
            else if (attempts > 3) loadingStatus.textContent = t('Validating Roadmap...');
            else loadingStatus.textContent = t('Consulting AI...');

            try {
                const res = await fetch(`/api/planner/status/${jobId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data.status === 'completed' && data.result) {
                    lastRoadmapData = data.result;
                    renderRoadmap(lastRoadmapData);
                    loading.classList.add('hidden');
                    resultCard.classList.remove('hidden');
                    if (genBtn) genBtn.disabled = false;
                    return;
                } else if (data.status === 'failed') {
                    throw new Error(data.error || t('Generation failed.'));
                }

                await new Promise(resolve => setTimeout(resolve, interval));
                interval = Math.min(interval * 1.5, 5000); // Exponential Backoff
            } catch (e) {
                loading.classList.add('hidden');
                roadmapConfig.classList.remove('hidden');
                alert(e.message || t('Error generating roadmap.'));
                if (genBtn) genBtn.disabled = false;
                return;
            }
        }

        loading.classList.add('hidden');
        roadmapConfig.classList.remove('hidden');
        alert(t('Generation timed out.'));
        if (genBtn) genBtn.disabled = false;
    }

    /**
     * Orchestrates wizard step transitions and progress bar updates.
     * 
     * @param {number} step - Step ID (1-3).
     */
    window.showStep = function (step) {
        currentStep = step;
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('hidden'));
        document.getElementById('step' + step).classList.remove('hidden');

        // Update progress bar
        const progressBar = document.getElementById('progressBar');
        if (progressBar) progressBar.style.width = ((step - 1) / 2 * 100) + '%';

        // Step dot colors
        for (let i = 1; i <= 3; i++) {
            const dot = document.getElementById('step-dot-' + i);
            const label = document.getElementById('step-label-' + i);
            if (i < step) {
                dot.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
                label.classList.add('text-emerald-600', 'dark:text-emerald-400');
            } else if (i === step) {
                dot.classList.add('border-emerald-500', 'text-emerald-600');
                label.classList.add('text-emerald-600', 'dark:text-emerald-400');
            } else {
                dot.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500', 'text-emerald-600');
                label.classList.remove('text-emerald-600', 'dark:text-emerald-400');
            }
        }

        // Step 2 Trigger: Load context-aware suggestions upon entry
        if (step === 2 && !lastSuggestionsData) loadSuggestions();
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (window.lucide) lucide.createIcons();
    };

    /**
     * Fetches rank-ordered crop suggestions based on active soil telemetry.
     */
    async function loadSuggestions() {
        const grid = document.getElementById('suggestionsGrid');
        const loading = document.getElementById('suggestionsLoading');
        if (!grid || !loading) return;

        grid.classList.add('hidden');
        loading.classList.remove('hidden');

        console.log('Loading suggestions for soil:', currentSoilType);

        if (!currentSoilType) {
            console.warn('Soil type is missing, aborting suggestion load.');
            loading.classList.add('hidden');
            grid.innerHTML = `<div class="col-span-full p-20 text-amber-500 font-bold text-center">${t('Please go back and select a soil type.')}</div>`;
            grid.classList.remove('hidden');
            return;
        }

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
                    locale: locale
                })
            });
            
            if (!res.ok) {
                throw new Error(`API error: ${res.status}`);
            }

            const data = await res.json();
            console.log('Suggestions received:', data);
            lastSuggestionsData = data;
            renderSuggestions(data);

            loading.classList.add('hidden');
            grid.classList.remove('hidden');
        } catch (e) {
            console.error('Failed to load suggestions:', e);
            loading.classList.add('hidden');
            grid.innerHTML = `<div class="col-span-full p-20 text-red-500 font-bold text-center">${t('Connection lost.')}</div>`;
            grid.classList.remove('hidden');
        }
    }

    function refreshRevealObserver() {
        if (window.revealObserver) {
            document.querySelectorAll('.reveal:not([data-observed])').forEach(el => {
                el.setAttribute('data-observed', 'true');
                window.revealObserver.observe(el);
            });
        } else {
            // Fallback: If no observer, just show them
            document.querySelectorAll('.reveal').forEach(el => el.classList.add('active'));
        }
    }

    function renderSuggestions(data) {
        const grid = document.getElementById('suggestionsGrid');
        if (!grid) return;

        grid.innerHTML = '';
        const items = data.suggestions || [];
        console.log('Rendering items count:', items.length);

        if (items.length === 0) {
            grid.innerHTML = `<div class="col-span-full p-20 text-emerald-800/40 font-bold text-center uppercase tracking-widest">${t('No direct matches found for this soil.')}</div>`;
            return;
        }

        items.forEach(item => {
            const card = document.createElement('div');
            card.className = 'p-6 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2.5rem] hover:border-emerald-500 transition-all cursor-pointer group reveal relative overflow-hidden';
            
            const name = locale === 'si' ? item.crop_name_si : (locale === 'ta' ? item.crop_name_ta : item.crop_name);
            const vName = locale === 'si' ? item.variety_name_si : (locale === 'ta' ? item.variety_name_ta : item.variety_name);

            card.innerHTML = `
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <i data-lucide="leaf" class="w-6 h-6"></i>
                    </div>
                    <div class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">
                        ${item.suitability}% Match
                    </div>
                </div>
                <h4 class="text-xl font-black text-emerald-950 dark:text-white mb-1 relative z-10">${name}</h4>
                <p class="text-xs font-bold text-emerald-600/60 uppercase tracking-widest mb-4 relative z-10">${vName}</p>
                <div class="flex items-center text-xs font-bold text-emerald-900/40 dark:text-emerald-500/40 space-x-4 relative z-10">
                    <span class="flex items-center"><i data-lucide="clock" class="w-3 h-3 mr-1"></i> ${item.growth_days} Days</span>
                    <span class="flex items-center"><i data-lucide="banknote" class="w-3 h-3 mr-1"></i> Rs.${item.price_per_kg_lkr}/kg</span>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i data-lucide="leaf" class="w-24 h-24 text-emerald-600 rotate-12"></i>
                </div>
            `;

            card.onclick = () => {
                selectedVarietyId = item.variety_id;
                planningMethod = 'manual';
                showStep(3);
            };
            grid.appendChild(card);
        });

        refreshRevealObserver();

        if (window.lucide) {
            try { lucide.createIcons(); } catch(e) { console.error('Lucide error:', e); }
        }
    }

    /**
     * Renders highly detailed, granular cultivation stages with weather risk overlays.
     * 
     * @param {Object} data - Processed roadmap payload.
     */
    function renderRoadmap(data) {
        const container = document.getElementById('roadmapContainer');
        if (!container) return;

        container.innerHTML = '';
        
        // Estimates & Metrics Scaling
        if (data.estimates) {
            document.getElementById('estSeeds').textContent = data.estimates.seeds_kg || 0;
            document.getElementById('estUrea').textContent = data.estimates.urea_kg || 0;
            document.getElementById('estTsp').textContent = data.estimates.tsp_kg || 0;
            document.getElementById('estMop').textContent = data.estimates.mop_kg || 0;
            document.getElementById('estYield').textContent = data.estimates.expected_yield_kg || 0;
            document.getElementById('estRevenue').textContent = new Intl.NumberFormat().format(data.estimates.estimated_revenue || 0);
            
            // Business Rule: Highlight yields adjusted for diagnosed farm health
            const healthBadge = document.getElementById('healthBadge');
            const healthVal = document.getElementById('healthVal');
            if (healthBadge && healthVal) {
                const score = data.health_score || 100;
                if (score < 100) {
                    healthVal.textContent = score;
                    healthBadge.classList.remove('hidden');
                } else healthBadge.classList.add('hidden');
            }
        }

        const cropName = locale === 'si' ? data.crop_name_si : (locale === 'ta' ? data.crop_name_ta : data.crop);
        const varietyName = locale === 'si' ? data.variety_name_si : (locale === 'ta' ? data.variety_name_ta : data.variety);
        
        document.getElementById('resCropName').textContent = cropName;
        document.getElementById('resVarietyName').textContent = varietyName;

        data.stages.forEach((stage, idx) => {
            const stageName = locale === 'si' ? stage.name_si : (locale === 'ta' ? stage.name_ta : stage.name);
            const stageAdvice = locale === 'si' ? stage.advice_si : (locale === 'ta' ? stage.advice_ta : stage.advice);
            const stageDesc = locale === 'si' ? stage.description_si : (locale === 'ta' ? stage.description_ta : stage.description);

            const div = document.createElement('div');
            div.className = 'relative pl-12 pb-12 group last:pb-0 reveal';
            div.style.transitionDelay = (idx * 100) + 'ms';

            div.innerHTML = `
                ${idx < data.stages.length - 1 ? '<div class="absolute left-[19px] top-10 bottom-0 w-1 bg-emerald-100 dark:bg-emerald-900 group-hover:bg-emerald-500 transition-colors"></div>' : ''}
                <div class="absolute left-0 top-0 w-10 h-10 bg-white dark:bg-[#0d2018] border-4 border-emerald-100 dark:border-emerald-900 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:border-emerald-500 transition-all z-10">
                    <i data-lucide="${stage.icon || 'sprout'}" class="w-5 h-5"></i>
                </div>
                <div class="bg-white dark:bg-[#081811] border-2 border-emerald-50 dark:border-emerald-900 p-6 rounded-[2rem] shadow-sm group-hover:shadow-md group-hover:border-emerald-100 transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h5 class="text-xl font-black text-emerald-950 dark:text-white tracking-tight">${stageName}</h5>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600/40">${stage.date}</span>
                    </div>
                    <p class="text-emerald-700 dark:text-emerald-400 font-bold mb-4">${stageAdvice}</p>
                    <div class="p-4 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100/50 dark:border-emerald-900/50 mb-4">
                        <p class="text-sm text-emerald-900/70 dark:text-emerald-100/60 leading-relaxed">${stageDesc}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${stage.urea_kg > 0 ? `<span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-900">Urea: ${stage.urea_kg}kg</span>` : ''}
                        ${stage.tsp_kg > 0 ? `<span class="px-3 py-1 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-orange-100 dark:border-orange-900">TSP: ${stage.tsp_kg}kg</span>` : ''}
                        ${stage.mop_kg > 0 ? `<span class="px-3 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-purple-100 dark:border-purple-900">MOP: ${stage.mop_kg}kg</span>` : ''}
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
        
        refreshRevealObserver();
        
        if (window.lucide) lucide.createIcons();
    }

    /**
     * Initialization routine.
     */
    function initPlanner() {
        // --- Geolocation Logic ---
        document.getElementById('detectLocationBtn')?.addEventListener('click', async function () {
            const btn = this;
            const originalContent = btn.innerHTML;
            const errorBanner = document.getElementById('geoErrorBanner');
            const errorText = document.getElementById('geoErrorText');

            const hideError = () => errorBanner?.classList.add('hidden');
            const showError = (msg) => {
                if (errorText) errorText.textContent = msg;
                errorBanner?.classList.remove('hidden');
            };

            hideError();
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-6 h-6 mr-3 animate-spin"></i> <span>${t('Detecting...')}</span>`;
            if (window.lucide) lucide.createIcons();

            const fallbackToIp = async () => {
                try {
                    const res = await fetch('https://get.geojs.io/v1/ip/geo.json');
                    const data = await res.json();
                    if (data.latitude && data.longitude) {
                        const soilRes = await fetch(`/api/soil-by-district?district=${data.region || 'Colombo'}`);
                        const soilData = await soilRes.json();
                        currentSoilType = soilData.soil_type;
                        
                        document.querySelectorAll('.soil-btn').forEach(b => {
                            if (b.dataset.soil === currentSoilType) b.click();
                        });

                        if (window.showToast) window.showToast(t('Location detected via IP Network'), 'info');
                        return true;
                    }
                } catch (e) {}
                return false;
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        try {
                            const { latitude, longitude } = pos.coords;
                            const res = await fetch(`/api/proxy/geocode?lat=${latitude}&lon=${longitude}`);
                            const data = await res.json();
                            const district = data.address?.city || data.address?.town || data.address?.district;
                            
                            const soilRes = await fetch(`/api/soil-by-district?district=${district}`);
                            const soilData = await soilRes.json();
                            currentSoilType = soilData.soil_type;

                            document.querySelectorAll('.soil-btn').forEach(b => {
                                if (b.dataset.soil === currentSoilType) b.click();
                            });

                            if (window.showToast) window.showToast(t('Soil type detected: ') + currentSoilType, 'success');
                        } catch (e) {
                            showError(t('Failed to parse location data.'));
                        } finally {
                            btn.disabled = false;
                            btn.innerHTML = originalContent;
                            if (window.lucide) lucide.createIcons();
                        }
                    },
                    async (err) => {
                        const success = await fallbackToIp();
                        if (!success) showError(t('Could not access location. Please select manually.'));
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                        if (window.lucide) lucide.createIcons();
                    },
                    { timeout: 10000 }
                );
            } else {
                const success = await fallbackToIp();
                if (!success) showError(t('Geolocation not supported.'));
                btn.disabled = false;
                btn.innerHTML = originalContent;
                if (window.lucide) lucide.createIcons();
            }
        });

        // --- District Picker Fallback ---
        document.getElementById('districtPicker')?.addEventListener('change', async function() {
            if (!this.value) return;
            try {
                const res = await fetch(`/api/soil-by-district?district=${this.value}`);
                const data = await res.json();
                currentSoilType = data.soil_type;
                document.querySelectorAll('.soil-btn').forEach(b => {
                    if (b.dataset.soil === currentSoilType) b.click();
                });
            } catch (e) { console.error('District lookup failed'); }
        });

        // --- Selection Logic ---
        document.querySelectorAll('.soil-btn').forEach(b => {
            b.addEventListener('click', function () {
                currentSoilType = this.dataset.soil;
                document.querySelectorAll('.soil-btn').forEach(btn => {
                    btn.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'dark:bg-emerald-900/20');
                    btn.querySelector('.soil-check-mark').classList.add('hidden');
                });
                this.classList.add('border-emerald-500', 'bg-emerald-50/50', 'dark:bg-emerald-900/20');
                const mark = this.querySelector('.soil-check-mark');
                if (mark) mark.classList.remove('hidden');
                document.getElementById('step1NextBtn').disabled = false;
            });
        });

        document.getElementById('step1NextBtn')?.addEventListener('click', () => showStep(2));
        document.getElementById('step2BackBtn')?.addEventListener('click', () => showStep(1));

        // --- Roadmap Generation Entry Point ---
        document.getElementById('generateRoadmapBtn')?.addEventListener('click', async () => {
            const genBtn = document.getElementById('generateRoadmapBtn');
            const roadmapConfig = document.getElementById('roadmapConfig');
            const loading = document.getElementById('roadmapLoading');
            const landSize = document.getElementById('landSize')?.value;
            const landUnit = document.getElementById('landUnit')?.value;
            const plantingDate = document.getElementById('plantingDate')?.value;

            if (!landSize || !plantingDate) {
                alert(t('Please provide land size and planting date.'));
                return;
            }

            genBtn.disabled = true;
            roadmapConfig.classList.add('hidden');
            loading.classList.remove('hidden');

            try {
                const bodyJson = {
                    crop_variety_id: selectedVarietyId,
                    planting_date: plantingDate,
                    land_size: landSize,
                    land_unit: landUnit,
                    lang: locale,
                    farm_id: selectedLandId
                };

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
                if (data.job_id) {
                    pollStatus(data.job_id);
                } else if (data.result) {
                    lastRoadmapData = data.result;
                    renderRoadmap(data.result);
                    loading.classList.add('hidden');
                    document.getElementById('resultCard').classList.remove('hidden');
                    genBtn.disabled = false;
                } else {
                    throw new Error(data.message || t('Failed to initialize roadmap generation.'));
                }
            } catch (e) {
                loading.classList.add('hidden');
                roadmapConfig.classList.remove('hidden');
                alert(e.message || t('Error starting roadmap generation.'));
                genBtn.disabled = false;
            }
        });

        // --- Save Plan Implementation ---
        document.getElementById('savePlanBtn')?.addEventListener('click', async function() {
            const farmId = document.getElementById('saveFarmId')?.value;
            if (!farmId) {
                alert(t('Please select a land to save the plan.'));
                return;
            }

            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> ${t('Saving...')}`;
            if (window.lucide) lucide.createIcons();

            try {
                const res = await fetch('/api/save-crop-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        farm_id: farmId,
                        roadmap: lastRoadmapData
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.showToast(t('Roadmap saved to your farm profile!'), 'success');
                    setTimeout(() => { window.location.href = config.profileUrl; }, 1500);
                } else {
                    alert(data.message || t('Failed to save plan.'));
                }
            } catch (e) {
                alert(t('Communication error with server.'));
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (window.lucide) lucide.createIcons();
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
