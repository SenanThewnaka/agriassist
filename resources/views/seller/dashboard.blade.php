@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="sellerDashboard()" @order-placed.window="handleUpdate($event.detail)" @order-status-updated.window="handleUpdate($event.detail)">
    <!-- Header -->
    <div class="flex justify-between items-end mb-12">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600/50">
                    <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a></li>
                    <li><i data-lucide="chevron-right" class="w-3 h-3"></i></li>
                    <li class="text-emerald-600">{{ __('Seller Portal') }}</li>
                </ol>
            </nav>
            <h1 class="text-5xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="Seller Dashboard">
                {{ __('Seller Dashboard') }}
            </h1>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('seller.listings.create') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-amber-950 rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-amber-500/20 transition-all flex items-center group">
                <i data-lucide="plus-circle" class="w-5 h-5 mr-3 group-hover:rotate-90 transition-transform"></i>
                <span data-t-key="New Listing">{{ __('New Listing') }}</span>
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal">
            <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600 mb-6">
                <i data-lucide="shopping-bag" class="w-8 h-8"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-800/40 dark:text-emerald-400/40 mb-1" data-t-key="Active Listings">{{ __('Active Listings') }}</p>
            <h3 class="text-4xl font-black text-emerald-950 dark:text-white">{{ $stats['active_listings'] }}</h3>
        </div>

        <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal" style="transition-delay: 100ms">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-blue-600 mb-6">
                <i data-lucide="message-square" class="w-8 h-8"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-800/40 dark:text-emerald-400/40 mb-1" data-t-key="Total Inquiries">{{ __('Total Inquiries') }}</p>
            <h3 class="text-4xl font-black text-emerald-950 dark:text-white">{{ $stats['total_leads'] }}</h3>
        </div>

        <div class="bg-emerald-950 p-8 rounded-[2.5rem] border-4 border-emerald-800 shadow-2xl reveal relative overflow-hidden group" style="transition-delay: 200ms">
            <div class="relative z-10">
                <div class="w-14 h-14 bg-emerald-800 rounded-2xl flex items-center justify-center text-emerald-400 mb-6">
                    <i data-lucide="trending-up" class="w-8 h-8"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400/60 mb-1" data-t-key="Market Exposure">{{ __('Market Exposure') }}</p>
                <h3 class="text-4xl font-black text-white uppercase">{{ __('High') }}</h3>
            </div>
            <i data-lucide="activity" class="absolute -bottom-4 -right-4 w-32 h-32 text-emerald-800/20 group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Recent Negotiations -->
        <div class="space-y-6">
            <h3 class="text-2xl font-black tracking-tight text-emerald-950 dark:text-white uppercase flex items-center">
                <i data-lucide="inbox" class="w-6 h-6 mr-3 text-emerald-600"></i>
                <span data-t-key="Active Negotiations">{{ __('Active Negotiations') }}</span>
            </h3>
            
            <div class="bg-white dark:bg-[#081811] rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl overflow-hidden reveal">
                @if($stats['recent_inquiries']->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/20 rounded-3xl flex items-center justify-center text-emerald-300 mx-auto mb-6">
                            <i data-lucide="mail-question" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-emerald-950 dark:text-white" data-t-key="No inquiries yet">{{ __('No inquiries yet') }}</h4>
                        <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold mt-2" data-t-key="Buyers will contact you about your active listings.">{{ __('Buyers will contact you about your active listings.') }}</p>
                    </div>
                @else
                    <div class="divide-y-2 divide-emerald-50 dark:divide-emerald-900/50">
                        @foreach($stats['recent_inquiries'] as $order)
                            <a href="{{ route('marketplace.chat', $order) }}" class="block p-6 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center font-black text-emerald-700 dark:text-emerald-200">
                                            {{ substr($order->buyer->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-black text-emerald-950 dark:text-white group-hover:text-emerald-600 transition-colors">{{ $order->buyer->full_name }}</h4>
                                            <p class="text-[10px] font-black uppercase text-emerald-500">{{ $order->items->first()->listing->title }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-black text-emerald-800/40 dark:text-emerald-400/40 uppercase">{{ $order->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 text-[8px] font-black rounded uppercase">{{ $order->order_status }}</span>
                                    <span class="text-xs font-black text-emerald-950 dark:text-white">Rs. {{ number_format($order->total_price) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="p-6 bg-emerald-50/50 dark:bg-[#0a1e15] border-t-2 border-emerald-50 dark:border-emerald-900/50">
                        <a href="{{ route('seller.leads.index') }}" class="text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 flex items-center">
                            <span data-t-key="View All Negotiations">{{ __('View All Negotiations') }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions / Guidelines -->
        <div class="space-y-6">
            <h3 class="text-2xl font-black tracking-tight text-emerald-950 dark:text-white uppercase flex items-center">
                <i data-lucide="zap" class="w-6 h-6 mr-3 text-amber-500"></i>
                <span data-t-key="Seller Toolkit">{{ __('Seller Toolkit') }}</span>
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('seller.listings.index') }}" class="p-8 bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl hover:border-emerald-500 transition-all reveal group">
                    <i data-lucide="list" class="w-8 h-8 text-emerald-600 mb-4 group-hover:scale-110 transition-transform"></i>
                    <h4 class="font-black text-emerald-950 dark:text-white uppercase text-sm tracking-tight" data-t-key="Manage Listings">{{ __('Manage Listings') }}</h4>
                </a>
                <a href="{{ route('seller.listings.create') }}" class="p-8 bg-white dark:bg-[#081811] rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl hover:border-emerald-500 transition-all reveal group" style="transition-delay: 100ms">
                    <i data-lucide="file-plus" class="w-8 h-8 text-amber-500 mb-4 group-hover:scale-110 transition-transform"></i>
                    <h4 class="font-black text-emerald-950 dark:text-white uppercase text-sm tracking-tight" data-t-key="Create New Ad">{{ __('Create New Ad') }}</h4>
                </a>
            </div>

            <div class="bg-emerald-950 p-8 rounded-[3rem] border-4 border-emerald-800 shadow-2xl relative overflow-hidden reveal" style="transition-delay: 200ms">
                <div class="relative z-10">
                    <h4 class="text-emerald-400 text-xs font-black uppercase tracking-widest mb-4" data-t-key="Selling Tips">{{ __('Selling Tips') }}</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start space-x-3">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                            <p class="text-white text-sm font-bold leading-tight" data-t-key="High-quality photos increase inquiries by 80%.">{{ __('High-quality photos increase inquiries by 80%.') }}</p>
                        </li>
                        <li class="flex items-start space-x-3">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                            <p class="text-white text-sm font-bold leading-tight" data-t-key="Include clear quantity and unit information (e.g., 500kg).">{{ __('Include clear quantity and unit information (e.g., 500kg).') }}</p>
                        </li>
                        <li class="flex items-start space-x-3">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                            <p class="text-white text-sm font-bold leading-tight" data-t-key="Pin your exact location to attract local buyers.">{{ __('Pin your exact location to attract local buyers.') }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function sellerDashboard() {
        return {
            handleUpdate(detail) {
                console.log('Real-time update received on dashboard:', detail);
                
                // We refresh the page to update all statistics and lists
                // We add a small delay so the user can see the notification toast first
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        };
    }
</script>
@endsection
