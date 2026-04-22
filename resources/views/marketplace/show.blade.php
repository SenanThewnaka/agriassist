@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #listing-map {
        height: 300px;
        width: 100%;
        border-radius: 2rem;
        z-index: 1;
    }
    .dark .leaflet-container {
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="marketplaceListing({{ $listing->latitude ?? 'null' }}, {{ $listing->longitude ?? 'null' }}, {{ $listing->quantity }})">
    <!-- Breadcrumbs -->
    <nav class="flex mb-8 reveal" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600/50">
            <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3"></i></li>
            <li><a href="{{ route('marketplace.index') }}" class="hover:text-emerald-600 transition-colors">{{ __('Marketplace') }}</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3"></i></li>
            <li class="text-emerald-600">{{ $listing->title }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Left: Images & Description -->
        <div class="lg:col-span-8 space-y-12">
            <!-- Gallery -->
            <div class="bg-white dark:bg-[#081811] p-4 rounded-[3.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl reveal overflow-hidden">
                <div class="relative h-[500px] rounded-[2.5rem] overflow-hidden group">
                    @if($listing->images && count($listing->images) > 0)
                        <img :src="activeImage" class="w-full h-full object-cover transition-all duration-700">
                        @if(count($listing->images) > 1)
                            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex space-x-3 p-3 bg-black/20 backdrop-blur-md rounded-2xl">
                                @foreach($listing->images as $index => $image)
                                    <button @click="activeImage = '{{ Storage::url($image) }}'" 
                                        :class="activeImage === '{{ Storage::url($image) }}' ? 'border-white' : 'border-transparent'"
                                        class="w-16 h-16 rounded-xl border-2 overflow-hidden transition-all hover:scale-110">
                                        <img src="{{ Storage::url($image) }}" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-200">
                            <i data-lucide="image" class="w-20 h-20 mb-4"></i>
                            <span class="text-xs font-black uppercase tracking-widest">{{ __('No Images Available') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="reveal" style="transition-delay: 100ms">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div>
                        <span class="px-4 py-1.5 bg-emerald-950 text-white text-[10px] font-black rounded-full uppercase tracking-[0.2em] border border-emerald-500/30 mb-4 inline-block shadow-lg">
                            {{ $listing->category }}
                        </span>
                        <h1 class="text-5xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase leading-none">{{ $listing->title }}</h1>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 tracking-widest mb-1">{{ __('Total Price') }}</p>
                        <p class="text-5xl font-black text-emerald-600 tracking-tighter">Rs. {{ number_format($listing->price) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                    <div class="p-6 bg-white dark:bg-[#081811] rounded-3xl border-2 border-emerald-100 dark:border-emerald-900 shadow-lg">
                        <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 mb-2">{{ __('Available') }}</p>
                        <p class="text-xl font-black text-emerald-950 dark:text-white">{{ $listing->quantity }} {{ $listing->unit }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-[#081811] rounded-3xl border-2 border-emerald-100 dark:border-emerald-900 shadow-lg">
                        <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 mb-2">{{ __('Posted') }}</p>
                        <p class="text-xl font-black text-emerald-950 dark:text-white">{{ $listing->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-[#081811] rounded-3xl border-2 border-emerald-100 dark:border-emerald-900 shadow-lg md:col-span-2">
                        <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 mb-2">{{ __('Location') }}</p>
                        <p class="text-xl font-black text-emerald-950 dark:text-white truncate">{{ $listing->location }}</p>
                    </div>
                </div>

                <div class="prose dark:prose-invert max-w-none">
                    <h3 class="text-2xl font-black text-emerald-950 dark:text-white uppercase mb-4">{{ __('Product Description') }}</h3>
                    <p class="text-lg font-bold text-emerald-800/70 dark:text-emerald-400/70 leading-relaxed">
                        {{ $listing->description }}
                    </p>
                </div>

                <!-- Reviews Section -->
                <div class="mt-16 pt-12 border-t-4 border-emerald-50 dark:border-emerald-900/50">
                    <div class="flex items-center justify-between mb-10">
                        <h3 class="text-3xl font-black text-emerald-950 dark:text-white uppercase tracking-tighter">{{ __('Customer Reviews') }}</h3>
                        <div class="flex items-center space-x-2">
                            <div class="flex text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star" class="w-5 h-5 {{ $i <= $listing->average_rating ? 'fill-amber-500' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="font-black text-emerald-950 dark:text-white">{{ number_format($listing->average_rating, 1) }}</span>
                            <span class="text-sm font-bold text-emerald-800/40">({{ $listing->reviews->count() }})</span>
                        </div>
                    </div>

                    @forelse($listing->reviews()->latest()->get() as $review)
                        <div class="p-8 bg-emerald-50/50 dark:bg-emerald-950/20 border-2 border-emerald-100 dark:border-emerald-900 rounded-[2rem] mb-6 reveal">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-emerald-700 text-white rounded-2xl flex items-center justify-center font-black text-xl">
                                        {{ substr($review->buyer->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-emerald-950 dark:text-white uppercase">{{ $review->buyer->full_name }}</h4>
                                        <div class="flex text-amber-500 scale-75 origin-left">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'fill-amber-500' : '' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-black uppercase text-emerald-800/40">{{ $review->created_at->format('M d, Y') }}</span>
                            </div>
                            <p class="text-emerald-800/80 dark:text-emerald-400/80 font-bold leading-relaxed italic">"{{ $review->comment }}"</p>
                        </div>
                    @empty
                        <div class="p-12 text-center border-4 border-dashed border-emerald-100 dark:border-emerald-900 rounded-[3rem]">
                            <p class="font-bold text-emerald-800/40">{{ __('No reviews yet. Verified buyers will be able to share their experience.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Order Request & Seller -->
        <div class="lg:col-span-4 space-y-8">
            <!-- Order Request Card -->
            <div class="bg-emerald-950 p-8 rounded-[3rem] border-4 border-emerald-800 shadow-2xl reveal relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex items-center space-x-4 mb-8">
                        <div class="w-16 h-16 bg-emerald-800 rounded-2xl flex items-center justify-center font-black text-2xl text-emerald-400 border-2 border-emerald-700/50">
                            {{ substr($listing->seller->full_name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-white uppercase">{{ $listing->seller->full_name }}</h4>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400/60">{{ __('Verified Farmer') }}</p>
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() !== $listing->seller_id)
                            <form action="{{ route('marketplace.order.store', $listing) }}" method="POST" class="space-y-6">
                                @csrf
                                <div>
                                    <label class="block text-[10px] font-black text-emerald-400 uppercase tracking-[0.2em] mb-3">{{ __('Desired Quantity') }} ({{ $listing->unit }})</label>
                                    <div class="relative">
                                        <input type="number" name="requested_quantity" x-model="requestedQuantity" step="0.1" min="0.1" :max="availableStock" required
                                            class="w-full px-6 py-5 bg-emerald-900/50 border-2 border-emerald-800 rounded-2xl outline-none focus:border-emerald-500 font-black text-2xl text-white transition-all">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 font-black text-emerald-400/40 uppercase tracking-widest">{{ $listing->unit }}</span>
                                    </div>
                                    <div class="flex justify-between mt-3 text-[10px] font-black uppercase tracking-widest text-emerald-400/60 px-2">
                                        <span>{{ __('Estimated Total') }}:</span>
                                        <span class="text-emerald-400">Rs. <span x-text="numberFormat(requestedQuantity * {{ $listing->price }})"></span></span>
                                    </div>
                                </div>

                                <button type="submit" :disabled="requestedQuantity <= 0 || requestedQuantity > availableStock"
                                    class="w-full py-6 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-amber-950 rounded-[1.5rem] font-black uppercase tracking-widest shadow-2xl shadow-amber-500/20 transition-all flex items-center justify-center border-b-8 border-amber-700 active:translate-y-1 active:border-b-0">
                                    <i data-lucide="shopping-cart" class="w-6 h-6 mr-3"></i>
                                    <span>{{ __('Place Request') }}</span>
                                </button>
                            </form>
                            
                            <p class="text-[9px] text-emerald-400/40 font-bold text-center mt-6 uppercase tracking-widest">
                                <i data-lucide="info" class="w-3 h-3 inline-block mr-1"></i>
                                {{ __('Requesting doesn\'t commit you to pay. Start a negotiation first.') }}
                            </p>
                        @else
                            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl text-center">
                                <p class="text-emerald-400/60 text-xs font-black uppercase tracking-widest">{{ __('This is your listing') }}</p>
                                <a href="{{ route('seller.listings.edit', $listing) }}" class="mt-4 inline-block text-white font-black uppercase text-xs hover:underline">{{ __('Edit Ad') }}</a>
                            </div>
                        @endif
                    @else
                        <div class="p-8 bg-white/5 border-2 border-white/10 rounded-[2rem] text-center">
                            <i data-lucide="lock" class="w-10 h-10 text-emerald-700 mx-auto mb-4"></i>
                            <p class="text-white font-bold mb-6 text-sm">{{ __('Login to place a request and message the farmer directly.') }}</p>
                            <a href="{{ route('login') }}" class="block w-full py-4 bg-emerald-600 text-white rounded-xl font-black uppercase tracking-widest text-xs hover:bg-emerald-500 transition-all shadow-xl">
                                {{ __('Login / Register') }}
                            </a>
                        </div>
                    @endauth
                </div>
                <i data-lucide="tractor" class="absolute -bottom-6 -right-6 w-40 h-40 text-emerald-900/20"></i>
            </div>

            <!-- Mini Map Card -->
            <div class="bg-white dark:bg-[#081811] p-2 rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal overflow-hidden" style="transition-delay: 200ms">
                <div id="listing-map" class="rounded-[2.5rem]"></div>
                <div class="p-6 text-center">
                    <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 tracking-widest mb-1">{{ __('Classification Area') }}</p>
                    <p class="text-sm font-black text-emerald-950 dark:text-white uppercase truncate">{{ $listing->location }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function marketplaceListing(lat, lon, maxQty) {
        return {
            activeImage: '{{ $listing->images && count($listing->images) > 0 ? Storage::url($listing->images[0]) : "" }}',
            requestedQuantity: 1,
            availableStock: maxQty,
            map: null,

            init() {
                if (lat && lon) {
                    setTimeout(() => this.initMap(lat, lon), 500);
                }
                if (window.lucide) lucide.createIcons();
            },

            numberFormat(num) {
                return new Intl.NumberFormat().format(num);
            },

            initMap(lat, lon) {
                this.map = L.map('listing-map', {
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: false
                }).setView([lat, lon], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
                L.marker([lat, lon]).addTo(this.map);
                this.map.invalidateSize();
            }
        }
    }
</script>
@endpush
