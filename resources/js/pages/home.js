window.weatherApp = function () {
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
        verdict: window.__AGRI_CONFIG.homeTranslations.verdict || 'Loading...',
        criticalAlert: null,
        translations: window.__AGRI_CONFIG.homeTranslations,
        rawData: null, // Keep raw data to re-process on lang change

        async refreshLocation() {
            console.log("Refreshing location...");
            this.locationName = '';
            this.verdict = this.translations.verdict || 'Loading...';

            const handleSuccess = (lat, lon) => {
                // Cache location if preference is enabled
                if (localStorage.getItem('agriassist_remember_location') === 'true') {
                    localStorage.setItem('agriassist_cached_location', JSON.stringify({
                        lat, lon, timestamp: Date.now()
                    }));
                }
                this.fetchWeather(lat, lon);
                this.reverseGeocode(lat, lon);
            };

            // Check for cached location first (valid for 24 hours)
            const cached = localStorage.getItem('agriassist_cached_location');
            if (cached) {
                const data = JSON.parse(cached);
                const isFresh = (Date.now() - data.timestamp) < (24 * 60 * 60 * 1000);
                if (isFresh) {
                    console.log("Using cached location");
                    handleSuccess(data.lat, data.lon);
                    return;
                }
            }

            const fallbackToColombo = () => {
                this.fetchWeather(6.9271, 79.8612); // Colombo default
                this.locationName = this.translations.defaultLocation;
            };

            const fallbackToIp = async () => {
                try {
                    const res = await fetch('https://get.geojs.io/v1/ip/geo.json');
                    const data = await res.json();
                    if (data.latitude && data.longitude) {
                        console.log("Geolocation fallback: Using IP location");
                        handleSuccess(parseFloat(data.latitude), parseFloat(data.longitude));
                        return true;
                    }
                } catch (e) {
                    console.error("IP Geolocation fallback failed:", e);
                }
                return false;
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        console.log("Position acquired:", pos.coords.latitude, pos.coords.longitude);
                        handleSuccess(pos.coords.latitude, pos.coords.longitude);
                    },
                    async err => {
                        console.error("Location error:", err);
                        const ipSuccess = await fallbackToIp();
                        if (!ipSuccess) fallbackToColombo();
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                console.warn("Geolocation not supported by this browser.");
                const ipSuccess = await fallbackToIp();
                if (!ipSuccess) {
                    fallbackToColombo();
                    this.locationName = this.translations.gpsNotAvailable;
                }
            }
        },

        init() {
            this.refreshLocation();

            // Listen for locale change to update weather strings
            window.addEventListener('agriassist-locale-changed', (e) => {
                const lang = e.detail.locale;
                // Update translations from the global config
                if (window.__AGRI_CONFIG.homeTranslations) {
                    this.translations = window.__AGRI_CONFIG.homeTranslations;
                    // Re-process if we have data
                    if (this.rawData) {
                        this.processForecast(this.rawData);
                    }
                }
            });
        },

        async reverseGeocode(lat, lon) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=${window.__AGRI_CONFIG.locale || 'en'}&email=contact@agriassist.app`);
                const data = await res.json();
                if (data.display_name) {
                    const parts = data.address;
                    this.locationName = parts.suburb || parts.town || parts.village || parts.city || parts.district || data.display_name.split(',')[0];
                }
            } catch (e) {
                console.error("Reverse geocode failed:", e);
            }
        },

        async fetchWeather(lat, lon) {
            try {
                const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&hourly=relativehumidity_2m&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,windspeed_10m_max,weathercode&timezone=auto`;
                const res = await fetch(url);
                const data = await res.json();

                this.rawData = data.daily; // Store for re-processing
                this.current = {
                    temp: Math.round(data.current_weather.temperature),
                    wind: Math.round(data.current_weather.windspeed),
                    humidity: data.hourly.relativehumidity_2m[0],
                    rain: data.daily.precipitation_probability_max[0]
                };

                this.locationName = this.locationName || `${this.translations.coordinates} ${lat.toFixed(2)}, ${lon.toFixed(2)}`;
                this.processForecast(data.daily);
            } catch (e) {
                this.verdict = this.translations.telemetryOffline;
                console.error("weather check failed:", e);
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

                let action = { type: 'neutral', text: this.translations.routineMaintenance, icon: 'clipboard-list' };

                if (windMax < 15 && rainProb < 20) {
                    action = { type: 'spray', text: this.translations.optimalSpraying, icon: 'spray-can' };
                } else if (rainSum >= 2 && rainSum <= 10 && (i === 0 || i === 1)) {
                    action = { type: 'fertilize', text: this.translations.applyFertilizer, icon: 'leafy-green' };
                } else if (rainProb > 70 || windMax > 30) {
                    action = { type: 'danger', text: this.translations.secureEquipment, icon: 'shield-alert' };
                } else if (tempMax > 33) {
                    action = { type: 'danger', text: this.translations.heatStressRisk, icon: 'sun-dim' };
                    extremeHeatCount++;
                }

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
                    dateStr: dateObj.toLocaleDateString(window.__AGRI_CONFIG.locale || 'en', { month: 'short', day: 'numeric' }),
                    tempMax, tempMin, rain: rainProb, wind: windMax,
                    icon: iconName,
                    action
                });
            }

            this.generateStrategicVerdict(daily, extremeHeatCount);
            this.$nextTick(() => window.lucide && window.lucide.createIcons());
        },

        generateStrategicVerdict(daily, heatCount) {
            this.insights = [];
            const current = this.current;

            if (current.wind < 15 && current.rain < 20) {
                this.insights.push({ text: this.translations.sprayWindowActive, icon: 'spray-can', type: 'good' });
            }
            if (current.humidity > 80 && current.temp > 25) {
                this.insights.push({ text: this.translations.fungalRiskElevated, icon: 'biohazard', type: 'danger' });
            }

            this.criticalAlert = null;
            const avgRain = daily.precipitation_probability_max.slice(0, 3).reduce((a, b) => a + b, 0) / 3;

            if (heatCount >= 3) {
                this.criticalAlert = { title: this.translations.droughtTitle, message: this.translations.droughtMsg };
                this.verdict = this.translations.droughtVerdict;
            } else if (avgRain > 70) {
                this.criticalAlert = { title: this.translations.floodTitle, message: this.translations.floodMsg };
                this.verdict = this.translations.floodVerdict;
            } else if (current.humidity > 80 && current.temp > 25) {
                this.verdict = this.translations.fungalVerdict;
            } else {
                this.verdict = this.translations.optimalVerdict;
            }
        }
    };
};
