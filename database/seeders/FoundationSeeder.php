<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\FarmerProfile;
use App\Models\MerchantProfile;
use App\Models\Farm;
use App\Models\CropSeason;
use App\Models\Listing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class FoundationSeeder extends Seeder {
    public function run(): void {
        // Create Farmer
        $farmer = User::updateOrCreate(
            ['email' => 'farmer@agriassist.com'],
            [
                'name' => 'Farmer User',
                'full_name' => 'Saman Perera',
                'password' => Hash::make('password'),
                'role' => 'farmer',
                'preferred_language' => 'si',
                'district' => 'Anuradhapura'
            ]
        );
        FarmerProfile::updateOrCreate(
            ['user_id' => $farmer->id],
            [
                'farm_size' => '5 Acres',
                'farming_type' => 'conventional',
                'experience_years' => 15,
                'main_crops' => 'Paddy, Maize'
            ]
        );
        $farm = Farm::updateOrCreate(
            ['farmer_id' => $farmer->id, 'farm_name' => 'Green Fields'],
            [
                'latitude' => 8.3114,
                'longitude' => 80.4037,
                'soil_type' => 'Reddish Brown Earth',
                'farm_size' => '5 Acres',
                'district' => 'Anuradhapura'
            ]
        );
        CropSeason::updateOrCreate(
            ['farm_id' => $farm->id, 'crop_name' => 'Paddy'],
            [
                'crop_variety' => 'BG352',
                'planting_date' => now()->subMonths(2),
                'crop_stage' => 'Flowering'
            ]
        );

        // Create Seller
        $seller = User::updateOrCreate(
            ['email' => 'seller@agriassist.com'],
            [
                'name' => 'Seller User',
                'full_name' => 'Agro Store',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'preferred_language' => 'en',
            ]
        );
        MerchantProfile::updateOrCreate(
            ['user_id' => $seller->id],
            [
                'store_name' => 'North Central Agro',
                'description' => 'Supplier of seeds and fertilizers',
                'store_location' => 'Anuradhapura Town',
                'delivery_available' => true
            ]
        );
        Listing::updateOrCreate(
            ['seller_id' => $seller->id, 'title' => 'Urea Fertilizer'],
            [
                'category' => 'fertilizer',
                'description' => 'High quality Urea for paddy',
                'price' => 5000.00,
                'quantity' => '50kg Bags',
                'location' => 'Anuradhapura'
            ]
        );

        // Create Buyer
        User::updateOrCreate(
            ['email' => 'buyer@agriassist.com'],
            [
                'name' => 'Buyer User',
                'full_name' => 'Harvest Buyers Ltd',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'preferred_language' => 'en',
            ]
        );
    }
}