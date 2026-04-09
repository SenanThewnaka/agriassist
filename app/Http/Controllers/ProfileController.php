<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'full_name' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'phone_number' => ['nullable', 'string', 'regex:/^(\+94|0)[0-9]{9}$/'],
            'district' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'preferred_language' => ['sometimes', 'required', 'in:en,si,ta'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        // If user has no role yet (e.g. fresh social login), require it
        if (!$user->role) {
            $rules['role'] = ['required', 'in:farmer,seller,buyer'];
        }

        $validated = $request->validate($rules, [
            'full_name.regex' => 'The full name may only contain letters and spaces.',
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
}
