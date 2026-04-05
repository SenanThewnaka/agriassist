@extends('layouts.app')

@push('scripts')
@vite(['resources/js/pages/auth.js'])
@endpush

@section('content')
<div class="max-w-md mx-auto px-4 py-20">
    <div class="bg-white dark:bg-[#081811] p-8 sm:p-12 rounded-[2.5rem] border-4 border-emerald-100 dark:border-emerald-900 shadow-2xl reveal">
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-emerald-700 rounded-2xl flex items-center justify-center text-amber-300 shadow-lg mx-auto mb-6">
                <i data-lucide="lock" class="w-8 h-8"></i>
            </div>
            <h2 class="text-4xl font-black tracking-tighter text-emerald-950 dark:text-white" data-t-key="Sign In">{{ __('Sign In') }}</h2>
            <p class="text-emerald-700/80 dark:text-emerald-400/80 font-bold mt-2" data-t-key="Access your farm intelligence">{{ __('Access your farm intelligence') }}</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Email Address">{{ __('Email Address') }}</label>
                <input type="email" name="email" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white" placeholder="farmer@example.com" value="{{ old('email') }}">
                @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-widest mb-2" data-t-key="Password">{{ __('Password') }}</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required class="w-full px-6 py-4 bg-emerald-50 dark:bg-[#0a1e15] border-2 border-emerald-100 dark:border-emerald-900 rounded-2xl focus:border-emerald-500 dark:focus:border-emerald-500 outline-none transition-all font-bold text-emerald-950 dark:text-white pr-14" placeholder="••••••••">
                    <button type="button" @click="togglePassword('password')" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-emerald-600/60 dark:text-emerald-400/60 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors">
                        <i data-lucide="eye" class="w-5 h-5" id="password-eye"></i>
                        <i data-lucide="eye-off" class="w-5 h-5 hidden" id="password-eye-off"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="remember" class="w-5 h-5 rounded border-2 border-emerald-200 dark:border-emerald-800 text-emerald-600 focus:ring-emerald-500 transition-all bg-white dark:bg-[#0a1e15]">
                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-400 group-hover:text-emerald-600 transition-colors" data-t-key="Remember me">{{ __('Remember me') }}</span>
                </label>
            </div>

            <button type="submit" class="w-full py-5 bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-2xl font-black shadow-xl shadow-emerald-700/40 hover:-translate-y-1 active:scale-95 transition-all duration-300 text-xl tracking-tight border-b-4 border-emerald-900 dark:border-emerald-800" data-t-key="Enter Terminal">
                {{ __('Enter Terminal') }}
            </button>
        </form>

        <div class="mt-10 pt-10 border-t-2 border-emerald-50 dark:border-emerald-900/50 text-center">
            <p class="text-emerald-800/60 dark:text-emerald-400/60 font-bold" data-t-key="New to AgriAssist?">{{ __('New to AgriAssist?') }}</p>
            <a href="{{ route('register') }}" class="inline-block mt-2 text-emerald-700 dark:text-emerald-400 font-black hover:text-emerald-500 transition-colors text-lg" data-t-key="Create Account">{{ __('Create Account') }}</a>
        </div>
    </div>
</div>
@endsection