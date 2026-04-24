@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12" x-data="negotiationRoom('{{ $order->id }}', {{ auth()->id() }}, '{{ $order->buyer_id }}')" @order-status-updated.window="handleStatusUpdate($event.detail)">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600/50">
                    <li><a href="{{ route('marketplace.index') }}" class="hover:text-emerald-600 transition-colors">{{ __('Marketplace') }}</a></li>
                    <li><i data-lucide="chevron-right" class="w-3 h-3"></i></li>
                    <li class="text-emerald-600">{{ __('Negotiation Room') }}</li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase leading-none">
                {{ __('Order') }} #{{ substr($order->id, 0, 8) }}
            </h1>
        </div>

        <!-- Order Status Badge -->
        <div class="flex items-center space-x-4">
            <span @class([
                'px-6 py-3 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl',
                'bg-blue-500 text-white shadow-blue-500/20' => $order->order_status === 'pending',
                'bg-emerald-500 text-white shadow-emerald-500/20' => $order->order_status === 'accepted',
                'bg-red-500 text-white shadow-red-500/20' => $order->order_status === 'rejected',
            ])>
                {{ $order->order_status }}
            </span>

            @if($order->seller_id === auth()->id() && $order->order_status === 'pending')
                <div class="flex space-x-2">
                    <button @click="updateStatus('accept')" class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-500 transition-all shadow-lg">
                        {{ __('Accept') }}
                    </button>
                    <button @click="updateStatus('reject')" class="px-6 py-3 bg-white text-red-600 border-2 border-red-100 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-50 transition-all shadow-lg">
                        {{ __('Reject') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Chat Area -->
        <div class="lg:col-span-2 flex flex-col h-[700px] bg-white dark:bg-[#081811] rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl overflow-hidden reveal">
            <!-- Messages List -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-8 space-y-6 scroll-smooth">
                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.sender_id == currentUserId ? 'flex flex-row-reverse' : 'flex flex-row'">
                        <div :class="msg.sender_id == currentUserId ? 'bg-emerald-700 text-white rounded-t-3xl rounded-l-3xl shadow-emerald-700/10' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-950 dark:text-emerald-50 rounded-t-3xl rounded-r-3xl border-2 border-emerald-100/50 dark:border-emerald-800/50'"
                             class="max-w-[80%] p-6 shadow-xl relative">
                            <p class="text-sm font-bold leading-relaxed" x-text="msg.message"></p>
                            <span class="block text-[8px] font-black uppercase mt-3 opacity-40 tracking-widest" x-text="formatTime(msg.created_at)"></span>
                        </div>
                    </div>
                </template>

                <div x-show="messages.length === 0" class="h-full flex flex-col items-center justify-center text-center p-12">
                    <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-200 mb-6">
                        <i data-lucide="message-circle" class="w-10 h-10"></i>
                    </div>
                    <h4 class="text-xl font-black text-emerald-950 dark:text-white uppercase">{{ __('No messages yet') }}</h4>
                    <p class="text-sm text-emerald-800/40 dark:text-emerald-400/40 font-bold mt-2">{{ __('Start the negotiation by sending a message below.') }}</p>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-8 bg-emerald-50/30 dark:bg-emerald-950/20 border-t-4 border-emerald-50 dark:border-emerald-900/50">
                <div class="relative group">
                    <textarea x-model="newMessage" @keydown.enter.prevent="sendMessage()" 
                        class="w-full pl-6 pr-20 py-5 bg-white dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-[2rem] outline-none focus:border-emerald-500 font-bold text-emerald-950 dark:text-white placeholder-emerald-800/20 transition-all shadow-inner"
                        placeholder="{{ __('Type your message here...') }}"></textarea>
                    <button @click="sendMessage()" :disabled="!newMessage.trim()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 p-4 bg-emerald-700 text-white rounded-2xl hover:bg-emerald-600 disabled:opacity-50 transition-all shadow-xl shadow-emerald-700/20">
                        <i data-lucide="send" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar: Order Context -->
        <div class="space-y-8">
            <!-- Product Card -->
            <div class="bg-emerald-950 p-8 rounded-[3rem] border-4 border-emerald-800 shadow-2xl relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-[10px] font-black uppercase text-emerald-400/60 tracking-widest mb-6">{{ __('Negotiating For') }}</h3>
                    
                    <div class="flex items-center space-x-4 mb-8">
                        <div class="w-16 h-16 bg-emerald-900 rounded-2xl overflow-hidden border-2 border-emerald-800">
                            @if($order->items->first()->listing->images)
                                <img src="{{ Storage::url($order->items->first()->listing->images[0]) }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div>
                            <h4 class="text-white font-black text-lg leading-tight uppercase">{{ $order->items->first()->listing->title }}</h4>
                            <p class="text-[10px] font-black uppercase text-emerald-400/60">{{ $order->items->first()->listing->category }}</p>
                        </div>
                    </div>

                    <div class="space-y-4 pt-6 border-t border-white/10">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase text-emerald-400/40">{{ __('Quantity') }}</span>
                            <span class="text-white font-black">{{ $order->items->first()->quantity }} {{ $order->items->first()->listing->unit }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase text-emerald-400/40">{{ __('Price') }}</span>
                            <span class="text-white font-black">Rs. {{ number_format($order->items->first()->price) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-white/10">
                            <span class="text-[10px] font-black uppercase text-amber-500">{{ __('Order Total') }}</span>
                            <span class="text-2xl font-black text-amber-500">Rs. {{ number_format($order->total_price) }}</span>
                        </div>
                    </div>
                </div>
                <i data-lucide="shopping-bag" class="absolute -bottom-8 -right-8 w-40 h-40 text-emerald-900/10"></i>
            </div>

            <!-- Review Prompt (Verified Buyers Only) -->
            @if($order->buyer_id === auth()->id() && $order->order_status === 'accepted')
                <div class="p-8 bg-amber-500 rounded-[3rem] border-4 border-amber-400 shadow-2xl reveal relative overflow-hidden" x-show="!reviewSubmitted">
                    <div class="relative z-10 text-amber-950">
                        <h4 class="text-xl font-black uppercase tracking-tighter mb-2">{{ __('Rate Your Experience') }}</h4>
                        <p class="text-xs font-bold mb-6 opacity-80">{{ __('Only buyers of accepted orders can leave a review.') }}</p>
                        
                        <div class="flex space-x-2 mb-6">
                            <template x-for="i in 5">
                                <button @click="rating = i" class="transition-transform active:scale-90">
                                    <i data-lucide="star" :class="i <= rating ? 'fill-amber-950' : 'opacity-20'" class="w-8 h-8"></i>
                                </button>
                            </template>
                        </div>

                        <textarea x-model="reviewComment" rows="3" class="w-full px-4 py-3 bg-white/30 border-2 border-amber-600/20 rounded-2xl outline-none focus:border-amber-600 font-bold placeholder-amber-900/40 text-sm mb-4" placeholder="{{ __('Write a quick review...') }}"></textarea>
                        
                        <button @click="submitReview()" :disabled="!rating || isSubmittingReview" class="w-full py-4 bg-amber-950 text-white rounded-xl font-black uppercase text-xs tracking-widest shadow-xl transition-all">
                            {{ __('Submit Verified Review') }}
                        </button>
                    </div>
                    <i data-lucide="award" class="absolute -bottom-6 -right-6 w-32 h-32 text-amber-600/20"></i>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function negotiationRoom(orderId, currentUserId, buyerId) {
        return {
            orderId: orderId,
            currentUserId: currentUserId,
            messages: [],
            newMessage: '',
            rating: 5,
            reviewComment: '',
            isSubmittingReview: false,
            reviewSubmitted: false,

            init() {
                this.fetchMessages();
                
                // Real-Time Upgrade: Use Laravel Echo instead of polling
                if (window.Echo) {
                    console.log('Echo initialized, joining channel: order.' + this.orderId);
                    window.Echo.private(`order.${this.orderId}`)
                        .subscribed(() => {
                            console.log('Successfully subscribed to private channel: order.' + this.orderId);
                        })
                        .listen('.message.sent', (e) => {
                            console.log('Real-time message received:', e);
                            this.messages.push(e);
                            this.scrollToBottom();
                        });
                } else {
                    // Fallback to polling if Echo is not initialized
                    setInterval(() => this.fetchMessages(), 3000);
                }

                if (window.lucide) lucide.createIcons();
            },

            handleStatusUpdate(detail) {
                // If the update is for this order, refresh the page to show the new status and UI changes
                if (detail.id == this.orderId) {
                    console.log('Order status updated in chat room:', detail);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },

            async fetchMessages() {
                try {
                    const res = await fetch(`/marketplace/orders/${this.orderId}/messages`);
                    const data = await res.json();
                    this.messages = data;
                    this.scrollToBottom();
                } catch (e) { console.error('Chat sync failed'); }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('chat-messages');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                        if (window.lucide) lucide.createIcons();
                    }
                });
            },

            async sendMessage() {
                if (!this.newMessage.trim()) return;
                const msgText = this.newMessage;
                this.newMessage = '';

                try {
                    await fetch(`/marketplace/orders/${this.orderId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: msgText })
                    });
                    // Note: We don't push to array here manually because 
                    // Echo will receive the broadcast and push it for us!
                } catch (e) { alert('Message failed to send.'); }
            },

            async updateStatus(action) {
                if (!confirm(`Are you sure you want to ${action} this order?`)) return;
                try {
                    const res = await fetch(`/seller/orders/${this.orderId}/${action}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error);
                    }
                } catch (e) { alert('Status update failed.'); }
            },

            async submitReview() {
                this.isSubmittingReview = true;
                try {
                    const res = await fetch('{{ route("marketplace.review.store", $order->items->first()->listing_id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            rating: this.rating,
                            comment: this.reviewComment
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.reviewSubmitted = true;
                        window.showToast(data.message, 'success');
                    } else {
                        alert(data.error);
                    }
                } catch (e) { alert('Review submission failed.'); }
                finally { this.isSubmittingReview = false; }
            },

            formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
    }
</script>
@endpush
