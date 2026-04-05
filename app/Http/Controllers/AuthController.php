<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FarmerProfile;
use App\Models\MerchantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

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
}
