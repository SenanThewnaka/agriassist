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
                coordinates: '{{ __('Coordinates: ') }}',
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
                            optimalVerdict: '{{ __('Stable metrics.Excellent 7 - day conditions for precision planting.') }}',
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
                dateStr: dateObj.toLocaleDateString('{{ app()->getLocale() }}', { month: 'short', day: 'numeric' }),
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
        } else if (current.humidity > 80 && current.temp > 25) {
            this.verdict = this.translations.fungalVerdict;
        } else {
            this.verdict = this.translations.optimalVerdict;
        }
    }
}
}
