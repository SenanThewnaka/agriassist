@extends('layouts.app')

@push('scripts')
@vite(['resources/js/pages/auth.js'])
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-20">
    <div class="bg-white dark:bg-[#081811] p-8 sm:p-12 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl reveal">
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-emerald-700 rounded-2xl flex items-center justify-center text-amber-300 shadow-lg mx-auto mb-6">
                <i data-lucide="user-plus" class="w-8 h-8"></i>
            </div>
            <h2 class="text-4xl font-black tracking-tighter text-emerald-950 dark:text-white" data-t-key="Join Ecosystem">{{ __('Join Ecosystem') }}</h2>
            <p class="text-emerald-700/80 dark:text-emerald-400/80 font-bold mt-2" data-t-key="Start your digital agriculture journey">{{ __('Start your digital agriculture journey') }}</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Full Name">{{ __('Full Name') }}</label>
                    <input type="text" name="full_name" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white" placeholder="Saman Perera" value="{{ old('full_name') }}">
                    @error('full_name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Email Address">{{ __('Email Address') }}</label>
                    <input type="email" name="email" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white" placeholder="saman@example.com" value="{{ old('email') }}">
                    @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-data="{ password: '', password_confirmation: '' }">
                <div>
                    <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Password">{{ __('Password') }}</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" x-model="password" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white pr-14" placeholder="••••••••">
                        <button type="button" @click="togglePassword('password')" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-emerald-600/60 dark:text-emerald-400/60 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors">
                            <i data-lucide="eye" class="w-5 h-5" id="password-eye"></i>
                            <i data-lucide="eye-off" class="w-5 h-5 hidden" id="password-eye-off"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Confirm Password">{{ __('Confirm Password') }}</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" x-model="password_confirmation" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white pr-14" placeholder="••••••••">
                        <button type="button" @click="togglePassword('password_confirmation')" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-emerald-600/60 dark:text-emerald-400/60 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors">
                            <i data-lucide="eye" class="w-5 h-5" id="password_confirmation-eye"></i>
                            <i data-lucide="eye-off" class="w-5 h-5 hidden" id="password_confirmation-eye-off"></i>
                        </button>
                    </div>
                    <p x-show="password_confirmation && password !== password_confirmation" class="text-red-500 text-xs font-bold mt-1" x-cloak data-t-key="Passwords do not match">
                        {{ __('Passwords do not match') }}
                    </p>
                </div>
            </div>

            <div class="pt-4">
                <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-4 text-center" data-t-key="Select Your Role">{{ __('Select Your Role') }}</label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="role" value="farmer" checked class="peer sr-only">
                        <div class="p-4 text-center border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl bg-emerald-50/50 dark:bg-[#0a1e15] peer-checked:border-emerald-500 peer-checked:bg-emerald-100 dark:peer-checked:bg-emerald-900/50 transition-all">
                            <i data-lucide="tractor" class="w-8 h-8 mx-auto mb-2 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="block font-black text-xs uppercase tracking-tighter text-emerald-900 dark:text-emerald-300" data-t-key="Farmer">{{ __('Farmer') }}</span>
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="role" value="seller" class="peer sr-only">
                        <div class="p-4 text-center border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl bg-emerald-50/50 dark:bg-[#0a1e15] peer-checked:border-emerald-500 peer-checked:bg-emerald-100 dark:peer-checked:bg-emerald-900/50 transition-all">
                            <i data-lucide="store" class="w-8 h-8 mx-auto mb-2 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="block font-black text-xs uppercase tracking-tighter text-emerald-900 dark:text-emerald-300" data-t-key="Seller">{{ __('Seller') }}</span>
                        </div>
                    </label>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="role" value="buyer" class="peer sr-only">
                        <div class="p-4 text-center border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl bg-emerald-50/50 dark:bg-[#0a1e15] peer-checked:border-emerald-500 peer-checked:bg-emerald-100 dark:peer-checked:bg-emerald-900/50 transition-all">
                            <i data-lucide="shopping-cart" class="w-8 h-8 mx-auto mb-2 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="block font-black text-xs uppercase tracking-tighter text-emerald-900 dark:text-emerald-300" data-t-key="Buyer">{{ __('Buyer') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Preferred Language">{{ __('Preferred Language') }}</label>
                <select name="preferred_language" class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white appearance-none">
                    <option value="en" data-t-key="English">{{ __('English') }}</option>
                    <option value="si" data-t-key="Sinhala">{{ __('Sinhala') }}</option>
                    <option value="ta" data-t-key="Tamil">{{ __('Tamil') }}</option>
                </select>
            </div>

            <button type="submit" class="w-full py-5 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-2xl font-black shadow-xl shadow-emerald-700/40 hover:-translate-y-1 active:scale-95 transition-all duration-300 text-xl tracking-tight border-b-4 border-emerald-900 dark:border-emerald-800" data-t-key="Initialize Account">
                {{ __('Initialize Account') }}
            </button>
        </form>

        <div class="mt-6">
            <div class="relative flex items-center justify-center mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-emerald-100 dark:border-emerald-900"></div>
                </div>
                <span class="relative px-4 bg-white dark:bg-[#081811] text-xs font-black text-emerald-600/40 uppercase tracking-widest" data-t-key="Or continue with">{{ __('Or continue with') }}</span>
            </div>

            <a href="{{ route('auth.google') }}" class="w-full py-4 bg-white dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl font-bold text-emerald-950 dark:text-white hover:bg-emerald-50 dark:hover:bg-[#0d251d] transition-all flex items-center justify-center space-x-3 shadow-sm active:scale-[0.98]">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.18 1-.78 1.85-1.63 2.42v2.81h2.63c1.54-1.41 2.43-3.5 2.43-5.24z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-2.63-2.81c-.73.49-1.66.77-2.65.77-2.85 0-5.27-1.92-6.13-4.51H2.18v3.09C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.87 13.8c-.22-.66-.35-1.36-.35-2.08s.13-1.42.35-2.08V6.55H2.18C1.4 8.11 1 9.85 1 11.8s.4 3.69 1.18 5.25l3.69-3.25z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 6.55l3.69 3.09c.86-2.59 3.28-4.51 6.13-4.51z" fill="#EA4335"/>
                </svg>
                <span data-t-key="Sign up with Google">{{ __('Sign up with Google') }}</span>
            </a>
        </div>

        <div class="mt-10 pt-10 border-t-2 border-emerald-50 dark:border-emerald-900/50 text-center">
            <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold" data-t-key="Already a member?">{{ __('Already a member?') }}</p>
            <a href="{{ route('login') }}" class="inline-block mt-2 text-emerald-700 dark:text-emerald-400 font-black hover:text-emerald-500 transition-colors text-lg" data-t-key="Login to Terminal">{{ __('Login to Terminal') }}</a>
        </div>
    </div>
</div>
@endsection