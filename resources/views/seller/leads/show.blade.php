@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="mb-12">
        <a href="{{ route('seller.leads.index') }}" class="inline-flex items-center text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 mb-4 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            <span data-t-key="Back to Inquiries">{{ __('Back to Inquiries') }}</span>
        </a>
        <h1 class="text-4xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="Inquiry Detail">
            {{ __('Inquiry Detail') }}
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Inquiry Content -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#081811] p-10 rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl reveal">
                <div class="flex items-center space-x-4 mb-8 pb-8 border-b-2 border-emerald-50 dark:border-emerald-900/50">
                    <div class="w-16 h-14 bg-emerald-700 text-white rounded-2xl flex items-center justify-center font-black text-2xl">
                        {{ substr($message->sender->full_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-emerald-950 dark:text-white">{{ $message->sender->full_name }}</h2>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500">{{ __('Potential Buyer') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="text-[10px] font-black uppercase tracking-widest text-emerald-800/40 dark:text-emerald-400/60">{{ __('Message Content') }}</div>
                    <div class="p-8 bg-emerald-50/50 dark:bg-emerald-950/40 rounded-[2rem] border-2 border-emerald-100 dark:border-emerald-900">
                        <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100 leading-relaxed italic">
                            "{{ $message->message }}"
                        </p>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t-2 border-emerald-50 dark:border-emerald-900/50 flex flex-col sm:flex-row gap-4">
                    <a href="https://wa.me/{{ $message->sender->phone_number }}" target="_blank" class="flex-1 py-5 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-[1.5rem] font-black uppercase tracking-widest transition-all flex items-center justify-center shadow-xl shadow-green-600/20">
                        <i data-lucide="phone" class="w-6 h-6 mr-3"></i>
                        {{ __('Reply via WhatsApp') }}
                    </a>
                    <a href="mailto:{{ $message->sender->email }}" class="flex-1 py-5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest transition-all flex items-center justify-center shadow-xl shadow-emerald-700/20">
                        <i data-lucide="mail" class="w-6 h-6 mr-3"></i>
                        {{ __('Reply via Email') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Listing Context Card -->
        <div class="space-y-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-emerald-800/40 dark:text-emerald-400/60 px-4">{{ __('Listing Context') }}</h3>
            <div class="bg-emerald-950 rounded-[2.5rem] border-4 border-emerald-800 shadow-2xl overflow-hidden group">
                <div class="h-48 bg-emerald-900 relative">
                    @if($message->listing->images && count($message->listing->images) > 0)
                        <img src="{{ Storage::url($message->listing->images[0]) }}" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-emerald-800">
                            <i data-lucide="image" class="w-12 h-12"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h4 class="text-white font-black text-xl leading-tight uppercase">{{ $message->listing->title }}</h4>
                    </div>
                </div>
                <div class="p-8 space-y-6 text-emerald-50">
                    <div class="flex justify-between items-center border-b border-white/10 pb-4">
                        <span class="text-[10px] font-black uppercase opacity-40">{{ __('Price') }}</span>
                        <span class="font-black text-emerald-400">Rs. {{ number_format($message->listing->price) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-white/10 pb-4">
                        <span class="text-[10px] font-black uppercase opacity-40">{{ __('Quantity') }}</span>
                        <span class="font-bold">{{ $message->listing->quantity }} {{ $message->listing->unit }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase opacity-40">{{ __('Status') }}</span>
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-[8px] font-black rounded-full uppercase border border-emerald-500/30">{{ $message->listing->status }}</span>
                    </div>
                    
                    <a href="{{ route('seller.listings.edit', $message->listing) }}" class="w-full py-4 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center">
                        {{ __('Manage Ad') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
