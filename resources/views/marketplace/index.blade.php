@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header & Search -->
    <div class="mb-16 text-center reveal">
        <h1 class="text-6xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase mb-4" data-t-key="Marketplace">
            {{ __('Marketplace') }}
        </h1>
        <p class="text-xl font-bold text-emerald-800/60 dark:text-emerald-400/60 max-w-2xl mx-auto mb-10" data-t-key="Browse and search for harvests, seeds, and tools from local Sri Lankan farmers.">
            {{ __('Browse and search for harvests, seeds, and tools from local Sri Lankan farmers.') }}
        </p>

        <form action="{{ route('marketplace.index') }}" method="GET" class="max-w-3xl mx-auto relative group">
            <input type="text" name="q" value="{{ request('q') }}" 
                class="w-full pl-16 pr-32 py-6 bg-white dark:bg-[#081811] border-4 border-emerald-100 dark:border-emerald-900 rounded-[2.5rem] shadow-2xl focus:border-emerald-500 outline-none font-black text-lg text-emerald-950 dark:text-white transition-all group-hover:shadow-emerald-500/10" 
                placeholder="{{ __('Search by product or location...') }}">
            <i data-lucide="search" class="absolute left-6 top-1/2 -translate-y-1/2 w-8 h-8 text-emerald-600"></i>
            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 px-8 py-3 bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-600 transition-all">
                {{ __('Search') }}
            </button>
        </form>

        <!-- Category Filters -->
        <div class="flex flex-wrap justify-center gap-3 mt-10">
            <a href="{{ route('marketplace.index') }}" 
                @class([
                    'px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs transition-all border-2',
                    'bg-emerald-700 text-white border-emerald-700 shadow-xl shadow-emerald-700/20' => !request('category'),
                    'bg-white dark:bg-[#081811] text-emerald-900 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900 hover:border-emerald-500' => request('category')
                ])>
                {{ __('All') }}
            </a>
            @foreach($categories as $category)
                <a href="{{ route('marketplace.index', ['category' => $category, 'q' => request('q')]) }}" 
                    @class([
                        'px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs transition-all border-2',
                        'bg-emerald-700 text-white border-emerald-700 shadow-xl shadow-emerald-700/20' => request('category') === $category,
                        'bg-white dark:bg-[#081811] text-emerald-900 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900 hover:border-emerald-500' => request('category') !== $category
                    ])>
                    {{ __($category) }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Listings Feed -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($listings as $listing)
            <a href="{{ route('marketplace.show', $listing) }}" class="bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl overflow-hidden reveal group hover:border-emerald-500 transition-all flex flex-col h-full">
                <!-- Image -->
                <div class="relative h-64 bg-emerald-50 dark:bg-emerald-900/20 overflow-hidden shrink-0">
                    @if($listing->images && count($listing->images) > 0)
                        <img src="{{ Storage::url($listing->images[0]) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-emerald-200">
                            <i data-lucide="image" class="w-12 h-12"></i>
                        </div>
                    @endif
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 bg-emerald-950 text-white text-[10px] font-black rounded-lg uppercase tracking-widest border border-emerald-500/30 shadow-2xl">
                            {{ $listing->category }}
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-8 flex flex-col flex-1">
                    <h3 class="text-xl font-black text-emerald-950 dark:text-white uppercase leading-tight mb-2 group-hover:text-emerald-600 transition-colors">{{ $listing->title }}</h3>
                    
                    <div class="flex items-center text-[10px] font-black uppercase tracking-widest text-emerald-800/40 dark:text-emerald-400/40 mb-6">
                        <i data-lucide="map-pin" class="w-3 h-3 mr-1.5 text-emerald-600"></i>
                        {{ Str::limit($listing->location, 25) }}
                    </div>

                    <div class="mt-auto pt-6 border-t-2 border-emerald-50 dark:border-emerald-900/50 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 tracking-widest">{{ __('Price') }}</p>
                            <p class="text-2xl font-black text-emerald-600">Rs. {{ number_format($listing->price) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            <i data-lucide="arrow-right" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-32 text-center reveal">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-200 mx-auto mb-8">
                    <i data-lucide="package-search" class="w-12 h-12"></i>
                </div>
                <h3 class="text-3xl font-black text-emerald-950 dark:text-white uppercase tracking-tighter" data-t-key="No listings found">{{ __('No listings found') }}</h3>
                <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold mt-4 max-w-md mx-auto" data-t-key="Try adjusting your search filters or check back later for new harvests.">
                    {{ __('Try adjusting your search filters or check back later for new harvests.') }}
                </p>
                @if(request()->anyFilled(['q', 'category']))
                    <a href="{{ route('marketplace.index') }}" class="mt-10 inline-flex px-10 py-4 bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-600 transition-all">
                        {{ __('Reset All Filters') }}
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-16 reveal">
        {{ $listings->links() }}
    </div>
</div>
@endsection
