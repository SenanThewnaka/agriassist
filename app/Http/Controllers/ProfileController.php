<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\FarmerProfile;
use App\Models\MerchantProfile;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load(['farmerProfile', 'merchantProfile', 'farms.cropSeasons.tasks']);
        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'regex:/^(\+94|0)[0-9]{9}$/'],
            'district' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'preferred_language' => ['sometimes', 'required', 'in:en,si,ta'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'role' => ['sometimes', 'required', 'in:farmer,seller,buyer'],
        ];

        $validated = $request->validate($rules, [
            'phone_number.regex' => 'The phone number must be a valid Sri Lankan number (e.g. 0712345678 or +94712345678).'
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);

        // Initialize role-specific profile if it's new
        if ($user->role === 'farmer' && !$user->farmerProfile) {
            FarmerProfile::create(['user_id' => $user->id]);
        } elseif ($user->role === 'seller' && !$user->merchantProfile) {
            MerchantProfile::create(['user_id' => $user->id, 'store_name' => $user->full_name . "'s Store"]);
        }

        if ($user->role === 'farmer' && $user->farmerProfile) {
            $user->farmerProfile->update($request->only(['farm_size', 'farming_type', 'irrigation_type', 'experience_years', 'main_crops']));
        } elseif ($user->role === 'seller' && $user->merchantProfile) {
            $user->merchantProfile->update($request->only(['store_name', 'description', 'store_location', 'phone', 'website', 'delivery_available']));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile synchronization complete.',
                'user' => $user->load(['farmerProfile', 'merchantProfile'])
            ]);
        }

        return back()->with('status', 'profile-updated');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        // If user has a password, verify it. Google-only users might not have one.
        if ($user->password) {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account permanently deleted.',
                'redirect' => route('home')
            ]);
        }

        return redirect('/')->with('status', 'Account permanently deleted.');
    }
}
