@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .image-preview-slot {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .image-preview-slot:hover {
        border-color: #10b981;
        background-color: rgba(16, 185, 129, 0.05);
    }
    #listing-map {
        height: 450px !important;
        width: 100%;
        border-radius: 2rem;
        z-index: 1;
        border: 4px solid #f0fdf4;
    }
    .dark #listing-map {
        border-color: #064e3b;
    }
    .leaflet-container {
        width: 100% !important;
        height: 100% !important;
        background: #f8fafc !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12" x-data="sellerListingForm({{ $userFarms->toJson() }})">
    <!-- Header -->
    <div class="mb-12 text-center sm:text-left">
        <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 mb-4 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            <span data-t-key="Back to Dashboard">{{ __('Back to Dashboard') }}</span>
        </a>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="Create New Listing">
            {{ __('Create New Listing') }}
        </h1>
    </div>

    <form action="{{ route('seller.listings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- Media Section -->
        <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal">
            <h3 class="text-lg font-black text-emerald-950 dark:text-white uppercase tracking-tight mb-6 flex items-center">
                <i data-lucide="camera" class="w-5 h-5 mr-3 text-emerald-600"></i>
                <span data-t-key="Product Photos">{{ __('Product Photos') }}</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <template x-for="(image, index) in previews" :key="index">
                    <div class="relative aspect-square rounded-2xl overflow-hidden border-2 border-emerald-500 shadow-lg animate-in zoom-in-50 duration-300">
                        <img :src="image" class="w-full h-full object-cover">
                        <button type="button" @click="removeImage(index)" class="absolute top-2 right-2 p-1.5 bg-red-600/90 text-white rounded-xl shadow-lg backdrop-blur-sm hover:bg-red-600 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>

                <label x-show="previews.length < 4" class="image-preview-slot cursor-pointer aspect-square rounded-2xl border-4 border-dashed border-emerald-100 dark:border-emerald-900 flex flex-col items-center justify-center text-emerald-300 hover:text-emerald-500 transition-all">
                    <i data-lucide="image-plus" class="w-8 h-8 mb-2"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Add Photo') }}</span>
                    <input type="file" name="images[]" multiple accept="image/*" class="hidden" @change="handleImageUpload">
                </label>
            </div>
        </div>

        <!-- Details Section -->
        <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal" style="transition-delay: 100ms">
            <h3 class="text-lg font-black text-emerald-950 dark:text-white uppercase tracking-tight mb-6 flex items-center">
                <i data-lucide="file-text" class="w-5 h-5 mr-3 text-emerald-600"></i>
                <span data-t-key="Listing Details">{{ __('Listing Details') }}</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Title') }}</label>
                    <input type="text" name="title" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white" placeholder="{{ __('e.g. Fresh Red Onions - Jaffna') }}">
                </div>
                <div>
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Category') }}</label>
                    <select name="category" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white outline-none appearance-none cursor-pointer">
                        <option value="Harvest">{{ __('Harvest') }}</option>
                        <option value="Seeds">{{ __('Seeds') }}</option>
                        <option value="Tools">{{ __('Tools') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Price (LKR)') }}</label>
                    <input type="number" name="price" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Quantity') }}</label>
                    <input type="text" name="quantity" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Unit') }}</label>
                    <select name="unit" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white outline-none appearance-none cursor-pointer">
                        <option value="kg">kg</option>
                        <option value="tons">tons</option>
                        <option value="units">units</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2">{{ __('Description') }}</label>
                    <textarea name="description" required rows="3" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white" placeholder="{{ __('e.g. High quality produce harvested this morning.') }}"></textarea>
                </div>
            </div>
        </div>

        <!-- Location Section -->
        <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal" style="transition-delay: 200ms">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 pb-8 border-b-2 border-emerald-50 dark:border-emerald-900/50">
                <div>
                    <h3 class="text-xl font-black text-emerald-950 dark:text-white uppercase flex items-center">
                        <i data-lucide="map-pin" class="w-6 h-6 mr-3 text-emerald-600"></i>
                        <span data-t-key="Pin Location">{{ __('Pin Location') }}</span>
                    </h3>
                    <p class="text-xs font-bold text-emerald-800/40 dark:text-emerald-400/40 mt-1">{{ __('Search or select existing land to pin your harvest location.') }}</p>
                </div>
                
                <div class="flex flex-col space-y-2">
                    <span class="text-[10px] font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest">{{ __('Quick Selection') }}:</span>
                    <select @change="selectFarm($event.target.value)" class="px-6 py-4 bg-emerald-700 text-white border-none rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-emerald-700/20 outline-none cursor-pointer hover:bg-emerald-600 transition-all min-w-[250px]">
                        <option value="">{{ __('Select Registered Land') }}</option>
                        <template x-for="farm in userFarms" :key="farm.id">
                            <option :value="farm.id" x-text="farm.farm_name + ' (' + farm.district + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Smart Search Over map -->
                <div class="relative rounded-[2rem] overflow-hidden shadow-2xl border-4 border-emerald-50 dark:border-emerald-900/50 group">
                    <!-- Search Bar Overlay -->
                    <div class="absolute top-6 left-6 right-6 z-[1000]">
                        <div class="relative max-w-lg">
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="searchPlaces()"
                                class="w-full pl-12 pr-12 py-4 bg-white/95 dark:bg-[#081811]/95 border-2 border-emerald-100 dark:border-emerald-800 rounded-2xl shadow-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white backdrop-blur-md transition-all" 
                                placeholder="{{ __('Search for village or town...') }}">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                            
                            <div x-show="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-800 rounded-2xl shadow-2xl overflow-hidden reveal" @click.away="searchResults = []">
                                <template x-for="result in searchResults" :key="result.lat + result.lon">
                                    <button type="button" @click="selectResult(result)" class="w-full px-6 py-4 text-left hover:bg-emerald-50 dark:hover:bg-emerald-900/40 flex items-center space-x-4 border-b border-emerald-50 last:border-0 transition-colors">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                        <span class="text-sm font-bold text-emerald-950 dark:text-emerald-100 truncate" x-text="result.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div id="listing-map"></div>

                    <!-- GPS Control Overlay -->
                    <button type="button" @click="locateUser()" class="absolute bottom-6 right-6 z-[1000] p-4 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl shadow-xl text-emerald-700 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-all">
                        <i data-lucide="crosshair" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Selected Address">{{ __('Selected Address') }}</label>
                    <input type="text" name="location" x-model="location" required readonly class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white cursor-default shadow-inner" placeholder="{{ __('Pin location on map...') }}">
                    <p x-show="!location" class="mt-2 text-[10px] font-black text-amber-600 uppercase animate-pulse">
                        <i data-lucide="alert-circle" class="w-3 h-3 inline-block mr-1"></i>
                        {{ __('Please pin a location on the map to continue.') }}
                    </p>
                </div>

                <input type="hidden" name="latitude" x-model="lat">
                <input type="hidden" name="longitude" x-model="lon">
            </div>
        </div>

        <button type="submit" :disabled="!location" class="w-full py-8 bg-emerald-700 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-[2.5rem] font-black text-2xl shadow-2xl shadow-emerald-700/30 transition-all flex items-center justify-center space-x-4 border-b-8 border-emerald-900 transform active:translate-y-1">
            <i data-lucide="check-circle" class="w-10 h-10"></i>
            <span data-t-key="Publish Classified Ad">{{ __('Publish Classified Ad') }}</span>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function sellerListingForm(userFarms) {
        return {
            previews: [],
            lat: null,
            lon: null,
            location: '',
            map: null,
            marker: null,
            userFarms: userFarms,
            searchQuery: '',
            searchResults: [],
            isSearching: false,

            init() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.initMap();
                        if (window.lucide) lucide.createIcons();
                    }, 400);
                });
            },

            handleImageUpload(e) {
                const files = Array.from(e.target.files).slice(0, 4 - this.previews.length);
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push(e.target.result);
                        this.$nextTick(() => { if(window.lucide) lucide.createIcons(); });
                    };
                    reader.readAsDataURL(file);
                });
            },

            removeImage(index) {
                this.previews.splice(index, 1);
            },

            initMap() {
                const mapContainer = document.getElementById('listing-map');
                if (!mapContainer || this.map) return;

                const slCenter = [7.8731, 80.7718];
                this.map = L.map('listing-map', {
                    zoomControl: false,
                    attributionControl: false
                }).setView(slCenter, 7);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);
                
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                this.map.on('click', (e) => {
                    this.updatePin(e.latlng.lat, e.latlng.lng);
                });

                this.map.invalidateSize();
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
                this.location = result.name; // Instant update from search
                this.updatePin(result.lat, result.lon, true);
                this.map.setView([result.lat, result.lon], 15);
            },

            locateUser() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        this.updatePin(pos.coords.latitude, pos.coords.longitude);
                        this.map.setView([pos.coords.latitude, pos.coords.longitude], 15);
                    });
                }
            },

            selectFarm(farmId) {
                const farm = this.userFarms.find(f => f.id == farmId);
                if (farm && farm.latitude && farm.longitude) {
                    const lat = parseFloat(farm.latitude);
                    const lon = parseFloat(farm.longitude);
                    this.location = farm.farm_name + ', ' + farm.district; // Instant update
                    this.updatePin(lat, lon, true);
                    this.map.setView([lat, lon], 16);
                }
            },

            updatePin(lat, lng, skipGeocode = false) {
                this.lat = lat;
                this.lon = lng;
                if (this.marker) this.map.removeLayer(this.marker);
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                
                this.marker.on('dragend', async (e) => {
                    const pos = e.target.getLatLng();
                    this.lat = pos.lat;
                    this.lon = pos.lng;
                    await this.reverseGeocode(pos.lat, pos.lng);
                });

                if (!skipGeocode) {
                    this.reverseGeocode(lat, lng);
                }
            },

            async reverseGeocode(lat, lng) {
                try {
                    const res = await fetch(`/proxy/geocode?lat=${lat}&lon=${lng}`);
                    const data = await res.json();
                    if (data.display_name) {
                        this.location = data.display_name;
                    }
                } catch (e) {
                    console.error('Geocoding failed', e);
                }
            }
        }
    }
</script>
@endpush
