<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FarmerProfile;
use App\Models\MerchantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first()
                ], 422);
            }
            throw $e;
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            \Illuminate\Support\Facades\Log::info('Login successful for user: ' . $request->email);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful. Entering terminal...',
                    'redirect' => route('home')
                ]);
            }
            
            return redirect()->intended(route('home'));
        }

        \Illuminate\Support\Facades\Log::warning('Login failed for user: ' . $request->email);
        
        if ($request->ajax() || $request->wantsJson()) {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                $message = 'No account found with this email. Please join first.';
            } elseif ($user->google_id && !$user->password) {
                $message = 'This account was created via Google. Please use "Sign in with Google".';
            } else {
                $message = 'Incorrect password. Please try again.';
            }
                
            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
                'role' => ['required', 'in:farmer,buyer,seller'],
                'preferred_language' => ['required', 'in:en,si,ta'],
            ], [
                'full_name.regex' => 'The full name may only contain letters and spaces.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Registration validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            
            $userExists = User::where('email', $request->email)->exists();
            $message = $userExists 
                ? 'An account with this email already exists. Please login instead.' 
                : collect($e->errors())->flatten()->first();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            throw $e;
        }

        $user = User::create([
            'name' => explode(' ', $request->full_name)[0],
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'preferred_language' => $request->preferred_language,
        ]);

        // Create role-specific profile
        if ($user->role === 'farmer') {
            FarmerProfile::create(['user_id' => $user->id]);
        } elseif ($user->role === 'seller') {
            MerchantProfile::create(['user_id' => $user->id, 'store_name' => $user->full_name . "'s Store"]);
        }

        Auth::login($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account initialized. Welcome to the ecosystem!',
                'redirect' => route('home')
            ]);
        }

        return redirect(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Redirect to Google.
    public function redirectToGoogle()
    {
        \Illuminate\Support\Facades\Log::info('Redirecting to Google OAuth...');
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Could not initialize Google login.');
        }
    }

    // Handle Google callback.
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Search by google_id first, then by email
            $user = User::where('google_id', $googleUser->id)->first();
            
            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();
            }

            if ($user) {
                // Update Google-related fields if they changed or are missing
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                    // Only update full_name if it was previously empty or just 'Farmer'
                    'full_name' => $user->full_name ?: $googleUser->name,
                ]);
                
                Auth::login($user, true); // Use remember=true for social login

                if (!$user->role) {
                    return redirect()->route('profile.show')->with('warning', 'Please complete your profile to continue.');
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'full_name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                    'role' => null,
                    'preferred_language' => app()->getLocale(),
                ]);

                Auth::login($user, true);

                return redirect()->route('profile.show')->with('info', 'Welcome! Please select your role to finalize your account.');
            }

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Callback Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }
    }
}
