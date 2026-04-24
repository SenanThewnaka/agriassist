@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12" x-data="inboxApp()" @order-placed.window="handleUpdate()" @order-status-updated.window="handleUpdate()">
    <!-- Header -->
    <div class="mb-12 flex justify-between items-end">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600/50">
                    <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a></li>
                    <li><i data-lucide="chevron-right" class="w-3 h-3"></i></li>
                    <li class="text-emerald-600">{{ __('Messages') }}</li>
                </ol>
            </nav>
            <h1 class="text-5xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase leading-none" data-t-key="AgriMessenger">
                {{ __('AgriMessenger') }}
            </h1>
        </div>
        <div class="text-right hidden sm:block">
            <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 tracking-widest">{{ __('Active Negotiations') }}</p>
            <p class="text-2xl font-black text-emerald-600">{{ $negotiations->count() }}</p>
        </div>
    </div>

    <!-- Negotiations List -->
    <div class="bg-white dark:bg-[#081811] rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl overflow-hidden reveal">
        @forelse($negotiations as $order)
            <a href="{{ route('marketplace.chat', $order) }}" class="block p-8 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all border-b-2 border-emerald-50 dark:border-emerald-900/50 last:border-0 group">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex items-start space-x-6 flex-1 min-w-0">
                        <!-- Avatar / Initials -->
                        <div class="relative shrink-0">
                            <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-800 rounded-2xl flex items-center justify-center font-black text-2xl text-emerald-700 dark:text-emerald-200 shadow-lg group-hover:scale-105 transition-transform">
                                {{ substr($order->other_party->full_name, 0, 1) }}
                            </div>
                            <div @class([
                                'absolute -bottom-1 -right-1 w-6 h-6 rounded-lg border-4 border-white dark:border-[#081811] flex items-center justify-center shadow-sm',
                                'bg-blue-500' => !$order->is_seller,
                                'bg-amber-500' => $order->is_seller
                            ]) title="{{ $order->is_seller ? __('You are the Seller') : __('You are the Buyer') }}">
                                <i data-lucide="{{ $order->is_seller ? 'tag' : 'shopping-bag' }}" class="w-3 h-3 text-white"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3 mb-1">
                                <h3 class="text-xl font-black text-emerald-950 dark:text-white truncate group-hover:text-emerald-600 transition-colors">{{ $order->other_party->full_name }}</h3>
                                <span @class([
                                    'px-2 py-0.5 text-[8px] font-black rounded-lg uppercase tracking-tighter shadow-sm',
                                    'bg-blue-50 text-blue-600' => $order->order_status === 'pending',
                                    'bg-emerald-50 text-emerald-600' => $order->order_status === 'accepted',
                                    'bg-red-50 text-red-600' => $order->order_status === 'rejected' || $order->order_status === 'cancelled'
                                ])>{{ $order->order_status }}</span>
                            </div>
                            <p class="text-xs font-black uppercase text-emerald-600 mb-2 truncate">
                                {{ $order->items->first()->listing->title }}
                            </p>
                            <p class="text-sm text-emerald-800/60 dark:text-emerald-400/60 font-bold leading-relaxed truncate">
                                @if($order->latest_msg)
                                    <span class="text-emerald-950 dark:text-emerald-100 italic">"{{ $order->latest_msg->message }}"</span>
                                @else
                                    <span class="opacity-40 italic">{{ __('No messages yet. Start the negotiation.') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Meta -->
                    <div class="flex flex-col items-end gap-3 shrink-0">
                        <span class="text-[10px] font-black text-emerald-800/40 dark:text-emerald-400/40 uppercase tracking-widest">
                            {{ ($order->latest_msg ? $order->latest_msg->created_at : $order->created_at)->diffForHumans() }}
                        </span>
                        <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="p-32 text-center reveal">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-200 mx-auto mb-8">
                    <i data-lucide="message-square-off" class="w-12 h-12"></i>
                </div>
                <h3 class="text-3xl font-black text-emerald-950 dark:text-white uppercase tracking-tighter" data-t-key="Inbox is empty">{{ __('Inbox is empty') }}</h3>
                <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold mt-4 max-w-md mx-auto" data-t-key="Browse the marketplace to find harvests and start your first negotiation.">
                    {{ __('Browse the marketplace to find harvests and start your first negotiation.') }}
                </p>
                <a href="{{ route('marketplace.index') }}" class="mt-10 inline-flex px-10 py-4 bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-700/20">
                    {{ __('Browse Marketplace') }}
                </a>
            </div>
        @endforelse
    </div>

    <!-- Tips -->
    <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-6 reveal" style="transition-delay: 200ms">
        <div class="p-8 bg-blue-50 dark:bg-blue-900/20 rounded-[2.5rem] border-2 border-blue-100 dark:border-blue-900 flex items-start space-x-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/40 rounded-xl text-blue-600 shrink-0">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-black text-blue-950 dark:text-blue-200 uppercase text-sm mb-1">{{ __('Negotiation Tip') }}</h4>
                <p class="text-xs font-bold text-blue-800/60 dark:text-blue-300/60 leading-relaxed">{{ __('Always discuss quality and delivery dates before accepting an order. Real-time updates help close deals faster!') }}</p>
            </div>
        </div>
        <div class="p-8 bg-amber-50 dark:bg-amber-900/20 rounded-[2.5rem] border-2 border-amber-100 dark:border-amber-900 flex items-start space-x-4">
            <div class="p-3 bg-amber-100 dark:bg-amber-900/40 rounded-xl text-amber-600 shrink-0">
                <i data-lucide="star" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-black text-amber-950 dark:text-amber-200 uppercase text-sm mb-1">{{ __('Verified Status') }}</h4>
                <p class="text-xs font-bold text-amber-800/60 dark:text-amber-300/60 leading-relaxed">{{ __('Once an order is accepted, buyers can leave a verified review. Good ratings improve your search visibility.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function inboxApp() {
        return {
            handleUpdate() {
                console.log('Update received in inbox');
                // Refresh list after toast
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        };
    }
</script>
@endsection
