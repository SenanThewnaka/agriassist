@extends('layouts.app')

@push('scripts')
<script>
    window.__AGRI_DATA = {
        userFarms: @json($user->farms)
    };
</script>
@vite(['resources/js/pages/profile.js'])
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar: Identity Module -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl text-center reveal">
                <div class="relative inline-block mb-6">
                    <div class="w-32 h-32 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center text-emerald-700 dark:text-emerald-400 border-4 border-white dark:border-emerald-800 shadow-inner overflow-hidden">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" class="w-full h-full object-cover" id="profile-display">
                        @else
                            <i data-lucide="user" class="w-16 h-16 opacity-50" id="profile-icon"></i>
                            <img src="" class="w-full h-full object-cover hidden" id="profile-display">
                        @endif
                    </div>
                    <button onclick="document.getElementById('photo-upload').click()" class="absolute bottom-0 right-0 bg-amber-400 p-2 rounded-xl shadow-lg border-2 border-white dark:border-emerald-900 hover:scale-110 transition-transform">
                        <i data-lucide="camera" class="w-4 h-4 text-amber-950"></i>
                    </button>
                    <input type="file" id="photo-upload" class="hidden" accept="image/*" onchange="uploadPhoto(this)">
                </div>
                <h2 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white">{{ $user->full_name }}</h2>
                <div class="mt-2 inline-flex items-center px-4 py-1 bg-emerald-100 dark:bg-emerald-900/40 rounded-full text-xs font-black uppercase tracking-widest text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                    {{ ucfirst($user->role) }}
                </div>
                <p class="mt-6 text-emerald-800/70 dark:text-emerald-400/70 font-bold text-sm italic" data-t-key="Cultivating the future with AgriAssist intelligence.">{{ $user->bio ?? __('Cultivating the future with AgriAssist intelligence.') }}</p>
            </div>

            <!-- Quick Contacts -->
            <div class="bg-white dark:bg-[#081811] p-6 rounded-[2rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl space-y-4 reveal">
                <div class="flex items-center space-x-4 p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl">
                    <i data-lucide="mail" class="w-5 h-5 text-emerald-600"></i>
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600/60" data-t-key="Email">{{ __('Email') }}</p>
                        <p class="font-bold text-emerald-950 dark:text-white truncate">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl">
                    <i data-lucide="phone" class="w-5 h-5 text-emerald-600"></i>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600/60" data-t-key="Phone">{{ __('Phone') }}</p>
                        <p class="font-bold text-emerald-950 dark:text-white">{{ $user->phone_number ?? '--' }}</p>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div x-data="{ open: false }" class="bg-red-50/50 dark:bg-red-950/10 p-6 rounded-[2rem] border-4 border-red-100 dark:border-red-900/30 shadow-xl reveal">
                <h4 class="text-xs font-black uppercase tracking-widest text-red-600 mb-4" data-t-key="Danger Zone">{{ __('Danger Zone') }}</h4>
                <button @click="open = true" class="w-full flex items-center justify-between p-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl transition-all shadow-lg group">
                    <span class="font-black text-sm uppercase tracking-widest" data-t-key="Delete Account">{{ __('Delete Account') }}</span>
                    <i data-lucide="trash-2" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
                </button>

                <!-- Delete Confirmation Modal -->
                <div x-show="open" 
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-red-950/90 backdrop-blur-md"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div @click.away="open = false" 
                         class="bg-white dark:bg-[#081811] w-full max-w-md p-8 sm:p-10 rounded-[3rem] border-4 border-red-200 dark:border-red-900 shadow-2xl relative reveal">
                        
                        <div class="w-20 h-20 bg-red-100 dark:bg-red-950 rounded-[1.5rem] flex items-center justify-center text-red-600 mx-auto mb-6">
                            <i data-lucide="alert-triangle" class="w-10 h-10"></i>
                        </div>

                        <h3 class="text-3xl font-black tracking-tighter text-center text-emerald-950 dark:text-white mb-2" data-t-key="Permanent Deletion">{{ __('Permanent Deletion') }}</h3>
                        <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold text-center mb-8 leading-tight" data-t-key="This action cannot be undone. All your farm data, history, and profile will be permanently wiped.">{{ __('This action cannot be undone. All your farm data, history, and profile will be permanently wiped.') }}</p>
                        
                        <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-6">
                            @csrf
                            @method('DELETE')
                            
                            @if($user->password)
                                <div>
                                    <label class="block text-xs font-black text-red-900 dark:text-red-400 uppercase tracking-widest mb-2" data-t-key="Confirm with Password">{{ __('Confirm with Password') }}</label>
                                    <input type="password" name="password" required class="w-full px-6 py-4 bg-red-50 dark:bg-[#1a0a0a] border-2 border-red-100 dark:border-red-900/50 rounded-2xl outline-none focus:border-red-500 font-bold text-emerald-950 dark:text-white" placeholder="••••••••">
                                </div>
                            @endif

                            <div class="flex flex-col gap-3">
                                <button type="submit" class="w-full py-5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black shadow-xl shadow-red-600/30 transition-all text-lg" data-t-key="Wipe Account Data">
                                    {{ __('Wipe Account Data') }}
                                </button>
                                <button type="button" @click="open = false" class="w-full py-4 bg-emerald-50 dark:bg-[#0a1e15] text-emerald-800 dark:text-emerald-400 rounded-2xl font-bold transition-all" data-t-key="Cancel">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Dynamic Terminal -->
        <div class="lg:col-span-8 space-y-8">
            <div x-data="{ tab: 'account' }" class="space-y-6">
                <!-- Smart Tab Navigation -->
                <div class="flex space-x-2 p-2 bg-white dark:bg-[#081811] rounded-3xl border-4 border-emerald-100 dark:border-emerald-900 shadow-xl overflow-x-auto scrollpane">
                    <button @click="tab = 'account'" :class="tab === 'account' ? 'bg-emerald-700 text-white shadow-lg' : 'text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40'" class="flex-1 px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap" data-t-key="Account">{{ __('Account') }}</button>
                    
                    @if($user->role === 'farmer')
                        <button @click="tab = 'farms'" :class="tab === 'farms' ? 'bg-emerald-700 text-white shadow-lg' : 'text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40'" class="flex-1 px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap" data-t-key="My Lands">{{ __('My Lands') }}</button>
                    @endif

                    @if($user->role === 'farmer' || $user->role === 'seller')
                        <button @click="tab = 'shop'" :class="tab === 'shop' ? 'bg-emerald-700 text-white shadow-lg' : 'text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40'" class="flex-1 px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap" data-t-key="My Shop">{{ __('My Shop') }}</button>
                    @endif

                    <button @click="tab = 'orders'" :class="tab === 'orders' ? 'bg-emerald-700 text-white shadow-lg' : 'text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40'" class="flex-1 px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest transition-all whitespace-nowrap" data-t-key="Orders">{{ __('Orders') }}</button>
                </div>

                <!-- Account Tab -->
                <div x-show="tab === 'account'" class="bg-white dark:bg-[#081811] p-8 sm:p-12 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal">
                    <h3 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white mb-8" data-t-key="Edit Account">{{ __('Edit Account') }}</h3>
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Full Name">{{ __('Full Name') }}</label>
                                <input type="text" name="full_name" value="{{ $user->full_name }}" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Your Role">{{ __('Your Role') }}</label>
                                <select name="role" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white appearance-none outline-none focus:border-emerald-500">
                                    <option value="farmer" {{ $user->role === 'farmer' ? 'selected' : '' }}>{{ __('Farmer') }}</option>
                                    <option value="seller" {{ $user->role === 'seller' ? 'selected' : '' }}>{{ __('Seller') }}</option>
                                    <option value="buyer" {{ $user->role === 'buyer' ? 'selected' : '' }}>{{ __('Buyer') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Phone Number">{{ __('Phone Number') }}</label>
                                <input type="text" name="phone_number" value="{{ $user->phone_number }}" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Interface Language">{{ __('Interface Language') }}</label>
                                <select name="preferred_language" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white appearance-none outline-none">
                                    <option value="en" {{ $user->preferred_language === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="si" {{ $user->preferred_language === 'si' ? 'selected' : '' }}>Sinhala</option>
                                    <option value="ta" {{ $user->preferred_language === 'ta' ? 'selected' : '' }}>Tamil</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Bio">{{ __('Bio') }}</label>
                            <textarea name="bio" rows="3" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white focus:border-emerald-500 outline-none">{{ $user->bio }}</textarea>
                        </div>
                        <button type="submit" class="px-10 py-4 bg-emerald-700 hover:bg-emerald-600 text-white rounded-2xl font-black shadow-lg transition-all" data-t-key="Save Changes">{{ __('Save Changes') }}</button>
                    </form>
                </div>

                <!-- My Lands Tab -->
                @if($user->role === 'farmer')
                <div x-show="tab === 'farms'" x-data="farmManager()" class="space-y-6">
                    <div class="flex justify-between items-center px-4">
                        <h3 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white" data-t-key="Registered Lands">{{ __('Registered Lands') }}</h3>
                        <button @click="openModal()" class="p-4 bg-emerald-700 text-white rounded-2xl shadow-lg hover:scale-105 transition-all">
                            <i data-lucide="plus" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        @forelse($user->farms as $farm)
                            <div class="bg-white dark:bg-[#081811] p-8 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl reveal flex flex-col space-y-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-6">
                                        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl flex items-center justify-center text-emerald-700 dark:text-emerald-400">
                                            <i data-lucide="map" class="w-8 h-8"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-2xl font-black text-emerald-950 dark:text-white tracking-tight">{{ $farm->farm_name }}</h4>
                                            <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold uppercase tracking-widest text-[10px]">{{ $farm->district }} • <span data-t-key="Soil: ">{{ __('Soil: ') }}</span>{{ $farm->soil_type ?? __('Detecting...') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button @click="openModal({{ json_encode($farm) }})" class="p-4 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-2xl hover:bg-emerald-100 transition-all">
                                            <i data-lucide="edit-3" class="w-6 h-6"></i>
                                        </button>
                                        <button @click="deleteFarm({{ $farm->id }})" class="p-4 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-2xl hover:bg-red-100 transition-all">
                                            <i data-lucide="trash-2" class="w-6 h-6"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                @if($farm->cropSeasons->count() > 0)
                                    <div class="border-t-2 border-emerald-50 dark:border-emerald-900/50 pt-6 space-y-4">
                                        <h5 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-500" data-t-key="Active Cultivations">{{ __('Active Cultivations') }}</h5>
                                        <div class="grid grid-cols-1 gap-4">
                                            @foreach($farm->cropSeasons as $season)
                                                <div class="p-5 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-3xl border-2 border-emerald-100/50 dark:border-emerald-800/50" x-data="{ expanded: false }">
                                                    <div class="flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                                                        <div class="flex items-center space-x-4">
                                                            <div class="w-10 h-10 bg-white dark:bg-[#0a1e15] rounded-xl flex items-center justify-center shadow-sm">
                                                                <i data-lucide="sprout" class="w-5 h-5 text-emerald-600"></i>
                                                            </div>
                                                            <div>
                                                                <p class="font-black text-emerald-950 dark:text-white">{{ $season->crop_name }} <span class="text-emerald-500/50 mx-1">-</span> <span class="text-sm font-bold text-emerald-800/70">{{ $season->crop_variety }}</span></p>
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600/60 dark:text-emerald-400/60 mt-1">
                                                                    {{ \Carbon\Carbon::parse($season->planting_date)->format('M d') }} ➔ {{ \Carbon\Carbon::parse($season->expected_harvest_date)->format('M d, Y') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-400 transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                                                    </div>
                                                    
                                                    <div x-show="expanded" x-collapse class="mt-6 pt-6 border-t-2 border-white dark:border-[#0a1e15]">
                                                        <div class="space-y-4">
                                                            @foreach($season->tasks as $task)
                                                                <div class="flex items-start space-x-3 group">
                                                                    <button class="shrink-0 mt-0.5 focus:outline-none transition-transform active:scale-90" onclick="toggleTask({{ $task->id }}, this)">
                                                                        <i data-lucide="{{ $task->completed ? 'check-circle-2' : 'circle' }}" class="w-5 h-5 {{ $task->completed ? 'text-emerald-500' : 'text-emerald-200 dark:text-emerald-800 group-hover:text-emerald-400' }} transition-colors"></i>
                                                                    </button>
                                                                    <div>
                                                                        <p class="text-sm font-bold {{ $task->completed ? 'text-emerald-800/40 line-through' : 'text-emerald-950 dark:text-emerald-100' }}">{{ $task->task_name }}</p>
                                                                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600/50 mt-1">{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-emerald-100/30 dark:bg-emerald-900/10 border-4 border-dashed border-emerald-200 dark:border-emerald-800 p-12 rounded-[2.5rem] text-center">
                                <i data-lucide="sprout" class="w-16 h-16 mx-auto mb-4 text-emerald-300"></i>
                                <p class="text-emerald-800/60 dark:text-emerald-400/60 font-black" data-t-key="No farms registered yet.">{{ __('No farms registered yet.') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add/Edit Farm Modal -->
                    <div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6" x-cloak>
                        <div @click="showModal = false" class="absolute inset-0 bg-[#06120c]/80 backdrop-blur-md"></div>
                        <div class="bg-white dark:bg-[#081811] w-full max-w-4xl rounded-[3rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
                            <div class="p-8 border-b border-emerald-50 dark:border-emerald-900/50 flex justify-between items-center bg-emerald-50/30 dark:bg-emerald-950/20">
                                <div>
                                    <h3 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white">
                                        <span x-show="!isEditing" data-t-key="Register New Land">{{ __('Register New Land') }}</span>
                                        <span x-show="isEditing" data-t-key="Update Land Intelligence">{{ __('Update Land Intelligence') }}</span>
                                    </h3>
                                    <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold text-sm" data-t-key="Drop a pin on your field to initialize local intelligence">{{ __('Drop a pin on your field to initialize local intelligence') }}</p>
                                </div>
                                <button @click="showModal = false" class="p-3 bg-white dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl text-emerald-900 dark:text-emerald-400 hover:scale-110 transition-all">
                                    <i data-lucide="x" class="w-6 h-6"></i>
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-8 space-y-8 scrollpane">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Land Name">{{ __('Land Name') }}</label>
                                            <input type="text" x-model="newFarm.farm_name" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white outline-none focus:border-emerald-500" placeholder="e.g. North Paddy Field">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Total Size">{{ __('Total Size') }}</label>
                                                <div class="flex">
                                                    <input type="number" step="0.1" x-model="newFarm.size_value" class="w-2/3 px-4 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-r-0 border-emerald-100 dark:border-emerald-900 rounded-l-2xl font-bold text-emerald-950 dark:text-white outline-none focus:border-emerald-500" placeholder="5">
                                                    <select x-model="newFarm.size_unit" class="w-1/3 px-2 py-4 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-r-2xl font-black text-[10px] uppercase tracking-tighter text-emerald-700 dark:text-emerald-400 appearance-none outline-none focus:border-emerald-500">
                                                        <option value="Acres">{{ __('Acres') }}</option>
                                                        <option value="Perches">{{ __('Perches') }}</option>
                                                        <option value="Hectares">{{ __('Hectares') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Irrigation Source">{{ __('Irrigation Source') }}</label>
                                                <select x-model="newFarm.irrigation_source" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white appearance-none outline-none">
                                                    <option value="rainfed">Rainfed</option>
                                                    <option value="tank">Tank / Wewa</option>
                                                    <option value="well">Groundwater / Well</option>
                                                    <option value="canal">Canal System</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="p-6 bg-emerald-500/5 rounded-3xl border-2 border-emerald-500/20 space-y-4">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest" data-t-key="Detected Intelligence">{{ __('Detected Intelligence') }}</span>
                                                <i data-lucide="brain-circuit" class="w-5 h-5 text-emerald-500"></i>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-bold text-emerald-900/60 dark:text-emerald-100/60" data-t-key="District:">{{ __('District: ') }}</span>
                                                <span class="font-black text-emerald-950 dark:text-white" x-text="newFarm.district || 'Select on map...'"></span>
                                            </div>
                                            <div>
                                                <div class="flex justify-between items-center mb-2">
                                                    <label class="block text-[10px] font-black text-emerald-900/40 dark:text-emerald-400/40 uppercase tracking-widest" data-t-key="Soil Type (Auto-detected)">{{ __('Soil Type (Auto-detected)') }}</label>
                                                    <div class="flex space-x-3">
                                                        <button type="button" @click="showWizard = true; wizard.step = 1" class="text-[10px] font-black text-amber-600 hover:text-amber-500 uppercase tracking-widest flex items-center space-x-1 transition-colors">
                                                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                            <span data-t-key="Analyze with Wizard">{{ __('Analyze with Wizard') }}</span>
                                                        </button>
                                                        <button type="button" @click="$refs.soilReportFile.click()" class="text-[10px] font-black text-emerald-600 hover:text-emerald-500 uppercase tracking-widest flex items-center space-x-1 transition-colors">
                                                            <i data-lucide="file-up" class="w-3 h-3"></i>
                                                            <span data-t-key="Upload Soil Report">{{ __('Upload Soil Report') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="file" x-ref="soilReportFile" @change="uploadSoilReport($event)" class="hidden" accept="image/*">
                                                
                                                <div x-show="uploadingReport" class="mb-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center space-x-3">
                                                    <div class="w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                                                    <span class="text-[10px] font-black uppercase text-emerald-600 tracking-widest">{{ __('Analyzing Report with AI...') }}</span>
                                                </div>

                                                <select x-model="newFarm.soil_type" class="w-full px-4 py-3 bg-white dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-xl font-bold text-emerald-950 dark:text-white appearance-none outline-none focus:border-emerald-500">
                                                    <option value="">{{ __('Detecting...') }}</option>
                                                    <option value="Reddish Brown Earths">Reddish Brown Earths (RBE)</option>
                                                    <option value="Low Humic Gley Soils">Low Humic Gley Soils (LHG)</option>
                                                    <option value="Non-Calcic Brown Soils">Non-Calcic Brown Soils (NCB)</option>
                                                    <option value="Red-Yellow Podzolic Soils">Red-Yellow Podzolic Soils (RYP)</option>
                                                    <option value="Red-Yellow Latosols">Red-Yellow Latosols (RYL)</option>
                                                    <option value="Calcic Latosols">Calcic Latosols (CL)</option>
                                                    <option value="Alluvial Soils">Alluvial Soils</option>
                                                    <option value="Solodized Solonetz">Solodized Solonetz</option>
                                                    <option value="Regosols">Regosols (Sandy)</option>
                                                    <option value="Grumusols">Grumusols (Clay)</option>
                                                    <option value="Immature Brown Loams">Immature Brown Loams (IBL)</option>
                                                    <option value="Bog and Half-Bog Soils">Bog and Half-Bog Soils</option>
                                                    <option value="Reddish Brown Latosolic Soils">Reddish Brown Latosolic Soils (RBL)</option>
                                                    <option value="Rendzina Soils">Rendzina Soils</option>
                                                    <option value="Coastal Sands">Coastal Sands</option>
                                                </select>
                                                <p class="text-[10px] text-emerald-600/60 mt-2 italic">
                                                    <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                                                    <span data-t-key="The system predicts your soil, but you can override it if your land is different.">{{ __('The system predicts your soil, but you can override it if your land is different.') }}</span>
                                                </p>
                                            </div>

                                            <!-- Nested Soil Wizard UI -->
                                            <div x-show="showWizard" class="absolute inset-0 z-50 bg-white dark:bg-[#081811] flex flex-col p-8 reveal" x-cloak>
                                                <div class="flex justify-between items-center mb-8">
                                                    <h4 class="text-2xl font-black tracking-tighter text-emerald-950 dark:text-white" data-t-key="Soil Analysis Wizard">{{ __('Soil Analysis Wizard') }}</h4>
                                                    <button @click="showWizard = false" class="p-2 text-emerald-900/40 dark:text-emerald-400/40">
                                                        <i data-lucide="x" class="w-6 h-6"></i>
                                                    </button>
                                                </div>

                                                <div class="flex-1 space-y-8">
                                                    <!-- Step 1: Feel -->
                                                    <div x-show="wizard.step === 1" class="space-y-6 reveal">
                                                        <p class="font-bold text-emerald-800 dark:text-emerald-200" data-t-key="Take a handful of dry soil. Rub it between your fingers. How does it feel?">{{ __('Take a handful of dry soil. Rub it between your fingers. How does it feel?') }}</p>
                                                        <div class="grid grid-cols-1 gap-3">
                                                            <button @click="wizard.answers.feel = 'gritty'; wizard.step = 2" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Gritty like sugar">{{ __('Gritty like sugar') }}</span>
                                                                <span class="text-xs text-emerald-600/60" data-t-key="Indicates high sand content">{{ __('Indicates high sand content') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.feel = 'smooth'; wizard.step = 2" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Smooth like flour">{{ __('Smooth like flour') }}</span>
                                                                <span class="text-xs text-emerald-600/60" data-t-key="Indicates high silt content">{{ __('Indicates high silt content') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.feel = 'sticky'; wizard.step = 2" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Sticky or Hard">{{ __('Sticky or Hard') }}</span>
                                                                <span class="text-xs text-emerald-600/60" data-t-key="Indicates high clay content">{{ __('Indicates high clay content') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Step 2: Water -->
                                                    <div x-show="wizard.step === 2" class="space-y-6 reveal">
                                                        <p class="font-bold text-emerald-800 dark:text-emerald-200" data-t-key="When it rains or you irrigate, what happens to the water?">{{ __('When it rains or you irrigate, what happens to the water?') }}</p>
                                                        <div class="grid grid-cols-1 gap-3">
                                                            <button @click="wizard.answers.water = 'fast'; wizard.step = 3" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Disappears very quickly">{{ __('Disappears very quickly') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.water = 'medium'; wizard.step = 3" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Drains normally">{{ __('Drains normally') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.water = 'slow'; wizard.step = 3" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Forms puddles or stays wet for a long time">{{ __('Forms puddles or stays wet for a long time') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Step 3: Ribbon Test -->
                                                    <div x-show="wizard.step === 3" class="space-y-6 reveal">
                                                        <p class="font-bold text-emerald-800 dark:text-emerald-200" data-t-key="Squeeze a wet ball of soil. Can you roll it into a string or ribbon?">{{ __('Squeeze a wet ball of soil. Can you roll it into a string or ribbon?') }}</p>
                                                        <div class="grid grid-cols-1 gap-3">
                                                            <button @click="wizard.answers.sticky = 'low'; calculateSoil()" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="No, it just crumbles">{{ __('No, it just crumbles') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.sticky = 'medium'; calculateSoil()" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Yes, but it breaks easily">{{ __('Yes, but it breaks easily') }}</span>
                                                            </button>
                                                            <button @click="wizard.answers.sticky = 'high'; calculateSoil()" class="p-5 text-left border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl hover:border-emerald-500 transition-all font-bold">
                                                                <span class="block text-emerald-950 dark:text-white" data-t-key="Yes, it stays long and sticky">{{ __('Yes, it stays long and sticky') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pt-8 mt-auto border-t border-emerald-50 dark:border-emerald-900/50 flex items-center justify-between">
                                                    <div class="flex space-x-2">
                                                        <template x-for="i in 3">
                                                            <div class="w-8 h-1.5 rounded-full transition-all" :class="wizard.step >= i ? 'bg-emerald-500' : 'bg-emerald-100 dark:bg-emerald-900'"></div>
                                                        </template>
                                                    </div>
                                                    <button @click="wizard.step--" x-show="wizard.step > 1" class="text-xs font-black uppercase tracking-widest text-emerald-600" data-t-key="Back">{{ __('Back') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="h-[400px] rounded-[2rem] border-4 border-emerald-100 dark:border-emerald-900 overflow-hidden relative shadow-inner">
                                        <div class="absolute top-4 left-4 right-4 z-[1000]">
                                            <div class="relative group">
                                                <input type="text" x-model="searchQuery" @input.debounce.500ms="searchPlaces()"
                                                    class="w-full pl-12 pr-12 py-4 bg-white/95 dark:bg-[#081811]/95 border-2 border-emerald-100 dark:border-emerald-800 rounded-2xl shadow-2xl focus:border-emerald-500 outline-none font-bold text-emerald-950 dark:text-white backdrop-blur-md transition-all" 
                                                    placeholder="{{ __('Search for village or town in Sri Lanka...') }}">
                                                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                                                <div x-show="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-800 rounded-2xl shadow-2xl overflow-hidden reveal" @click.away="searchResults = []">
                                                    <template x-for="result in searchResults" :key="result.lat + result.lon">
                                                        <button @click="selectResult(result)" class="w-full px-6 py-4 text-left hover:bg-emerald-50 dark:hover:bg-emerald-900/40 flex items-center space-x-4 border-b border-emerald-50 last:border-0 transition-colors">
                                                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                                            <span class="text-sm font-bold text-emerald-950 dark:text-emerald-100 truncate" x-text="result.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <button @click="locateUser()" class="absolute bottom-4 right-4 z-[1000] p-4 bg-white dark:bg-[#081811] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl shadow-xl text-emerald-700 dark:text-emerald-400 hover:scale-110 active:scale-95 transition-all">
                                            <i data-lucide="crosshair" class="w-6 h-6"></i>
                                        </button>
                                        <div id="farm-map" class="w-full h-full z-10"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8 border-t border-emerald-50 dark:border-emerald-900/50 bg-emerald-50/30 dark:bg-emerald-950/20">
                                <button @click="saveFarm()" :disabled="!newFarm.latitude || isSaving" class="w-full py-5 bg-emerald-700 hover:bg-emerald-600 disabled:opacity-50 text-white rounded-2xl font-black shadow-xl transition-all text-xl flex items-center justify-center space-x-3">
                                    <template x-if="!isSaving">
                                        <div class="flex items-center space-x-3">
                                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                                            <span x-show="!isEditing" data-t-key="Finalize Registration">{{ __('Finalize Registration') }}</span>
                                            <span x-show="isEditing" data-t-key="Save Changes">{{ __('Save Changes') }}</span>
                                        </div>
                                    </template>
                                    <template x-if="isSaving">
                                        <i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- My Shop Tab -->
                @if($user->role === 'farmer' || $user->role === 'seller')
                <div x-show="tab === 'shop'" class="space-y-6">
                    <div class="flex justify-between items-center px-4">
                        <h3 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white uppercase" data-t-key="Marketplace Shop">{{ __('Marketplace Shop') }}</h3>
                        <a href="{{ route('seller.listings.create') }}" class="p-4 bg-amber-500 text-amber-950 rounded-2xl shadow-lg hover:scale-105 transition-all">
                            <i data-lucide="plus-circle" class="w-6 h-6"></i>
                        </a>
                    </div>

                    <div class="bg-white dark:bg-[#081811] p-12 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl text-center reveal">
                        <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-3xl flex items-center justify-center text-emerald-600 mx-auto mb-8">
                            <i data-lucide="store" class="w-12 h-12"></i>
                        </div>
                        <h4 class="text-3xl font-black text-emerald-950 dark:text-white uppercase tracking-tight" data-t-key="Manage Your Harvests">{{ __('Manage Your Harvests') }}</h4>
                        <p class="mt-4 text-emerald-800/60 dark:text-emerald-400/60 font-bold max-w-md mx-auto" data-t-key="Use the dedicated Seller Portal to manage your classified ads and track buyer inquiries.">{{ __('Use the dedicated Seller Portal to manage your classified ads and track buyer inquiries.') }}</p>

                        <a href="{{ route('seller.dashboard') }}" class="mt-10 inline-flex items-center px-10 py-5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest shadow-2xl shadow-emerald-700/30 transition-all group">
                            <span data-t-key="Enter Seller Portal">{{ __('Enter Seller Portal') }}</span>
                            <i data-lucide="external-link" class="w-5 h-5 ml-3 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
                @endif
                <!-- Orders Tab -->
                <div x-show="tab === 'orders'" class="space-y-6">
                    <h3 class="text-3xl font-black tracking-tighter text-emerald-950 dark:text-white px-4" data-t-key="My Purchases">{{ __('My Purchases') }}</h3>
                    <div class="bg-white dark:bg-[#081811] p-12 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-xl text-center reveal">
                        <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/20 rounded-3xl flex items-center justify-center text-emerald-600 mx-auto mb-6">
                            <i data-lucide="package" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-2xl font-black text-emerald-950 dark:text-white" data-t-key="No Orders Yet">{{ __('No Orders Yet') }}</h4>
                        <p class="mt-2 text-emerald-800/60 dark:text-emerald-400/60 font-bold" data-t-key="Purchase seeds or tools to see them here.">{{ __('Purchase seeds or tools to see them here.') }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
