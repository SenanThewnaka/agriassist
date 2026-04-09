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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful. Entering terminal...',
                    'redirect' => route('home')
                ]);
            }
            
            return redirect()->intended(route('home'));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
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
        $request->validate([
            'full_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:farmer,buyer,seller'],
            'preferred_language' => ['required', 'in:en,si,ta'],
        ], [
            'full_name.regex' => 'The full name may only contain letters and spaces.'
        ]);

        $user = User::create([
            'name' => explode(' ', $request->full_name)[0],
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

    /**
     * Redirect to Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // Link Google ID if not already linked
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar_url' => $googleUser->avatar
                    ]);
                }
                
                Auth::login($user);

                // If user somehow has no role (social link), redirect to profile to fix it
                if (!$user->role) {
                    return redirect()->route('profile.show')->with('warning', 'Please complete your profile to continue.');
                }
            } else {
                // Create new user WITHOUT a default role
                // This forces them to choose a role during onboarding
                $user = User::create([
                    'name' => $googleUser->name,
                    'full_name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                    'role' => null, // No default role
                    'preferred_language' => app()->getLocale(),
                ]);

                Auth::login($user);

                return redirect()->route('profile.show')->with('info', 'Welcome! Please select your role to finalize your account.');
            }

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }
    }
}
