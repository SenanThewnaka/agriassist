<div x-data="consentManager()" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     class="fixed bottom-0 inset-x-0 z-[100] p-4 sm:p-6" x-cloak>
    
    <div class="max-w-5xl mx-auto bg-white/95 dark:bg-[#081811]/95 border-2 border-emerald-100 dark:border-emerald-900 backdrop-blur-2xl rounded-[2.5rem] shadow-[0_-20px_60px_rgba(0,0,0,0.15)] p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
        
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>

        <div class="flex items-start space-x-5 relative z-10">
            <div class="w-14 h-14 bg-emerald-700 rounded-2xl flex items-center justify-center text-amber-300 shadow-lg shrink-0">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <div class="space-y-2">
                <h4 class="text-xl font-black tracking-tight text-emerald-950 dark:text-white" data-t-key="Privacy & Intelligence Control">{{ __('Privacy & Intelligence Control') }}</h4>
                <p class="text-sm font-medium text-emerald-800/70 dark:text-emerald-400/70 leading-relaxed max-w-2xl">
                    {{ __('AgriAssist uses localized GPS for soil mapping and cloud AI (Gemini/Llama) for disease analysis. We strip all metadata from your photos before processing to protect your privacy.') }}
                    <a href="/privacy" class="text-emerald-600 dark:text-emerald-400 font-black hover:underline ml-1" data-t-key="Learn more in our Privacy Policy">{{ __('Learn more in our Privacy Policy') }}</a>.
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-4 shrink-0 relative z-10 w-full md:w-auto">
            <button @click="accept()" class="flex-1 md:flex-none px-8 py-4 bg-emerald-700 hover:bg-emerald-600 text-white rounded-2xl font-black shadow-lg transition-all active:scale-95" data-t-key="I Understand">{{ __('I Understand') }}</button>
        </div>
    </div>
</div>

<script>
window.consentManager = function() {
    return {
        show: false,
        init() {
            if (!localStorage.getItem('agriassist_consent')) {
                setTimeout(() => {
                    this.show = true;
                    if (window.lucide) window.lucide.createIcons();
                }, 1000);
            }
        },
        accept() {
            localStorage.setItem('agriassist_consent', 'true');
            this.show = false;
        }
    }
}
</script>
