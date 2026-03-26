<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================
        // GRAINS
        // =========================================================

        $rice = Crop::create([
            'name' => 'Rice',
            'category' => 'grain',
            'description' => 'Staple food crop of Sri Lanka, grown in Maha and Yala seasons in paddy fields.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'all',
        ]);
        foreach ([
        ['BG 300', 90, 'both', ['Alluvial', 'Clay Loam', 'Sandy Loam'], 22, 34, 150, 'high'],
        ['BG 352', 105, 'both', ['Alluvial', 'Clay', 'Clay Loam'], 22, 34, 150, 'high'],
        ['BG 94-1', 120, 'maha', ['Alluvial', 'Clay', 'Lateritic'], 22, 32, 180, 'high'],
        ['AT 307', 105, 'both', ['Alluvial', 'Sandy Loam', 'Clay Loam'], 24, 35, 140, 'high'],
        ['Samba Mahee', 135, 'maha', ['Alluvial', 'Clay'], 20, 30, 200, 'high'],
        ] as [$vname, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create(['crop_id' => $rice->id, 'variety_name' => $vname, 'growth_days' => $days,
                'season' => $season, 'soil_types' => json_encode($soils), 'min_temp' => $minT,
                'max_temp' => $maxT, 'min_rainfall' => $minR, 'water_requirement' => $water]);
        }

        $maize = Crop::create([
            'name' => 'Maize', 'category' => 'grain',
            'description' => 'Widely grown as food and feed crop in dry and intermediate zones.',
            'ideal_months' => json_encode([4, 5, 9, 10, 11]),
            'climate_zone' => 'dry',
        ]);
        foreach ([
        ['Ruwan', 105, 'both', ['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic'], 18, 35, 60, 'medium'],
        ['DMRSL-01', 100, 'both', ['Sandy Loam', 'Lateritic'], 20, 35, 60, 'medium'],
        ] as [$vname, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create(['crop_id' => $maize->id, 'variety_name' => $vname, 'growth_days' => $days,
                'season' => $season, 'soil_types' => json_encode($soils), 'min_temp' => $minT,
                'max_temp' => $maxT, 'min_rainfall' => $minR, 'water_requirement' => $water]);
        }

        $kurakkan = Crop::create([
            'name' => 'Kurakkan (Finger Millet)', 'category' => 'grain',
            'description' => 'Drought-tolerant traditional grain, ideal for dry zone small-holders.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $kurakkan->id, 'variety_name' => 'Rawana', 'growth_days' => 90,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Lateritic', 'Sandy']),
            'min_temp' => 18, 'max_temp' => 38, 'min_rainfall' => 40, 'water_requirement' => 'low']);

        $sesame = Crop::create([
            'name' => 'Sesame (Thal)', 'category' => 'grain',
            'description' => 'Oil seed crop well suited to dry zone conditions.',
            'ideal_months' => json_encode([3, 4, 8, 9]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $sesame->id, 'variety_name' => 'MI-4', 'growth_days' => 85,
            'season' => 'yala', 'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 25, 'max_temp' => 38, 'min_rainfall' => 40, 'water_requirement' => 'low']);

        // =========================================================
        // VEGETABLES
        // =========================================================

        $tomato = Crop::create([
            'name' => 'Tomato', 'category' => 'vegetable',
            'description' => 'High-demand vegetable for fresh consumption and processing.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'intermediate',
        ]);
        foreach ([
        ['Thilina', 90, 'yala', ['Sandy Loam', 'Red Yellow Podzolic', 'Lateritic'], 15, 30, 60, 'medium'],
        ['Lanka Cherry', 75, 'both', ['Sandy Loam', 'Alluvial'], 18, 32, 60, 'medium'],
        ] as [$vname, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create(['crop_id' => $tomato->id, 'variety_name' => $vname, 'growth_days' => $days,
                'season' => $season, 'soil_types' => json_encode($soils), 'min_temp' => $minT,
                'max_temp' => $maxT, 'min_rainfall' => $minR, 'water_requirement' => $water]);
        }

        $chili = Crop::create([
            'name' => 'Chili', 'category' => 'vegetable',
            'description' => 'Essential spice crop for Sri Lankan cuisine, exported widely.',
            'ideal_months' => json_encode([9, 10, 11, 12]),
            'climate_zone' => 'dry',
        ]);
        foreach ([
        ['MI-2', 120, 'maha', ['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic'], 18, 35, 60, 'medium'],
        ['Kuliyapitiya Local', 135, 'maha', ['Sandy Loam', 'Alluvial'], 18, 32, 80, 'medium'],
        ] as [$vname, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create(['crop_id' => $chili->id, 'variety_name' => $vname, 'growth_days' => $days,
                'season' => $season, 'soil_types' => json_encode($soils), 'min_temp' => $minT,
                'max_temp' => $maxT, 'min_rainfall' => $minR, 'water_requirement' => $water]);
        }

        $brinjal = Crop::create([
            'name' => 'Brinjal (Eggplant)', 'category' => 'vegetable',
            'description' => 'Versatile vegetable widely grown across all climate zones.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 9, 10, 11, 12]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create(['crop_id' => $brinjal->id, 'variety_name' => 'Padagoda MI-2', 'growth_days' => 120,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Clay Loam', 'Lateritic']),
            'min_temp' => 22, 'max_temp' => 35, 'min_rainfall' => 50, 'water_requirement' => 'medium']);

        $okra = Crop::create([
            'name' => 'Okra (Ladies\' Fingers)', 'category' => 'vegetable',
            'description' => 'Fast-growing vegetable suited to warm humid conditions.',
            'ideal_months' => json_encode([3, 4, 5, 6, 9, 10]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create(['crop_id' => $okra->id, 'variety_name' => 'MI Super', 'growth_days' => 60,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Lateritic']),
            'min_temp' => 22, 'max_temp' => 38, 'min_rainfall' => 50, 'water_requirement' => 'medium']);

        $bitterGourd = Crop::create([
            'name' => 'Bitter Gourd', 'category' => 'vegetable',
            'description' => 'Popular gourd vegetable, grows well in warm and humid areas.',
            'ideal_months' => json_encode([2, 3, 4, 5, 6, 7, 8, 9]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create(['crop_id' => $bitterGourd->id, 'variety_name' => 'MC 43', 'growth_days' => 75,
            'season' => 'yala', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Red Yellow Podzolic']),
            'min_temp' => 24, 'max_temp' => 36, 'min_rainfall' => 80, 'water_requirement' => 'medium']);

        $beans = Crop::create([
            'name' => 'Beans (Bush Beans)', 'category' => 'vegetable',
            'description' => 'Quick maturing legume-vegetable grown in upcountry areas.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12, 1]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create(['crop_id' => $beans->id, 'variety_name' => 'Wade', 'growth_days' => 65,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Red Yellow Podzolic', 'Lateritic']),
            'min_temp' => 15, 'max_temp' => 28, 'min_rainfall' => 60, 'water_requirement' => 'medium']);

        $cabbage = Crop::create([
            'name' => 'Cabbage', 'category' => 'vegetable',
            'description' => 'Cool-weather crop mainly grown in Nuwara Eliya highlands.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create(['crop_id' => $cabbage->id, 'variety_name' => 'KY Cross', 'growth_days' => 90,
            'season' => 'both', 'soil_types' => json_encode(['Red Yellow Podzolic', 'Sandy Loam']),
            'min_temp' => 10, 'max_temp' => 24, 'min_rainfall' => 80, 'water_requirement' => 'medium']);

        $pumpkin = Crop::create([
            'name' => 'Pumpkin', 'category' => 'vegetable',
            'description' => 'Hardy vine vegetable tolerant of dry conditions.',
            'ideal_months' => json_encode([3, 4, 5, 8, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $pumpkin->id, 'variety_name' => 'Local Red', 'growth_days' => 90,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic', 'Alluvial']),
            'min_temp' => 22, 'max_temp' => 38, 'min_rainfall' => 40, 'water_requirement' => 'low']);

        $carrot = Crop::create([
            'name' => 'Carrot', 'category' => 'vegetable',
            'description' => 'Root vegetable best grown in cool upcountry regions.',
            'ideal_months' => json_encode([7, 8, 9, 10, 11, 12, 1]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create(['crop_id' => $carrot->id, 'variety_name' => 'Nantes', 'growth_days' => 100,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Red Yellow Podzolic']),
            'min_temp' => 12, 'max_temp' => 25, 'min_rainfall' => 60, 'water_requirement' => 'medium']);

        $leek = Crop::create([
            'name' => 'Leek', 'category' => 'vegetable',
            'description' => 'Grown in the upcountry area, mostly Nuwara Eliya region.',
            'ideal_months' => json_encode([7, 8, 9, 10, 11]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create(['crop_id' => $leek->id, 'variety_name' => 'Lanka White', 'growth_days' => 120,
            'season' => 'both', 'soil_types' => json_encode(['Red Yellow Podzolic', 'Sandy Loam']),
            'min_temp' => 10, 'max_temp' => 22, 'min_rainfall' => 80, 'water_requirement' => 'medium']);

        // =========================================================
        // LEGUMES
        // =========================================================

        $cowpea = Crop::create([
            'name' => 'Cowpea (Mung)', 'category' => 'vegetable',
            'description' => 'Short season legume suited to dry zone inter-cropping.',
            'ideal_months' => json_encode([3, 4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $cowpea->id, 'variety_name' => 'Waruni', 'growth_days' => 70,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 20, 'max_temp' => 38, 'min_rainfall' => 40, 'water_requirement' => 'low']);

        $groundnut = Crop::create([
            'name' => 'Groundnut (Peanut)', 'category' => 'grain',
            'description' => 'Oil and protein rich crop , major in dry zone.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $groundnut->id, 'variety_name' => 'Tikiri', 'growth_days' => 110,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 22, 'max_temp' => 36, 'min_rainfall' => 60, 'water_requirement' => 'low']);

        $soybean = Crop::create([
            'name' => 'Soybean', 'category' => 'grain',
            'description' => 'High-protein cash legume adaptable across zones.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create(['crop_id' => $soybean->id, 'variety_name' => 'PB 1', 'growth_days' => 95,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Clay Loam']),
            'min_temp' => 20, 'max_temp' => 32, 'min_rainfall' => 60, 'water_requirement' => 'medium']);

        // =========================================================
        // FRUITS
        // =========================================================

        $banana = Crop::create([
            'name' => 'Banana', 'category' => 'fruit',
            'description' => 'Most widely grown fruit crop in Sri Lanka, year-round cultivation.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create(['crop_id' => $banana->id, 'variety_name' => 'Embul (Sour Banana)', 'growth_days' => 300,
            'season' => 'both', 'soil_types' => json_encode(['Alluvial', 'Sandy Loam', 'Clay Loam']),
            'min_temp' => 24, 'max_temp' => 38, 'min_rainfall' => 100, 'water_requirement' => 'high']);

        $papaya = Crop::create([
            'name' => 'Papaya', 'category' => 'fruit',
            'description' => 'Fast-fruiting tropical fruit grown across all zones.',
            'ideal_months' => json_encode([3, 4, 5, 9, 10]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create(['crop_id' => $papaya->id, 'variety_name' => 'Red Lady', 'growth_days' => 270,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Lateritic']),
            'min_temp' => 22, 'max_temp' => 38, 'min_rainfall' => 80, 'water_requirement' => 'medium']);

        $watermelon = Crop::create([
            'name' => 'Watermelon', 'category' => 'fruit',
            'description' => 'High-value seasonal fruit crop best in dry zone sandy soils.',
            'ideal_months' => json_encode([2, 3, 4, 5, 6]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create(['crop_id' => $watermelon->id, 'variety_name' => 'Sugar Baby', 'growth_days' => 80,
            'season' => 'yala', 'soil_types' => json_encode(['Sandy', 'Sandy Loam']),
            'min_temp' => 25, 'max_temp' => 40, 'min_rainfall' => 30, 'water_requirement' => 'medium']);

        $pineapple = Crop::create([
            'name' => 'Pineapple', 'category' => 'fruit',
            'description' => 'Grown mainly in the wet and intermediate zones.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create(['crop_id' => $pineapple->id, 'variety_name' => 'Mauritius', 'growth_days' => 540,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic']),
            'min_temp' => 22, 'max_temp' => 35, 'min_rainfall' => 100, 'water_requirement' => 'medium']);

        // =========================================================
        // CASH CROPS
        // =========================================================

        $coconut = Crop::create([
            'name' => 'Coconut', 'category' => 'fruit',
            'description' => 'Sri Lanka\'s national tree — major export and subsistence crop.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create(['crop_id' => $coconut->id, 'variety_name' => 'Sri Lanka Tall', 'growth_days' => 2555,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Sandy', 'Lateritic']),
            'min_temp' => 20, 'max_temp' => 38, 'min_rainfall' => 100, 'water_requirement' => 'medium']);

        $cinnamon = Crop::create([
            'name' => 'Cinnamon', 'category' => 'grain',
            'description' => 'World-renowned Sri Lankan spice crop, true cinnamon.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create(['crop_id' => $cinnamon->id, 'variety_name' => 'C5', 'growth_days' => 730,
            'season' => 'both', 'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 22, 'max_temp' => 35, 'min_rainfall' => 120, 'water_requirement' => 'medium']);
    }
}