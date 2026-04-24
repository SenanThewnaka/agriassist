@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12" x-data="leadsIndex()" @order-placed.window="handleUpdate($event.detail)" @order-status-updated.window="handleUpdate($event.detail)">
    <!-- Header -->
    <div class="mb-12">
        <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 mb-4 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            <span data-t-key="Back to Dashboard">{{ __('Back to Dashboard') }}</span>
        </a>
        <h1 class="text-4xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="Active Negotiations">
            {{ __('Active Negotiations') }}
        </h1>
    </div>

    <!-- Leads List -->
    <div class="bg-white dark:bg-[#081811] rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl overflow-hidden reveal">
        @forelse($leads as $lead)
            <div class="p-8 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all border-b-2 border-emerald-50 last:border-0 group">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-800 rounded-2xl flex items-center justify-center font-black text-xl text-emerald-700 dark:text-emerald-200 shrink-0">
                            {{ substr($lead->buyer->full_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="text-lg font-black text-emerald-950 dark:text-white">{{ $lead->buyer->full_name }}</h3>
                                <span @class([
                                    'px-2 py-0.5 text-[8px] font-black rounded-lg uppercase tracking-tighter',
                                    'bg-blue-50 text-blue-600' => $lead->order_status === 'pending',
                                    'bg-emerald-50 text-emerald-600' => $lead->order_status === 'accepted'
                                ])>{{ $lead->order_status }}</span>
                            </div>
                            <p class="text-xs font-black uppercase text-emerald-600 mb-2">
                                {{ __('Re:') }} {{ $lead->items->first()->listing->title }}
                            </p>
                            <p class="text-[10px] font-black uppercase text-emerald-800/40 dark:text-emerald-400/40 tracking-widest">
                                Rs. {{ number_format($lead->total_price) }} • {{ $lead->items->first()->quantity }} {{ $lead->items->first()->listing->unit }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-4">
                        <span class="text-[10px] font-black text-emerald-800/40 dark:text-emerald-400/40 uppercase tracking-widest">{{ $lead->created_at->format('M d, Y') }}</span>
                        <div class="flex space-x-2">
                            <a href="https://wa.me/{{ $lead->buyer->phone_number }}" target="_blank" class="p-3 bg-[#25D366] text-white rounded-xl shadow-lg hover:scale-110 transition-transform">
                                <i data-lucide="phone" class="w-5 h-5"></i>
                            </a>
                            <a href="{{ route('marketplace.chat', $lead) }}" class="px-6 py-3 bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-600 shadow-xl shadow-emerald-700/20 transition-all flex items-center">
                                <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>
                                {{ __('Open Negotiation') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-20 text-center">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-200 mx-auto mb-6">
                    <i data-lucide="message-square-off" class="w-12 h-12"></i>
                </div>
                <h3 class="text-2xl font-black text-emerald-950 dark:text-white uppercase tracking-tight" data-t-key="No negotiations found">{{ __('No negotiations found') }}</h3>
                <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold mt-2" data-t-key="Buyers will contact you about your active listings.">{{ __('Buyers will contact you about your active listings.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $leads->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    function leadsIndex() {
        return {
            handleUpdate(detail) {
                console.log('Real-time update received on leads index:', detail);
                
                // Refresh the page to show the new negotiation lead or updated status
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        };
    }
</script>
@endsection
