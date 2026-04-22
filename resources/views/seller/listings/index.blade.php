@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="flex justify-between items-end mb-12">
        <div>
            <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                <span data-t-key="Back to Dashboard">{{ __('Back to Dashboard') }}</span>
            </a>
            <h1 class="text-5xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="My Classified Ads">
                {{ __('My Classified Ads') }}
            </h1>
        </div>
        <a href="{{ route('seller.listings.create') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-amber-950 rounded-2xl font-black uppercase tracking-widest shadow-xl transition-all flex items-center group">
            <i data-lucide="plus-circle" class="w-5 h-5 mr-3 group-hover:rotate-90 transition-transform"></i>
            <span data-t-key="New Listing">{{ __('New Listing') }}</span>
        </a>
    </div>

    <!-- Listings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($listings as $listing)
            <div class="bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl overflow-hidden reveal group flex flex-col">
                <!-- Image Header -->
                <div class="relative h-64 bg-emerald-50 dark:bg-emerald-900/20 overflow-hidden">
                    @if($listing->images && count($listing->images) > 0)
                        <img src="{{ Storage::url($listing->images[0]) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-emerald-200">
                            <i data-lucide="image" class="w-12 h-12 mb-2"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ __('No Image') }}</span>
                        </div>
                    @endif
                    
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 bg-emerald-950 text-white text-[10px] font-black rounded-lg uppercase tracking-widest shadow-2xl border border-emerald-500/30">
                            {{ $listing->category }}
                        </span>
                    </div>

                    <div class="absolute top-4 right-4">
                        <span @class([
                            'px-3 py-1 text-[10px] font-black rounded-lg uppercase tracking-widest shadow-lg',
                            'bg-emerald-500 text-white' => $listing->status === 'active',
                            'bg-amber-500 text-amber-950' => $listing->status === 'sold',
                            'bg-gray-500 text-white' => $listing->status === 'archived',
                        ])>
                            {{ $listing->status }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-black text-emerald-950 dark:text-white uppercase leading-tight">{{ $listing->title }}</h3>
                        <p class="text-lg font-black text-emerald-600">Rs. {{ number_format($listing->price) }}</p>
                    </div>

                    <div class="flex items-center space-x-4 mb-6 text-[10px] font-black uppercase tracking-widest text-emerald-800/40 dark:text-emerald-400/40">
                        <div class="flex items-center">
                            <i data-lucide="package" class="w-3 h-3 mr-1.5"></i>
                            {{ $listing->quantity }} {{ $listing->unit }}
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="map-pin" class="w-3 h-3 mr-1.5"></i>
                            {{ Str::limit($listing->location, 20) }}
                        </div>
                    </div>

                    <p class="text-sm text-emerald-800/60 dark:text-emerald-400/60 font-bold mb-8 line-clamp-2 flex-1">{{ $listing->description }}</p>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-6 border-t-2 border-emerald-50 dark:border-emerald-900/50 mt-auto">
                        <a href="{{ route('seller.listings.edit', $listing) }}" class="flex-1 py-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-100 transition-all flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i>
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('seller.listings.destroy', $listing) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-3 bg-red-50 dark:bg-red-950/20 text-red-600 rounded-xl hover:bg-red-100 transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white dark:bg-[#081811] rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-200 mx-auto mb-6">
                    <i data-lucide="clipboard-list" class="w-12 h-12"></i>
                </div>
                <h3 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-tight" data-t-key="No active classifieds">{{ __('No active classifieds') }}</h3>
                <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold mt-2" data-t-key="Start by creating your first listing to reach buyers.">{{ __('Start by creating your first listing to reach buyers.') }}</p>
                <a href="{{ route('seller.listings.create') }}" class="mt-8 inline-flex items-center px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-700 transition-all">
                    <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>
                    {{ __('Create Listing') }}
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-12">
        {{ $listings->links() }}
    </div>
</div>
@endsection
