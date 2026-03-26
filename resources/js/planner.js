(function () {
    let currentStep = 1;
    let currentSoilType = '';
    let selectedVarietyId = '';

    // data from blade template
    const config = window.__PLANNER_CONFIG || {};
    const csrf = config.csrf;
    const locale = config.locale || 'en';
    const translations = config.translations || {};

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
        if (step === 2) loadSuggestions();
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
                    temperature: 28
                })
            });
            const data = await res.json();
            const subtitle = document.getElementById('step2Subtitle');
            if (subtitle) {
                subtitle.textContent = data.suggestions.length + ' Best crops for your region';
            }

            if (!data.suggestions || data.suggestions.length === 0) {
                grid.innerHTML = `<div class="col-span-full p-20 text-center"><div class="text-6xl mb-4">🏜️</div><h3 class="text-2xl font-black text-emerald-950 dark:text-white">No ideal matches found</h3><p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold">Try selecting a different soil type or use the manual search above.</p></div>`;
            } else {
                data.suggestions.forEach(crop => {
                    const card = document.createElement('div');
                    card.className = 'p-6 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2rem] cursor-pointer hover:border-emerald-400 transition-all duration-300 group';
                    card.innerHTML = `
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-black text-emerald-950 dark:text-white capitalize">${crop.crop_name}</h3>
                                <p class="text-sm font-bold text-emerald-600">${crop.variety_name} &middot; ${crop.growth_days} Days</p>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-black text-emerald-700">${crop.suitability}%</div>
                                <div class="text-[9px] font-black uppercase text-emerald-500">Match</div>
                            </div>
                        </div>
                        <div class="h-2 bg-emerald-100 rounded-full overflow-hidden mb-4">
                            <div class="h-full bg-emerald-600" style="width:${crop.suitability}%"></div>
                        </div>
                    `;
                    card.addEventListener('click', () => {
                        selectedVarietyId = crop.variety_id;
                        showStep(3);
                    });
                    grid.appendChild(card);
                });
            }
            loading.classList.add('hidden');
            grid.classList.remove('hidden');
        } catch (e) {
            console.error('API Error:', e);
            loading.classList.add('hidden');
            grid.innerHTML = '<div class="col-span-full text-center p-20 text-red-500 font-bold">Connection lost. Please try again.</div>';
            grid.classList.remove('hidden');
        }
    }

    function renderRoadmap(data) {
        const container = document.getElementById('roadmapContainer');
        if (!container) return;

        container.innerHTML = '<div class="absolute left-[11px] top-2 bottom-2 w-1 bg-gradient-to-b from-emerald-600 via-emerald-400 to-emerald-200 dark:from-emerald-700 dark:via-emerald-800 dark:to-emerald-950 rounded-full"></div>';

        document.getElementById('resDuration').textContent = data.growth_days || 0;
        document.getElementById('resCropVariety').textContent = data.crop + ' - ' + data.variety;
        document.getElementById('resPlantDate').textContent = new Date(data.planting_date).toLocaleDateString(locale, { dateStyle: 'long' });
        document.getElementById('resHarvestDate').textContent = new Date(data.estimated_harvest).toLocaleDateString(locale, { dateStyle: 'long' });

        data.stages.forEach((stage, i) => {
            const stageEl = document.createElement('div');
            stageEl.className = 'relative flex space-x-6 pb-12 last:pb-0';
            const nextStage = data.stages[i + 1];
            const nextDay = nextStage ? nextStage.days_from_start - 1 : data.growth_days;
            const dayRange = stage.days_from_start === nextDay ? `Day ${stage.days_from_start}` : `Day ${stage.days_from_start} - ${nextDay}`;

            stageEl.innerHTML = `
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900 border-2 border-emerald-500 flex items-center justify-center text-emerald-600 font-black z-10">
                        ${i + 1}
                    </div>
                </div>
                <div class="flex-1 bg-white dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-800 rounded-3xl p-6 hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xl font-black text-emerald-950 dark:text-white capitalize">${stage.name}</h4>
                        <span class="bg-emerald-50 dark:bg-emerald-800 text-emerald-600 dark:text-emerald-400 text-xs font-black px-3 py-1 rounded-xl uppercase">
                            ${dayRange}
                        </span>
                    </div>
                    <p class="text-emerald-800 dark:text-emerald-200 font-bold mb-4">${stage.advice}</p>
                    <div class="bg-emerald-50 dark:bg-emerald-900/40 rounded-2xl p-4">
                        <div class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">Key Targets</div>
                        <div class="text-xs text-emerald-900 dark:text-emerald-200 font-bold italic leading-relaxed">
                            ${stage.description || 'Follow standard cultivation guidelines.'}
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(stageEl);
        });
        if (window.lucide) lucide.createIcons();
    }

    function initPlanner() {
        const detectBtn = document.getElementById('detectLocationBtn');
        const step1NextBtn = document.getElementById('step1NextBtn');

        detectBtn?.addEventListener('click', function () {
            const btn = this;
            const originalText = btn.innerHTML;

            if (!navigator.geolocation) {
                alert('Geolocation is not supported or is blocked by your browser. Please select soil manually.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></div> ${translations['Detecting'] || 'Detecting...'}`;

            try {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        try {
                            const res = await fetch('/api/soil-type', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    lat: pos.coords.latitude,
                                    lon: pos.coords.longitude
                                })
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

                            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>${translations['Detected'] || 'Detected'}: ${currentSoilType}`;
                            btn.classList.remove('bg-emerald-600');
                            btn.classList.add('bg-emerald-500');
                        } catch (e) {
                            console.error('Soil Detection API Error:', e);
                            alert('Could not determine soil type. Please select manually.');
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    },
                    (err) => {
                        let msg = 'Location access failed. Please select soil manually.';
                        switch (err.code) {
                            case 1: msg = translations['Permission Denied']; break;
                            case 2: msg = translations['Position Unavailable']; break;
                            case 3: msg = translations['Timeout']; break;
                        }
                        alert(msg);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    { timeout: 10000 }
                );
            } catch (e) {
                console.error('Geolocation call failed:', e);
                alert('Geolocation failed unexpectedly. Please select soil manually.');
                btn.innerHTML = originalText;
                btn.disabled = false;
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

        manualCrop?.addEventListener('change', async function () {
            const cropId = this.value;
            manualVariety.innerHTML = '<option value="">-- Seed Type --</option>';
            manualVariety.disabled = true;
            manualProceed.disabled = true;
            if (!cropId) return;

            try {
                const res = await fetch('/crops/' + cropId + '/varieties');
                const varieties = await res.json();
                varieties.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.name;
                    manualVariety.appendChild(opt);
                });
                manualVariety.disabled = false;
            } catch (e) {
                console.error('Failed to load varieties:', e);
            }
        });

        manualVariety?.addEventListener('change', function () {
            selectedVarietyId = this.value;
            manualProceed.disabled = !selectedVarietyId;
        });

        manualProceed?.addEventListener('click', () => {
            if (selectedVarietyId) showStep(3);
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
                const res = await fetch('/api/crop-plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        crop_variety_id: selectedVarietyId,
                        planting_date: document.getElementById('roadmapDate').value
                    })
                });
                const data = await res.json();
                renderRoadmap(data);
                loading.classList.add('hidden');
                resultCard.classList.remove('hidden');
            } catch (e) {
                console.error('Roadmap failed:', e);
                loading.classList.add('hidden');
                roadmapConfig.classList.remove('hidden');
            }
        });

        document.getElementById('restartWizardBtn')?.addEventListener('click', () => {
            location.reload();
        });

        document.getElementById('backToStep2FromConfig')?.addEventListener('click', () => {
            showStep(2);
        });

        // District picker logic
        const districtPicker = document.getElementById('districtPicker');
        districtPicker?.addEventListener('change', async function () {
            const district = this.value;
            if (!district) return;

            try {
                const res = await fetch('/api/soil-by-district?district=' + district);
                const data = await res.json();
                currentSoilType = data.soil_type;

                // Trigger button matching UI
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlanner);
    } else {
        initPlanner();
    }
})();
