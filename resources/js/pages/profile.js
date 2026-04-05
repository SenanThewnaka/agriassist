window.farmManager = function() {
    return {
        showModal: false,
        showWizard: false,
        isSaving: false,
        isSearching: false,
        isEditing: false,
        isLoading: true,
        editingId: null,
        map: null,
        marker: null,
        dashboardMap: null,
        searchQuery: '',
        searchResults: [],
        slBounds: [[5.9, 79.5], [9.9, 81.9]],
        newFarm: {
            farm_name: '', latitude: null, longitude: null,
            farm_size: '', size_value: '', size_unit: 'Acres',
            district: '', soil_type: '', irrigation_source: 'rainfed'
        },

        wizard: {
            step: 1,
            answers: { feel: '', water: '', sticky: '', color: '' }
        },

        init() {
            setTimeout(() => {
                this.isLoading = false;
                this.$nextTick(() => {
                    this.initDashboardMap();
                    if (window.lucide) window.lucide.createIcons();
                });
            }, 800);
        },

        // Using standard high-detail OSM tiles for both modes
        // Dark mode is now handled via CSS filter for maximum visibility
        getTileLayer() {
            return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            });
        },

        initDashboardMap() {
            const container = document.getElementById('dashboard-map');
            if (!container || this.dashboardMap) return;

            this.dashboardMap = L.map('dashboard-map', { 
                zoomControl: false,
                attributionControl: false 
            }).setView([7.8731, 80.7718], 7);

            this.getTileLayer().addTo(this.dashboardMap);

            const farms = window.__AGRI_DATA.userFarms || [];
            if (farms.length > 0) {
                const markers = [];
                farms.forEach(farm => {
                    const m = L.marker([farm.latitude, farm.longitude]).addTo(this.dashboardMap);
                    m.bindPopup(`<b class="font-black text-emerald-950">${farm.farm_name}</b><br>${farm.district}`);
                    markers.push([farm.latitude, farm.longitude]);
                });
                const bounds = L.latLngBounds(markers);
                this.dashboardMap.fitBounds(bounds, { padding: [50, 50] });
            }
        },

        openModal(farm = null) {
            this.showModal = true;
            this.showWizard = false;
            if (farm) {
                this.isEditing = true;
                this.editingId = farm.id;
                this.newFarm = { ...farm };
                if (farm.farm_size) {
                    const parts = farm.farm_size.split(' ');
                    this.newFarm.size_value = parts[0];
                    this.newFarm.size_unit = parts[1] || 'Acres';
                }
            } else {
                this.isEditing = false;
                this.editingId = null;
                this.resetForm();
            }

            setTimeout(() => {
                this.initMap();
                if (this.newFarm.latitude) {
                    this.map.setView([this.newFarm.latitude, this.newFarm.longitude], 16);
                    this.setMarker(this.newFarm.latitude, this.newFarm.longitude);
                    this.updateLocation(this.newFarm.latitude, this.newFarm.longitude);
                } else {
                    this.locateUser(); 
                }
            }, 400);
        },

        calculateSoil() {
            const a = this.wizard.answers;
            let result = "Reddish Brown Earths"; 
            if (a.feel === 'sticky' && a.water === 'slow') result = "Grumusols";
            else if (a.feel === 'gritty' && a.water === 'fast') result = "Regosols";
            else if (a.feel === 'smooth' && a.sticky === 'medium') result = "Red-Yellow Podzolic Soils";
            else if (a.sticky === 'high' && a.water === 'slow') result = "Low Humic Gley Soils";
            else if (a.feel === 'gritty' && a.water === 'medium') result = "Non-Calcic Brown Soils";

            this.newFarm.soil_type = result;
            this.showWizard = false;
            window.showToast(`Intelligence analyzed: Your land likely has ${result}.`, 'info');
        },

        resetForm() {
            this.newFarm = {
                farm_name: '', latitude: null, longitude: null,
                farm_size: '', size_value: '', size_unit: 'Acres',
                district: '', soil_type: '', irrigation_source: 'rainfed'
            };
            if (this.marker) {
                this.map.removeLayer(this.marker);
                this.marker = null;
            }
        },

        initMap() {
            const mapContainer = document.getElementById('farm-map');
            if (!mapContainer) return;

            if (!this.map) {
                this.map = L.map('farm-map', { 
                    zoomControl: false,
                    maxBounds: this.slBounds,
                    maxBoundsViscosity: 1.0,
                    minZoom: 7
                }).setView([7.8731, 80.7718], 8);

                this.getTileLayer().addTo(this.map);
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                this.map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    this.updateLocation(lat, lng);
                });
            }
            this.map.invalidateSize(true);
        },

        locateUser() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const { latitude, longitude } = pos.coords;
                        if (latitude > 5.9 && latitude < 9.9 && longitude > 79.5 && longitude < 81.9) {
                            if (this.map) {
                                this.map.setView([latitude, longitude], 16);
                                this.updateLocation(latitude, longitude);
                            }
                        }
                    },
                    err => window.showToast("Could not access your location.", "warning"),
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            }
        },

        async searchPlaces() {
            if (this.searchQuery.length < 3) {
                this.searchResults = [];
                return;
            }

            this.isSearching = true;
            try {
                const res = await fetch(`/proxy/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                this.searchResults = data.features.map(f => ({
                    name: [f.properties.name, f.properties.city, f.properties.district].filter(Boolean).join(', '),
                    lat: f.geometry.coordinates[1],
                    lon: f.geometry.coordinates[0]
                }));
            } catch (e) {
                console.error("Search failed", e);
            } finally {
                this.isSearching = false;
            }
        },

        selectResult(result) {
            this.searchQuery = result.name;
            this.searchResults = [];
            if (this.map) {
                this.map.setView([result.lat, result.lon], 16);
                this.updateLocation(result.lat, result.lon);
            }
        },

        setMarker(lat, lng) {
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.updateLocation(pos.lat, pos.lng);
                });
            }
        },

        async updateLocation(lat, lng) {
            this.newFarm.latitude = lat;
            this.newFarm.longitude = lng;
            this.setMarker(lat, lng);

            try {
                const res = await fetch(`/proxy/geocode?lat=${lat}&lon=${lng}`);
                const data = await res.json();
                if (data.address) {
                    let district = data.address.state_district || data.address.city || data.address.district || data.address.county || '';
                    district = district.replace(' District', '').replace(' Division', '').replace(' Province', '').trim();
                    this.newFarm.district = district;

                    const soilRes = await fetch(`/api/soil-by-district?district=${encodeURIComponent(district)}`);
                    const soilData = await soilRes.json();
                    if (soilData.soil_type) {
                        this.newFarm.soil_type = soilData.soil_type;
                    }
                }
            } catch (e) {
                console.error("Geocoding failed", e);
            }
        },

        async saveFarm() {
            this.isSaving = true;
            const url = this.isEditing ? `/farms/${this.editingId}` : '/farms';
            const method = this.isEditing ? 'PATCH' : 'POST';

            if (this.newFarm.size_value) {
                this.newFarm.farm_size = `${this.newFarm.size_value} ${this.newFarm.size_unit}`;
            }

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newFarm)
                });
                const data = await res.json();
                if (data.success) {
                    window.showToast(data.message, 'success');
                    window.location.reload(); 
                } else {
                    window.showToast(data.message || 'Failed to save farm', 'error');
                }
            } catch (e) {
                window.showToast('Connection error', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async deleteFarm(id) {
            if (!confirm('Remove this land?')) return;
            try {
                const res = await fetch(`/farms/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (res.ok) {
                    window.showToast('Land removed.', 'info');
                    window.location.reload();
                }
            } catch (e) {
                window.showToast('Error removing land', 'error');
            }
        }
    };
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
    const profileForms = document.querySelectorAll('form[action*=\"profile\"]');
    profileForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type=\"submit\"]');
            if (!submitBtn) return;
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i data-lucide=\"loader-2\" class=\"w-5 h-5 animate-spin mx-auto\"></i>`;
            if (window.lucide) window.lucide.createIcons();
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: new FormData(form)
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    window.showToast(data.message, 'success');
                } else {
                    window.showToast(data.message || 'Update failed.', 'error');
                }
            } catch (error) {
                window.showToast('Server sync failed.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (window.lucide) window.lucide.createIcons();
            }
        });
    });
});
