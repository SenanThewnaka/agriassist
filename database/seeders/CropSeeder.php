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
        // GRAINS & CEREALS
        // =========================================================

        $rice = Crop::updateOrCreate(['name' => 'Rice'], [
            'name_si' => 'වී',
            'name_ta' => 'நெல்',
            'category' => 'grain',
            'description' => 'Staple food crop of Sri Lanka.',
            'ideal_months' => [4, 5, 9, 10],
            'climate_zone' => 'all',
        ]);

        foreach ([
            ['Bg 300', 'බී.ජී. 300', 'பி.ஜி. 300', 90, 'both', ['Alluvial Soils', 'Clay Loam'], 22, 34, 150, 'high', 4500, 45, 110],
            ['Bg 352', 'බී.ජී. 352', 'பி.ஜி. 352', 105, 'both', ['Alluvial Soils', 'Grumusols'], 22, 34, 150, 'high', 5000, 45, 110],
            ['At 307', 'ඒ.ටී. 307', 'ஏ.டி. 307', 105, 'both', ['Alluvial Soils', 'Regosols'], 24, 35, 140, 'high', 4800, 45, 115],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water, $yield, $seed, $price]) {
            CropVariety::updateOrCreate(['variety_name' => $vname], [
                'crop_id' => $rice->id,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => $soils,
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water,
                'yield_per_acre_kg' => $yield,
                'seed_per_acre_kg' => $seed,
                'base_market_price_per_kg' => $price
            ]);
        }

        // =========================================================
        // VEGETABLES
        // =========================================================

        $tomato = Crop::updateOrCreate(['name' => 'Tomato'], [
            'name_si' => 'තක්කාලි',
            'name_ta' => 'தக்காளி',
            'category' => 'vegetable',
            'ideal_months' => [6, 7, 8, 9, 10, 11, 12],
            'climate_zone' => 'intermediate',
        ]);
        foreach ([
            ['Thilina', 'තිළිණ', 'திலினா', 90, 'yala', ['Red-Yellow Podzolic Soils', 'Alluvial Soils'], 15, 30, 60, 'medium', 12000, 0.15, 180],
            ['Lanka Cherry', 'ලංකා චෙරි', 'லங்கா செர்ரி', 75, 'both', ['Red-Yellow Podzolic Soils', 'Alluvial Soils'], 18, 32, 60, 'medium', 8000, 0.12, 220],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water, $yield, $seed, $price]) {
            CropVariety::updateOrCreate(['variety_name' => $vname], [
                'crop_id' => $tomato->id,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => $soils,
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water,
                'yield_per_acre_kg' => $yield,
                'seed_per_acre_kg' => $seed,
                'base_market_price_per_kg' => $price
            ]);
        }

        $brinjal = Crop::updateOrCreate(['name' => 'Brinjal'], [
            'name_si' => 'වම්බටු',
            'name_ta' => 'கத்தரிக்காய்',
            'category' => 'vegetable',
            'ideal_months' => [1, 2, 3, 4, 5, 9, 10, 11, 12],
            'climate_zone' => 'all',
        ]);
        CropVariety::updateOrCreate(['variety_name' => 'Padagoda'], [
            'crop_id' => $brinjal->id,
            'variety_name_si' => 'පාදගොඩ',
            'variety_name_ta' => 'படாகொட',
            'growth_days' => 120,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earths', 'Alluvial Soils'],
            'min_temp' => 22,
            'max_temp' => 35,
            'min_rainfall' => 50,
            'water_requirement' => 'medium',
            'yield_per_acre_kg' => 15000,
            'seed_per_acre_kg' => 0.2,
            'base_market_price_per_kg' => 160
        ]);

        $beans = Crop::updateOrCreate(['name' => 'Beans'], [
            'name_si' => 'බෝංචි',
            'name_ta' => 'பீன்ஸ்',
            'category' => 'vegetable',
            'ideal_months' => [6, 7, 8, 9, 10, 11, 12, 1],
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::updateOrCreate(['variety_name' => 'Wade'], [
            'crop_id' => $beans->id,
            'variety_name_si' => 'වේඩ්',
            'variety_name_ta' => 'வேட்',
            'growth_days' => 65,
            'season' => 'both',
            'soil_types' => ['Red-Yellow Podzolic Soils', 'Alluvial Soils'],
            'min_temp' => 15,
            'max_temp' => 28,
            'min_rainfall' => 60,
            'water_requirement' => 'medium',
            'yield_per_acre_kg' => 6000,
            'seed_per_acre_kg' => 30,
            'base_market_price_per_kg' => 350
        ]);

        // =========================================================
        // FRUITS
        // =========================================================

        $banana = Crop::updateOrCreate(['name' => 'Banana'], [
            'name_si' => 'කෙසෙල්',
            'name_ta' => 'வாழை',
            'category' => 'fruit',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'climate_zone' => 'wet',
        ]);
        CropVariety::updateOrCreate(['variety_name' => 'Embul (Sour Banana)'], [
            'crop_id' => $banana->id,
            'variety_name_si' => 'ඇඹුල් කෙසෙල්',
            'variety_name_ta' => 'ஆனப்பாவாடை வாழை',
            'growth_days' => 300,
            'season' => 'both',
            'soil_types' => ['Alluvial Soils', 'Reddish Brown Earths'],
            'min_temp' => 24,
            'max_temp' => 38,
            'min_rainfall' => 100,
            'water_requirement' => 'high',
            'yield_per_acre_kg' => 8000,
            'seed_per_acre_kg' => 0, // propagated via suckers
            'base_market_price_per_kg' => 180
        ]);

        $coconut = Crop::updateOrCreate(['name' => 'Coconut'], [
            'name_si' => 'පොල්',
            'name_ta' => 'தேங்காய்',
            'category' => 'fruit',
            'ideal_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'climate_zone' => 'wet',
        ]);
        CropVariety::updateOrCreate(['variety_name' => 'Sri Lanka Tall'], [
            'crop_id' => $coconut->id,
            'variety_name_si' => 'ලංකා උස පොල්',
            'variety_name_ta' => 'இலங்கை உயரமான தென்னை',
            'growth_days' => 2555,
            'season' => 'both',
            'soil_types' => ['Alluvial Soils', 'Coastal Sands'],
            'min_temp' => 20,
            'max_temp' => 38,
            'min_rainfall' => 100,
            'water_requirement' => 'medium',
            'yield_per_acre_kg' => 4000, // per year approx
            'seed_per_acre_kg' => 64, // nuts per acre
            'base_market_price_per_kg' => 90 // per nut basis internally
        ]);
    }
}
