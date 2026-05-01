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
    window.selectLandQuickStart = function (land) {
        selectedLandId = land.id;
        currentSoilType = land.soil_type;

        // CRITICAL: Enable the 'Next' button since we now have a valid soil type
        const nextBtn = document.getElementById('step1NextBtn');
        if (nextBtn) nextBtn.disabled = false;

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

    // Reflects the current locale across all static DOM elements marked with [data-t-key].
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
            if (loadingStatus) {
                if (attempts > 6) loadingStatus.textContent = t('Finalizing Details...');
                else if (attempts > 3) loadingStatus.textContent = t('Validating Roadmap...');
                else loadingStatus.textContent = t('Consulting AI...');
            }

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

    // Fetches rank-ordered crop suggestions based on active soil telemetry.
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
            card.id = `variety-card-${item.variety_id}`;
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
                // Clear selection from all cards
                document.querySelectorAll('[id^="variety-card-"]').forEach(c => {
                    c.classList.remove('border-emerald-500', 'ring-4', 'ring-emerald-500/10');
                });
                // Mark this card as selected
                card.classList.add('border-emerald-500', 'ring-4', 'ring-emerald-500/10');

                // CRITICAL: Set selectedVarietyId directly from the item, not via dropdown events
                selectedVarietyId = item.variety_id;
                console.log('Variety selected from card:', selectedVarietyId, name);

                planningMethod = 'manual';

                // Update dropdowns to reflect selection
                // Suppress standard change handler reset to preserve state
                const cropSelect = document.getElementById('manualCropId');
                if (cropSelect) {
                    cropSelect._suppressVarietyReset = true;
                    cropSelect.value = item.crop_id;
                    const event = new Event('change');
                    cropSelect.dispatchEvent(event);

                    // Delay setting variety dropdown to wait for it to be populated
                    setTimeout(() => {
                        cropSelect._suppressVarietyReset = false;
                        const varietySelect = document.getElementById('manualVarietyId');
                        if (varietySelect) {
                            varietySelect.value = item.variety_id;
                            selectedVarietyId = item.variety_id;
                        }
                    }, 600);
                }

                showStep(3);
            };
            grid.appendChild(card);
        });

        refreshRevealObserver();

        if (window.lucide) {
            try { lucide.createIcons(); } catch (e) { console.error('Lucide error:', e); }
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
            const setSafeContent = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            setSafeContent('estSeeds', data.estimates.seeds_kg || 0);
            setSafeContent('estUrea', data.estimates.urea_kg || 0);
            setSafeContent('estTsp', data.estimates.tsp_kg || 0);
            setSafeContent('estMop', data.estimates.mop_kg || 0);
            setSafeContent('estYield', data.estimates.expected_yield_kg || 0);
            setSafeContent('estRevenue', new Intl.NumberFormat().format(data.estimates.estimated_revenue || 0));

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

        // Use ID found in blade: resCropVariety
        const varietyDisplayEl = document.getElementById('resCropVariety');
        if (varietyDisplayEl) {
            varietyDisplayEl.textContent = cropName + ' (' + varietyName + ')';
        }

        // Sidebar Overview sync
        const durationEl = document.getElementById('resDuration');
        const plantDateEl = document.getElementById('resPlantDate');
        const harvestDateEl = document.getElementById('resHarvestDate');

        if (durationEl) durationEl.textContent = data.growth_days || 0;
        if (plantDateEl) plantDateEl.textContent = data.planting_date || '--';
        if (harvestDateEl) harvestDateEl.textContent = data.estimated_harvest || '--';

        // NEW: Render Pest Alerts
        const pestContainer = document.getElementById('pestAlertsContainer');
        if (pestContainer) {
            pestContainer.innerHTML = '';
            if (data.pest_alerts && data.pest_alerts.length > 0) {
                data.pest_alerts.forEach(alert => {
                    const isHigh = alert.risk_level === 'High' || alert.risk_level === 'Critical';
                    const div = document.createElement('div');
                    div.className = `p-5 rounded-3xl border-2 mb-4 flex items-start space-x-4 ${isHigh ? 'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-900 text-red-900 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-900 text-amber-900 dark:text-amber-400'}`;
                    
                    div.innerHTML = `
                        <div class="p-3 ${isHigh ? 'bg-red-100 dark:bg-red-800/40 text-red-600' : 'bg-amber-100 dark:bg-amber-800/40 text-amber-600'} rounded-2xl">
                            <i data-lucide="shield-alert" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <h4 class="font-black uppercase tracking-widest text-xs">${alert.pest_name} ${t('Threat Detected')}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase ${isHigh ? 'bg-red-600 text-white' : 'bg-amber-600 text-white'}">${alert.risk_level}</span>
                            </div>
                            <p class="text-sm font-bold mb-2">${alert.message}</p>
                            <div class="flex items-center text-[10px] font-black uppercase opacity-60">
                                <i data-lucide="info" class="w-3 h-3 mr-1"></i>
                                ${t('Action')}: ${alert.recommended_action}
                            </div>
                        </div>
                    `;
                    pestContainer.appendChild(div);
                });
                pestContainer.classList.remove('hidden');
            } else {
                pestContainer.classList.add('hidden');
            }
        }

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

    // Initialization routine.
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
                } catch (e) { }
                return false;
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        try {
                            const { latitude, longitude } = pos.coords;
                            const res = await fetch(`/proxy/geocode?lat=${latitude}&lon=${longitude}`);
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
        document.getElementById('districtPicker')?.addEventListener('change', async function () {
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
        document.getElementById('backToStep2FromConfig')?.addEventListener('click', () => showStep(2));

        // --- Manual Crop Selection Logic ---
        const manualCrop = document.getElementById('manualCropId');
        const manualVariety = document.getElementById('manualVarietyId');
        const customCropContainer = document.getElementById('customCropContainer');
        const customVarietyContainer = document.getElementById('customVarietyContainer');
        const manualProceedBtn = document.getElementById('manualProceedBtn');

        manualCrop?.addEventListener('change', async function () {
            const val = this.value;
            // Clear variety selection when custom option is toggled,
            // unless triggered programmatically
            if (!this._suppressVarietyReset) {
                selectedVarietyId = '';
            }

            manualVariety.disabled = true;
            manualVariety.innerHTML = `<option value="">-- ${t('Seed Type')} --</option>`;
            manualProceedBtn.disabled = true;
            customCropContainer.classList.add('hidden');
            customVarietyContainer.classList.add('hidden');

            if (val === 'other') {
                customCropContainer.classList.remove('hidden');
            } else if (val) {
                // Fetch varieties for selected crop
                try {
                    const res = await fetch(`/api/crops/${val}/varieties`);
                    const varieties = await res.json();

                    varieties.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = locale === 'si' ? v.variety_name_si : (locale === 'ta' ? v.variety_name_ta : v.variety_name);
                        manualVariety.appendChild(opt);
                    });

                    // Add AI Enrichment Option
                    const aiOpt = document.createElement('option');
                    aiOpt.value = 'trigger_ai_search';
                    aiOpt.textContent = '[' + t('AI') + '] ' + t('Find Other Varieties');
                    manualVariety.appendChild(aiOpt);

                    manualVariety.disabled = false;
                } catch (e) { console.error('Failed to load varieties'); }
            }
        });

        document.getElementById('suggestVarietiesBtn')?.addEventListener('click', async () => {
            const cropName = document.getElementById('customCropName').value;
            console.log('Finding seeds for:', cropName, 'Soil:', currentSoilType);

            // Clear any previous selection
            selectedVarietyId = '';

            if (!cropName) {
                alert(t('Please enter a crop name first.'));
                return;
            }

            const btn = document.getElementById('suggestVarietiesBtn');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>`;
            if (window.lucide) lucide.createIcons();

            try {
                const res = await fetch('/api/planner/suggest-varieties', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ crop_name: cropName, soil_type: currentSoilType })
                });

                const data = await res.json();
                console.log('Seed varieties received:', data);

                // --- Robust Array Extraction ---
                let varieties = [];
                const raw = data.varieties || data; // Handle Laravel wrapper

                if (Array.isArray(raw)) {
                    varieties = raw;
                } else if (raw && typeof raw === 'object') {
                    // Check common nested keys
                    if (Array.isArray(raw.suitable_varieties)) varieties = raw.suitable_varieties;
                    else if (Array.isArray(raw.varieties)) varieties = raw.varieties;
                    else if (Array.isArray(raw.suggestions)) varieties = raw.suggestions;
                    else {
                        // Find any array inside the object
                        for (const key in raw) {
                            if (Array.isArray(raw[key])) {
                                varieties = raw[key];
                                break;
                            }
                        }
                    }
                }

                manualVariety.innerHTML = `<option value="">-- ${t('Seed Type')} --</option>`;

                if (varieties && varieties.length > 0) {
                    varieties.forEach(v => {
                        const opt = document.createElement('option');
                        // Handle both {name: "..."} and {variety_name: "..."} formats
                        const vName = v.name || v.variety_name || v;
                        opt.value = vName;
                        opt.textContent = vName;
                        manualVariety.appendChild(opt);
                    });

                    const otherOpt = document.createElement('option');
                    otherOpt.value = 'other';
                    otherOpt.textContent = t('Other / Custom');
                    manualVariety.appendChild(otherOpt);

                    manualVariety.disabled = false;
                    console.log('Variety dropdown populated and enabled');
                } else {
                    console.warn('No varieties returned from AI, enabling manual entry');
                    const opt = document.createElement('option');
                    opt.value = 'other';
                    opt.textContent = t('Other / Custom');
                    manualVariety.appendChild(opt);
                    manualVariety.disabled = false;
                    // Trigger the manual entry box automatically
                    manualVariety.value = 'other';
                    customVarietyContainer.classList.remove('hidden');
                }
            } catch (e) {
                console.error('Variety suggestion failed:', e);
                alert('Failed to suggest varieties');
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
                if (window.lucide) lucide.createIcons();
            }
        });

        manualVariety?.addEventListener('change', async function () {
            const val = this.value;

            if (val === 'trigger_ai_search') {
                // Find the visible text of the selected crop
                const cropName = manualCrop.options[manualCrop.selectedIndex].text;
                console.log('Enriching existing crop with AI:', cropName);

                if (window.showToast) window.showToast(t('Asking AI for more varieties...'), 'info');

                try {
                    const res = await fetch('/api/planner/suggest-varieties', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ crop_name: cropName, soil_type: currentSoilType })
                    });

                    const data = await res.json();
                    const aiVarieties = data.varieties || data.suggestions || [];

                    // Reset dropdown but keep original options
                    const originalOptions = Array.from(manualVariety.options).filter(o => o.value !== 'trigger_ai_search' && o.value !== 'other');
                    manualVariety.innerHTML = `<option value="">-- ${t('Seed Type')} --</option>`;

                    // Re-add originals
                    originalOptions.forEach(o => manualVariety.appendChild(o));

                    // Add AI ones
                    if (aiVarieties.length > 0) {
                        aiVarieties.forEach(v => {
                            const name = v.name || v.variety_name || v;
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = '[' + t('AI') + '] ' + name;
                            manualVariety.appendChild(opt);
                        });
                    }

                    // Add 'other' back at the end
                    const otherOpt = document.createElement('option');
                    otherOpt.value = 'other';
                    otherOpt.textContent = t('Other / Custom');
                    manualVariety.appendChild(otherOpt);

                    manualVariety.disabled = false;
                    if (window.showToast) window.showToast(t('AI has suggested new varieties!'), 'success');

                } catch (e) {
                    console.error('AI enrichment failed', e);
                }
                return;
            }

            if (val === 'other') {
                customVarietyContainer.classList.remove('hidden');
            } else {
                customVarietyContainer.classList.add('hidden');
                if (val && !isNaN(val)) {
                    selectedVarietyId = val;
                }
            }
            manualProceedBtn.disabled = !this.value;
        });

        manualProceedBtn?.addEventListener('click', () => {
            const isOtherCrop = manualCrop.value === 'other';
            const isOtherVariety = manualVariety.value === 'other';

            if (isOtherCrop) {
                planningMethod = 'ai';
                // Note: job will use customCropName and customVarietyName
            } else {
                selectedVarietyId = manualVariety.value;
                planningMethod = 'manual';
            }
            showStep(3);
        });

        // --- Roadmap Generation Entry Point ---
        document.getElementById('generateRoadmapBtn')?.addEventListener('click', async () => {
            const genBtn = document.getElementById('generateRoadmapBtn');
            const roadmapConfig = document.getElementById('roadmapConfig');
            const loading = document.getElementById('roadmapLoading');
            const landSize = document.getElementById('landSize')?.value;
            const landUnit = document.getElementById('landUnit')?.value;
            const plantingDate = document.getElementById('roadmapDate')?.value;
            const district = document.getElementById('districtPicker')?.value || '';

            // Validation: Prevent accidental generation if no crop is selected
            const hasCustomCrop = document.getElementById('customCropName')?.value;
            if (!selectedVarietyId && !hasCustomCrop && manualCrop.value !== 'other' && !manualVariety.value) {
                alert(t('Please select a crop variety first.'));
                return;
            }

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
                    farm_id: selectedLandId,
                    soil_type: currentSoilType,
                    district: district,
                    custom_crop_name: document.getElementById('customCropName')?.value || '',
                    custom_variety_name: document.getElementById('customVarietyName')?.value || ''
                };

                if (manualCrop.value && manualCrop.value !== 'other') {
                    const opt = manualCrop.options[manualCrop.selectedIndex];
                    bodyJson.custom_crop_name = opt.getAttribute('data-name') || opt.text;
                }

                // If variety dropdown has an AI string value, treat it as a custom variety
                if (manualVariety && isNaN(manualVariety.value) && manualVariety.value !== 'other' && manualVariety.value !== '') {
                    bodyJson.custom_variety_name = manualVariety.value;
                    bodyJson.crop_variety_id = 'other';
                } else if (manualVariety.value === 'other') {
                    bodyJson.custom_variety_name = document.getElementById('customVarietyName').value;
                    bodyJson.crop_variety_id = 'other';
                }

                console.log('Dispatching roadmap generation with payload:', bodyJson);

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
        document.getElementById('savePlanBtn')?.addEventListener('click', async function () {
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

        // --- Planning Method Selection ---
        const manualBtn = document.getElementById('methodManualBtn');
        const aiBtn = document.getElementById('methodAiBtn');
        const manualInput = document.getElementById('manualDateInput');
        const aiInput = document.getElementById('aiDateRecommendation');

        const setMethod = (method) => {
            planningMethod = method;
            if (method === 'manual') {
                manualBtn.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
                manualBtn.querySelector('.w-5').classList.add('bg-emerald-500', 'border-emerald-500');
                aiBtn.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
                aiBtn.querySelector('.w-5').classList.remove('bg-emerald-500', 'border-emerald-500');
                manualInput.classList.remove('hidden');
                aiInput.classList.add('hidden');
            } else {
                aiBtn.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
                aiBtn.querySelector('.w-5').classList.add('bg-emerald-500', 'border-emerald-500');
                manualBtn.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/30');
                manualBtn.querySelector('.w-5').classList.remove('bg-emerald-500', 'border-emerald-500');
                aiInput.classList.remove('hidden');
                manualInput.classList.add('hidden');
                getAiDateRecommendation();
            }
        };

        manualBtn?.addEventListener('click', () => setMethod('manual'));
        aiBtn?.addEventListener('click', () => setMethod('ai'));

        async function getAiDateRecommendation() {
            const loading = document.getElementById('aiDateLoading');
            const result = document.getElementById('aiDateResult');
            loading.classList.remove('hidden');
            result.classList.add('hidden');

            try {
                // Fetch weather context (14 days)
                const lat = document.getElementById('latitude')?.value || 6.9271;
                const lon = document.getElementById('longitude')?.value || 79.8612;

                const weatherRes = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,precipitation_sum&timezone=auto`);
                const weatherData = await weatherRes.json();

                // Logic fix: Ensure we capture the crop name for custom/AI variety flows
                let activeCropName = document.getElementById('customCropName')?.value || '';
                if (!activeCropName && manualCrop.value && manualCrop.value !== 'other') {
                    activeCropName = manualCrop.options[manualCrop.selectedIndex].text;
                }

                const res = await fetch('/api/planner/recommend-date', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        crop_variety_id: selectedVarietyId,
                        custom_crop_name: activeCropName,
                        weather: weatherData.daily,
                        soil_type: currentSoilType
                    })
                });
                const data = await res.json();

                if (data.recommended_date) {
                    aiRecommendedDate = data.recommended_date;

                    // Sync the main planting date input
                    const roadmapDateInput = document.getElementById('roadmapDate');
                    if (roadmapDateInput) roadmapDateInput.value = data.recommended_date;

                    // Update visual display
                    const displayEl = document.getElementById('recDateDisplay');
                    const reasonEl = document.getElementById('recReason');

                    if (displayEl) {
                        displayEl.textContent = new Date(data.recommended_date).toLocaleDateString(undefined, {
                            month: 'long', day: 'numeric', year: 'numeric'
                        });
                    }

                    if (reasonEl) {
                        reasonEl.textContent = data.reason;
                    }

                    loading.classList.add('hidden');
                    result.classList.remove('hidden');
                }
            } catch (e) {
                console.error('AI Date Recommendation failed', e);
                setMethod('manual');
                alert(t('AI recommendation currently unavailable. Switched to manual.'));
            }
        }

        document.getElementById('restartWizardBtn')?.addEventListener('click', () => {
            window.location.reload();
        });

        updateStaticTranslations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlanner);
    } else {
        initPlanner();
    }
})();
