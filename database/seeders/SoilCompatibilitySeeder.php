<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Database\Seeder;

class SoilCompatibilitySeeder extends Seeder
{
    /**
     * Run the database seeds to ensure every soil type has at least two matching crops.
     */
    public function run(): void
    {
        // Soil types from the soilNameMap in CropPlannerController
        $normalizedSoils = [
            'Reddish Brown Earth',
            'Alluvial',
            'Red Yellow Podzolic',
            'Red-Yellow Latosols',
            'Sandy',
            'Black Soil',
            'Lateritic',
            'Grumusols',
        ];

        // 1. Maize (Very adaptable)
        $maize = Crop::updateOrCreate(['name' => 'Maize'], [
            'name_si' => 'බඩඉරිඟු',
            'name_ta' => 'சோளம்',
            'category' => 'grain',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        // Maize variety for almost all soils
        $vMaize = CropVariety::updateOrCreate(['variety_name' => 'Pacific 999'], [
            'crop_id' => $maize->id,
            'variety_name_si' => 'පැසිෆික් 999',
            'variety_name_ta' => 'பசிபிக் 999',
            'growth_days' => 110,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earth', 'Alluvial', 'Red Yellow Podzolic', 'Sandy Loam', 'Red-Yellow Latosols', 'Sandy', 'Grumusols'],
            'yield_per_acre_kg' => 3500,
            'seed_per_acre_kg' => 8,
            'base_market_price_per_kg' => 120,
        ]);

        $maizeStages = [
            ['Land Preparation', 'භූමිය සැකසීම', 0, 'map', 'Plow the land and add organic matter.', 50, 25, 25],
            ['Sowing', 'බීජ වැපිරීම', 7, 'sprout', 'Sow seeds at 20cm spacing.', 0, 0, 0],
            ['Vegetative Growth', 'වැඩෙන අවධිය', 30, 'leaf', 'Apply top dressing and weed the area.', 50, 0, 0],
            ['Flowering', 'මල් පිපීම', 55, 'flower', 'Maintain consistent moisture.', 0, 0, 0],
            ['Harvesting', 'අස්වනු නෙලීම', 110, 'shopping-basket', 'Harvest when husks turn brown.', 0, 0, 0],
        ];

        foreach ($maizeStages as [$name, $name_si, $offset, $icon, $advice, $urea, $tsp, $mop]) {
            \App\Models\CropStage::updateOrCreate(
                ['crop_variety_id' => $vMaize->id, 'name' => $name],
                [
                    'name_si' => $name_si,
                    'days_offset' => $offset,
                    'icon' => $icon,
                    'advice' => $advice,
                    'urea_per_acre_kg' => $urea,
                    'tsp_per_acre_kg' => $tsp,
                    'mop_per_acre_kg' => $mop
                ]
            );
        }

        // 2. Green Gram (Mung Bean) - Good for dry/sandy soils
        $mung = Crop::updateOrCreate(['name' => 'Green Gram'], [
            'name_si' => 'මුං ඇට',
            'name_ta' => 'பயறு',
            'category' => 'grain',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        CropVariety::updateOrCreate(['variety_name' => 'MI 6'], [
            'crop_id' => $mung->id,
            'variety_name_si' => 'එම්.අයි. 6',
            'variety_name_ta' => 'எம்.ஐ. 6',
            'growth_days' => 65,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earth', 'Sandy', 'Red-Yellow Latosols', 'Lateritic'],
            'yield_per_acre_kg' => 800,
            'seed_per_acre_kg' => 10,
            'base_market_price_per_kg' => 600,
        ]);

        // 3. Sweet Potato - Very hardy
        $sweetPotato = Crop::updateOrCreate(['name' => 'Sweet Potato'], [
            'name_si' => 'බතල',
            'name_ta' => 'சர்க்கரைவள்ளி',
            'category' => 'vegetable',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        CropVariety::updateOrCreate(['variety_name' => 'Wariapola Red'], [
            'crop_id' => $sweetPotato->id,
            'variety_name_si' => 'වාරියපොළ රතු',
            'variety_name_ta' => 'வாரியாபொல சிவப்பு',
            'growth_days' => 110,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earth', 'Alluvial', 'Sandy', 'Red Yellow Podzolic', 'Black Soil', 'Grumusols'],
            'yield_per_acre_kg' => 12000,
            'seed_per_acre_kg' => 0, // Cuttings
            'base_market_price_per_kg' => 110,
        ]);

        // 4. Cassava - Good for Lateritic and Black Soil
        $cassava = Crop::updateOrCreate(['name' => 'Cassava'], [
            'name_si' => 'මඤ්ඤොක්කා',
            'name_ta' => 'மரவள்ளி',
            'category' => 'vegetable',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        CropVariety::updateOrCreate(['variety_name' => 'Kirikawadi'], [
            'crop_id' => $cassava->id,
            'variety_name_si' => 'කිරිකාවඩි',
            'variety_name_ta' => 'கிரிகாவடி',
            'growth_days' => 240,
            'season' => 'both',
            'soil_types' => ['Lateritic', 'Red Yellow Podzolic', 'Black Soil', 'Reddish Brown Earth'],
            'yield_per_acre_kg' => 15000,
            'seed_per_acre_kg' => 0, // Cuttings
            'base_market_price_per_kg' => 80,
        ]);

        // 5. Kurakkan (Finger Millet) - For Black Soil and Grumusols
        $millet = Crop::updateOrCreate(['name' => 'Finger Millet'], [
            'name_si' => 'කුරක්කන්',
            'name_ta' => 'குரக்கன்',
            'category' => 'grain',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        CropVariety::updateOrCreate(['variety_name' => 'Oshada'], [
            'crop_id' => $millet->id,
            'variety_name_si' => 'ඕෂධ',
            'variety_name_ta' => 'ஓஷத',
            'growth_days' => 105,
            'season' => 'both',
            'soil_types' => ['Black Soil', 'Grumusols', 'Reddish Brown Earth'],
            'yield_per_acre_kg' => 1200,
            'seed_per_acre_kg' => 4,
            'base_market_price_per_kg' => 450,
        ]);
        
        // 6. Groundnut - For Sandy Latosols and Sandy soils
        $groundnut = Crop::updateOrCreate(['name' => 'Groundnut'], [
            'name_si' => 'රටකජු',
            'name_ta' => 'நிலக்கடலை',
            'category' => 'grain',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        ]);

        CropVariety::updateOrCreate(['variety_name' => 'Tissa'], [
            'crop_id' => $groundnut->id,
            'variety_name_si' => 'තිස්ස',
            'variety_name_ta' => 'திஸ்ஸ',
            'growth_days' => 95,
            'season' => 'both',
            'soil_types' => ['Red-Yellow Latosols', 'Sandy', 'Reddish Brown Earth'],
            'yield_per_acre_kg' => 1500,
            'seed_per_acre_kg' => 50,
            'base_market_price_per_kg' => 550,
        ]);
    }
}
