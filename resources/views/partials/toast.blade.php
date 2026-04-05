<div x-data="toastManager()" 
     @toast.window="add($event.detail)"
     class="fixed bottom-10 right-6 sm:right-10 z-[100] flex flex-col gap-4 pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-10 scale-90 blur-xl"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90 blur-lg"
             class="pointer-events-auto min-w-[300px] max-w-md p-5 rounded-[2rem] border border-white/10 backdrop-blur-2xl shadow-2xl flex items-center gap-5 relative overflow-hidden group"
             :class="{
                'bg-emerald-500/10 border-emerald-500/30': toast.type === 'success',
                'bg-red-500/10 border-red-500/30': toast.type === 'error',
                'bg-amber-500/10 border-amber-500/30': toast.type === 'warning',
                'bg-blue-500/10 border-blue-500/30': toast.type === 'info'
             }">
            
            <!-- Glow Effect -->
            <div class="absolute inset-0 opacity-20 transition-opacity duration-500 group-hover:opacity-30"
                 :class="{
                    'bg-emerald-500 blur-3xl': toast.type === 'success',
                    'bg-red-500 blur-3xl': toast.type === 'error',
                    'bg-amber-500 blur-3xl': toast.type === 'warning',
                    'bg-blue-500 blur-3xl': toast.type === 'info'
                 }"></div>

            <!-- Icon -->
            <div class="w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center border border-white/10 relative z-10"
                 :class="{
                    'bg-emerald-500/20 text-emerald-400': toast.type === 'success',
                    'bg-red-500/20 text-red-400': toast.type === 'error',
                    'bg-amber-500/20 text-amber-400': toast.type === 'warning',
                    'bg-blue-500/20 text-blue-400': toast.type === 'info'
                 }">
                <i :data-lucide="toast.icon || getIcon(toast.type)" class="w-6 h-6"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 relative z-10">
                <h4 class="font-black text-xs uppercase tracking-widest opacity-60 mb-1" x-text="toast.title || getTitle(toast.type)"></h4>
                <p class="text-white font-bold leading-tight" x-text="toast.message"></p>
            </div>

            <!-- Close -->
            <button @click="remove(toast.id)" class="p-2 opacity-40 hover:opacity-100 transition-opacity relative z-10 text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <!-- Progress bar -->
            <div class="absolute bottom-0 left-0 h-1 bg-white/10 transition-all duration-linear" 
                 :style="{ width: toast.progress + '%' }"></div>
        </div>
    </template>
</div>

<script>
window.toastManager = function() {
    return {
        toasts: [],
        add(detail) {
            const id = Date.now();
            const toast = {
                id,
                visible: false,
                type: detail.type || 'info',
                message: detail.message,
                title: detail.title,
                icon: detail.icon,
                duration: detail.duration || 5000,
                progress: 100
            };
            
            this.toasts.push(toast);
            
            this.$nextTick(() => {
                toast.visible = true;
                if (window.lucide) window.lucide.createIcons();
                
                const startTime = Date.now();
                const interval = setInterval(() => {
                    const elapsed = Date.now() - startTime;
                    toast.progress = 100 - (elapsed / toast.duration * 100);
                    
                    if (elapsed >= toast.duration) {
                        clearInterval(interval);
                        this.remove(id);
                    }
                }, 10);
            });
        },
        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },
        getIcon(type) {
            return {
                success: 'check-circle-2',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            }[type];
        },
        getTitle(type) {
            return {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Update'
            }[type];
        }
    }
}
</script>
