<div class="space-y-6 sm:space-y-8 origin-top" x-data="{ show: true }"
    x-transition:enter="transition-all ease-out duration-700"
    x-transition:enter-start="opacity-0 translate-y-20 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100">
    <div
        class="bg-white dark:bg-[#081811] p-6 sm:p-10 lg:p-12 rounded-[2.5rem] sm:rounded-[3.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl relative overflow-hidden group hover:border-emerald-200 dark:hover:border-emerald-800 transition-colors duration-500 printable-area">
        <!-- Background Pattern -->
        <div
            class="absolute -right-32 -top-32 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000 no-print">
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-8 sm:mb-12 gap-6 relative z-10">
            <div class="space-y-3 w-full sm:w-2/3">
                <div
                    class="inline-flex items-center space-x-2 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 px-4 py-1.5 rounded-full text-xs font-black tracking-widest uppercase mb-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span data-t-key="Diagnostic Finding">{{ __('Diagnostic Finding') }}</span>
                </div>
                <h3
                    class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tighter leading-none text-emerald-950 dark:text-white capitalize">
                    {{ $diagnosis->disease }}</h3>
                @if($diagnosis->engine_tier)
                    <div class="mt-4 flex items-center space-x-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600/40 dark:text-emerald-400/40" data-t-key="Source:">{{ __('Source: ') }}</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-900/20 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                            {{ $diagnosis->engine_tier }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto bg-emerald-50/50 dark:bg-emerald-900/20 sm:bg-transparent p-4 sm:p-0 rounded-[1.5rem]">
                <div class="text-xs font-black text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-1" data-t-key="Reliability">
                    {{ __('Reliability') }}</div>
                <div class="flex items-baseline space-x-1">
                    <div
                        class="text-4xl sm:text-5xl font-black text-emerald-700 dark:text-emerald-400 tracking-tighter font-mono">
                        {{ is_numeric($diagnosis->confidence) ? number_format($diagnosis->confidence * 100, 1) : $diagnosis->confidence }}</div>
                    @if(is_numeric($diagnosis->confidence))
                        <div class="text-2xl font-bold text-emerald-600/50">%</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- NEW: Intelligence Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="p-5 rounded-2xl border-2 border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/30 dark:bg-[#0a1e15] flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i data-lucide="gauge" class="w-5 h-5 text-amber-500"></i>
                    <span class="text-sm font-bold text-emerald-900 dark:text-emerald-100" data-t-key="Severity">{{ __('Severity') }}</span>
                </div>
                <span class="font-black text-emerald-950 dark:text-white">{{ $diagnosis->severity ?? 'N/A' }}</span>
            </div>
            <div class="p-5 rounded-2xl border-2 border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/30 dark:bg-[#0a1e15] flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i data-lucide="wind" class="w-5 h-5 text-red-500"></i>
                    <span class="text-sm font-bold text-emerald-900 dark:text-emerald-100" data-t-key="Spread Risk">{{ __('Spread Risk') }}</span>
                </div>
                <span class="font-black text-emerald-950 dark:text-white">{{ $diagnosis->spread_risk ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="space-y-6 sm:space-y-8 relative z-10">
            <div
                class="p-6 sm:p-8 bg-emerald-900 dark:bg-emerald-950 rounded-[2rem] border-2 border-emerald-800 text-white relative shadow-inner">
                <div
                    class="absolute -top-4 left-6 sm:left-8 px-5 py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full text-white text-xs font-black uppercase tracking-widest shadow-lg flex items-center space-x-2">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span data-t-key="Treatment Protocol">{{ __('Treatment Protocol') }}</span>
                </div>
                <div class="flex items-start space-x-5 mt-4">
                    <i data-lucide="flask-conical" class="w-8 h-8 text-amber-400 shrink-0 mt-1 opacity-80"></i>
                    <p class="text-emerald-50 leading-relaxed font-medium text-lg sm:text-xl">
                        {{ $diagnosis->treatment }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                <div
                    class="p-5 sm:p-6 bg-emerald-50 dark:bg-[#0a1e15] rounded-[1.5rem] border border-emerald-100 dark:border-emerald-900/50 text-center">
                    <i data-lucide="calendar"
                        class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-3 text-emerald-600 dark:text-emerald-500 opacity-70"></i>
                    <p
                        class="text-[10px] sm:text-xs font-black text-emerald-700 dark:text-emerald-600/80 uppercase tracking-widest" data-t-key="Analyzed On">
                        {{ __('Analyzed On') }}</p>
                    <p class="text-base sm:text-lg font-black text-emerald-950 dark:text-white mt-1">{{
                        $diagnosis->created_at->format('M d, Y') }}</p>
                </div>
                <div
                    class="p-5 sm:p-6 bg-emerald-50 dark:bg-[#0a1e15] rounded-[1.5rem] border border-emerald-100 dark:border-emerald-900/50 text-center">
                    <i data-lucide="layers"
                        class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-3 text-emerald-600 dark:text-emerald-500 opacity-70"></i>
                    <p
                        class="text-[10px] sm:text-xs font-black text-emerald-700 dark:text-emerald-600/80 uppercase tracking-widest" data-t-key="Data Points">
                        {{ __('Data Points') }}</p>
                    <p class="text-base sm:text-lg font-black text-emerald-950 dark:text-white mt-1">
                        {{ count($diagnosis->image_paths) }} <span data-t-key="Specimens">{{ __('Specimens') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mt-10 sm:mt-12 flex flex-col sm:flex-row gap-4 sm:gap-5 relative z-10 w-full pb-safe sm:pb-0 no-print">
            <button @click="resetForm()"
                class="w-full sm:flex-1 py-5 sm:py-6 bg-emerald-950 dark:bg-emerald-800 hover:bg-black dark:hover:bg-emerald-700 text-white rounded-[1.5rem] font-black shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center space-x-3 text-lg border-2 border-transparent">
                <i data-lucide="refresh-ccw" class="w-6 h-6"></i>
                <span data-t-key="Start New Analysis">{{ __('Start New Analysis') }}</span>
            </button>
            <div class="flex space-x-4 w-full sm:w-auto">
                <button onclick="window.print()"
                    class="flex-1 sm:px-8 py-5 sm:py-6 bg-white dark:bg-[#081811] border-2 border-emerald-200 dark:border-emerald-800 rounded-[1.5rem] text-emerald-900 dark:text-emerald-100 font-bold hover:bg-emerald-50 dark:hover:bg-emerald-900 shadow-sm hover:shadow-md transition-all flex items-center justify-center space-x-2">
                    <i data-lucide="printer" class="w-6 h-6"></i>
                    <span class="sm:hidden text-lg" data-t-key="Print">{{ __('Print') }}</span>
                </button>

                <a href="https://wa.me/?text={{ urlencode(__('AgriAssist Diagnosis') . ': ' . $diagnosis->disease . ' - ' . $diagnosis->treatment) }}"
                    target="_blank"
                    class="flex-1 sm:px-8 py-5 sm:py-6 bg-[#25D366] text-white rounded-[1.5rem] hover:bg-[#128C7E] transition-all flex items-center justify-center shadow-lg border-b-4 border-[#075E54]">
                    <i data-lucide="message-circle" class="w-6 h-6"></i>
                    <span class="sm:hidden text-lg" data-t-key="WhatsApp">{{ __('WhatsApp') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>