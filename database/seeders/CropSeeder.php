<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        // Rice
        $rice = Crop::create([
            'name' => 'Rice',
            'category' => 'grain',
            'description' => 'A staple food in Sri Lanka, primarily grown in the Maha and Yala seasons.'
        ]);

        CropVariety::create([
            'crop_id' => $rice->id,
            'variety_name' => 'BG 300',
            'growth_days' => 90,
            'season' => 'both',
            'notes' => 'Early maturing variety.'
        ]);

        CropVariety::create([
            'crop_id' => $rice->id,
            'variety_name' => 'BG 352',
            'growth_days' => 105,
            'season' => 'both',
            'notes' => 'Medium maturing variety.'
        ]);

        CropVariety::create([
            'crop_id' => $rice->id,
            'variety_name' => 'BG 94',
            'growth_days' => 120,
            'season' => 'both',
            'notes' => 'Long duration variety.'
        ]);

        // Tomato
        $tomato = Crop::create([
            'name' => 'Tomato',
            'category' => 'vegetable',
            'description' => 'High-demand vegetable used in various culinary applications.'
        ]);

        CropVariety::create([
            'crop_id' => $tomato->id,
            'variety_name' => 'Thilina',
            'growth_days' => 90,
            'season' => 'yala',
            'notes' => 'Recommended for the Yala season.'
        ]);

        CropVariety::create([
            'crop_id' => $tomato->id,
            'variety_name' => 'Lanka Cherry',
            'growth_days' => 75,
            'season' => 'both',
            'notes' => 'Quick harvest variety.'
        ]);

        // Chili
        $chili = Crop::create([
            'name' => 'Chili',
            'category' => 'vegetable',
            'description' => 'Essential spice for Sri Lankan cuisine.'
        ]);

        CropVariety::create([
            'crop_id' => $chili->id,
            'variety_name' => 'MI-2',
            'growth_days' => 120,
            'season' => 'maha',
            'notes' => 'Typically planted during the Maha season.'
        ]);
    }
}