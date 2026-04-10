<?php

namespace Database\Seeders;

use App\Models\CropVariety;
use App\Models\CropStage;
use Illuminate\Database\Seeder;

class CropStageSeeder extends Seeder
{
    public function run(): void
    {
        $varieties = [
            'Bg 300' => ['type' => 'rice', 'days' => 90],
            'Bg 352' => ['type' => 'rice', 'days' => 105],
            'Bg 94-1' => ['type' => 'rice', 'days' => 120],
            'At 307' => ['type' => 'rice', 'days' => 105],
            'Samba Mahee' => ['type' => 'rice', 'days' => 135],
            'Ruwan' => ['type' => 'maize', 'days' => 105],
            'DMRSL-01' => ['type' => 'maize', 'days' => 100],
            'Rawana' => ['type' => 'kurakkan', 'days' => 90],
            'MI 4' => ['type' => 'sesame', 'days' => 85],
            'Thilina' => ['type' => 'tomato', 'days' => 90],
            'Lanka Cherry' => ['type' => 'tomato', 'days' => 75],
            'MI 2' => ['type' => 'chili', 'days' => 120],
            'Kuliyapitiya Local' => ['type' => 'chili', 'days' => 135],
            'Padagoda' => ['type' => 'brinjal', 'days' => 120],
            'MI Super' => ['type' => 'okra', 'days' => 60],
            'MC 43' => ['type' => 'bitter_gourd', 'days' => 75],
            'Wade' => ['type' => 'beans', 'days' => 65],
            'KY Cross' => ['type' => 'cabbage', 'days' => 90],
            'Local Red' => ['type' => 'pumpkin', 'days' => 90],
            'Nantes' => ['type' => 'carrot', 'days' => 100],
            'Lanka White' => ['type' => 'leek', 'days' => 120],
            'Waruni' => ['type' => 'cowpea', 'days' => 70],
            'Tikiri' => ['type' => 'groundnut', 'days' => 110],
            'PB 1' => ['type' => 'soybean', 'days' => 95],
            'Embul (Sour Banana)' => ['type' => 'banana', 'days' => 300],
            'Red Lady' => ['type' => 'papaya', 'days' => 270],
            'Sugar Baby' => ['type' => 'watermelon', 'days' => 80],
            'Mauritius' => ['type' => 'pineapple', 'days' => 540],
            'Sri Lanka Tall' => ['type' => 'coconut', 'days' => 2555],
            'Sri Gemunu' => ['type' => 'cinnamon', 'days' => 730],
        ];

        foreach ($varieties as $name => $meta) {
            $variety = CropVariety::where('variety_name', $name)->first();
            if (!$variety) continue;

            $variety->stages()->delete();

            switch ($meta['type']) {
                case 'rice': $this->seedRice($variety, $meta['days']); break;
                case 'maize': $this->seedMaize($variety, $meta['days']); break;
                case 'tomato': $this->seedTomato($variety, $meta['days']); break;
                case 'chili': $this->seedChili($variety, $meta['days']); break;
                case 'okra': $this->seedOkra($variety, $meta['days']); break;
                case 'kurakkan': $this->seedKurakkan($variety, $meta['days']); break;
                default: $this->seedDefault($variety, $meta['days']); break;
            }
        }
    }

    private function createStages($variety, $stages) {
        foreach ($stages as $stage) {
            CropStage::create(array_merge($stage, ['crop_variety_id' => $variety->id]));
        }
    }

    private function seedRice($v, $days) {
        $this->createStages($v, [
            ['name' => 'Paddy Field Preparation', 'name_si' => 'කුඹුරු සකස් කිරීම', 'name_ta' => 'வயல் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply basal fertilizer (V-mixture). Clear weeds.', 'advice_si' => 'මූලික පොහොර (V-මිශ්‍රණය) යොදන්න. වල් පැලෑටි ඉවත් කරන්න.', 'advice_ta' => 'அடிப்படை உரம் (V-கலவை) இடவும். களைகளை அகற்றவும்.', 'description' => 'Basal: 50kg Urea, 100kg TSP, 50kg MOP per hectare.', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 40, 'mop_per_acre_kg' => 20],
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Sow pre-germinated seeds.', 'advice_si' => 'පැළ වූ බීජ වපුරන්න.', 'advice_ta' => 'முளைக்கட்டிய விதைகளை விதைக்கவும்.', 'description' => 'Maintain saturated soil but no standing water for first 3 days.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'First Top Dressing', 'name_si' => 'පළමු මතුපිට පොහොර', 'name_ta' => 'முதல் மேலுரம்', 'days_offset' => 14, 'icon' => 'droplets', 'advice' => 'Apply Urea. Maintain 3-5cm water.', 'advice_si' => 'යූරියා යොදන්න. සෙ.මී. 3-5 ක ජල මට්ටමක් පවත්වා ගන්න.', 'advice_ta' => 'யூரியா இடவும். 3-5 செமீ நீர் மட்டத்தை பராமரிக்கவும்.', 'description' => 'Promotes tillering and early growth.', 'urea_per_acre_kg' => 35, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Heading Phase', 'name_si' => 'කරල් පීදීම', 'name_ta' => 'கதிர் விடும் நிலை', 'days_offset' => round($days * 0.75), 'icon' => 'flower', 'advice' => 'Apply final MOP dose. Critical water stage.', 'advice_si' => 'අවසාන MOP මාත්‍රාව යොදන්න. ජලය ඉතා අත්‍යවශ්‍ය අවධියකි.', 'advice_ta' => 'இறுதி MOP அளவை இடவும். முக்கியமான நீர் தேவைப்படும் நிலை.', 'description' => 'Do not allow the field to dry during grain filling.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 15],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Drain field 10 days before harvest.', 'advice_si' => 'අස්වනු නෙලීමට දින 10 කට පෙර ජලය ඉවත් කරන්න.', 'advice_ta' => 'அறுவடைக்கு 10 நாட்களுக்கு முன்பு தண்ணீரை வடிக்கவும்.', 'description' => 'Target 14% moisture for storage.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedMaize($v, $days) {
        $this->createStages($v, [
            ['name' => 'Land Preparation', 'name_si' => 'බිම් සකස් කිරීම', 'name_ta' => 'நிலம் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply organic manure and basal fertilizer.', 'advice_si' => 'කාබනික පොහොර සහ මූලික පොහොර යොදන්න.', 'advice_ta' => 'கரிம உரம் மற்றும் அடிப்படை உரம் இடவும்.', 'description' => 'Deep ploughing ensures better root penetration.', 'urea_per_acre_kg' => 15, 'tsp_per_acre_kg' => 30, 'mop_per_acre_kg' => 15],
            ['name' => 'Sowing', 'name_si' => 'වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x25cm. Plant 5cm deep.', 'advice_si' => 'සෙ.මී. 60x25 පරතරය තබා ගන්න. සෙ.මී. 5 ක් ගැඹුරින් සිටුවන්න.', 'advice_ta' => '60x25 செமீ இடைவெளி விடவும். 5 செமீ ஆழத்தில் நடவும்.', 'description' => 'Optimal moisture is critical for uniform germination.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Top Dressing', 'name_si' => 'මතුපිට පොහොර යෙදීම', 'name_ta' => 'மேலுரமிடுதல்', 'days_offset' => 30, 'icon' => 'droplets', 'advice' => 'Apply Urea and perform earthing up.', 'advice_si' => 'යූරියා යොදා පස් දමන්න.', 'advice_ta' => 'யூரியா இட்டு பாத்திகளை அணைக்கவும்.', 'description' => 'Watch for Fall Armyworm (Sena) infestation.', 'urea_per_acre_kg' => 40, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 10],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when husks are brown.', 'advice_si' => 'කොපු දුඹුරු පැහැ වූ විට අස්වනු නෙලන්න.', 'advice_ta' => 'தோகைகள் பழுப்பு நிறமானதும் அறுவடை செய்யவும்.', 'description' => 'Grains should be hard and dry.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedTomato($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -21, 'icon' => 'thermometer', 'advice' => 'Use sterilized soil mix.', 'advice_si' => 'විෂබීජහරණය කළ පස් මිශ්‍රණයක් භාවිතා කරන්න.', 'advice_ta' => 'சுத்திகரிக்கப்பட்ட மண் கலவையைப் பயன்படுத்தவும்.', 'description' => 'Protect seedlings from high heat.', 'urea_per_acre_kg' => 5, 'tsp_per_acre_kg' => 10, 'mop_per_acre_kg' => 5],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x45cm. Irrigate immediately.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය භාවිතා කරන්න. වහාම ජලය සපයන්න.', 'advice_ta' => '60x45 செமீ இடைவெளி விடவும். உடனே நீர்ப்பாசனம் செய்யவும்.', 'description' => 'Deep transplanting helps root stability.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'First Top Dressing', 'name_si' => 'පළමු මතුපිට පොහොර', 'name_ta' => 'முதல் மேலுரம்', 'days_offset' => 21, 'icon' => 'trending-up', 'advice' => 'Apply Urea and MOP.', 'advice_si' => 'යූරියා සහ MOP පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மற்றும் MOP உரமிடவும்.', 'description' => 'Promotes early fruit set.', 'urea_per_acre_kg' => 25, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 15],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 65, 'icon' => 'shopping-basket', 'advice' => 'Pick at breaker (pink) stage.', 'advice_si' => 'රෝස පැහැයට හැරෙන විට නෙලන්න.', 'advice_ta' => 'இளஞ்சிவப்பு நிறமாகும் போது பறிக்கவும்.', 'description' => 'Continue picking every 3 days.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedChili($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -28, 'icon' => 'thermometer', 'advice' => 'Protect from heavy rain.', 'advice_si' => 'අධික වර්ෂාවෙන් ආරක්ෂා කරන්න.', 'advice_ta' => 'பலத்த மழையிலிருந்து பாதுகாக்கவும்.', 'description' => 'Ensure good drainage in nursery beds.', 'urea_per_acre_kg' => 5, 'tsp_per_acre_kg' => 10, 'mop_per_acre_kg' => 5],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use 60x45cm spacing.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය භාවිතා කරන්න.', 'advice_ta' => '60x45 செமீ இடைவெளி விடவும்.', 'description' => 'Avoid waterlogging at all costs.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Flowering Dressing', 'name_si' => 'මල් පිපෙන අවධියේ පොහොර', 'name_ta' => 'பூக்கும் கால உரம்', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Apply MOP for fruit set.', 'advice_si' => 'ගෙඩි හටගැනීම සඳහා MOP පොහොර යොදන්න.', 'advice_ta' => 'காய் பிடிப்பதற்கு MOP உரமிடவும்.', 'description' => 'Watch for leaf curl virus.', 'urea_per_acre_kg' => 30, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 20],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 75, 'icon' => 'shopping-basket', 'advice' => 'Harvest green or red.', 'advice_si' => 'අමු හෝ ඉදුණු මිරිස් නෙලන්න.', 'advice_ta' => 'பச்சை அல்லது பழுத்த மிளகாயை அறுவடை செய்யவும்.', 'description' => 'Pick at regular intervals.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedOkra($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Soak seeds before sowing.', 'advice_si' => 'වැපිරීමට පෙර බීජ පොඟවන්න.', 'advice_ta' => 'விதைப்பதற்கு முன் விதைகளை ஊறவைக்கவும்.', 'description' => 'Basal: 25kg Urea, 50kg TSP, 25kg MOP per acre.', 'urea_per_acre_kg' => 25, 'tsp_per_acre_kg' => 50, 'mop_per_acre_kg' => 25],
            ['name' => 'Flowering', 'name_si' => 'මල් පිපීම', 'name_ta' => 'பூக்கும் நிலை', 'days_offset' => 35, 'icon' => 'flower', 'advice' => 'Apply Urea top dressing.', 'advice_si' => 'යූරියා මතුපිට පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மேலுரமிடவும்.', 'description' => 'Apply 30kg Urea per acre.', 'urea_per_acre_kg' => 30, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'First Harvest', 'name_si' => 'පළමු අස්වැන්න', 'name_ta' => 'முதல் அறுவடை', 'days_offset' => 45, 'icon' => 'shopping-basket', 'advice' => 'Harvest tender pods daily.', 'advice_si' => 'ලාබාල කරල් දිනපතා නෙලන්න.', 'advice_ta' => 'இளம் காய்களை தினமும் பறிக்கவும்.', 'description' => 'Fibrous pods are unmarketable.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedKurakkan($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Apply basal fertilizer.', 'advice_si' => 'මූලික පොහොර යොදන්න.', 'advice_ta' => 'அடிப்படை உரமிடவும்.', 'description' => 'Mix seeds with sand.', 'urea_per_acre_kg' => 15, 'tsp_per_acre_kg' => 30, 'mop_per_acre_kg' => 15],
            ['name' => 'Heading', 'name_si' => 'කරල් පීදීම', 'name_ta' => 'கதிர் விடும் நிலை', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Apply Urea top dressing.', 'advice_si' => 'යූරියා මතුපිට පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மேலுரமிடவும்.', 'description' => 'Critical for grain filling.', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Dry heads in sun.', 'advice_si' => 'අස්වැන්න අව්වේ වියළන්න.', 'advice_ta' => 'கதிர்களை வெயிலில் காயவைக்கவும்.', 'description' => 'Store at low moisture.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedDefault($v, $days) {
        $this->createStages($v, [
            ['name' => 'Preparation', 'name_si' => 'සූදානම් කිරීම', 'name_ta' => 'தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'General preparation.', 'advice_si' => 'පොදු සූදානම් කිරීම.', 'advice_ta' => 'பொதுவான தயாரிப்பு.', 'urea_per_acre_kg' => 10, 'tsp_per_acre_kg' => 20, 'mop_per_acre_kg' => 10],
            ['name' => 'Growth', 'name_si' => 'වර්ධනය', 'name_ta' => 'வளர்ச்சி', 'days_offset' => round($days * 0.4), 'icon' => 'trending-up', 'advice' => 'Monitor growth.', 'advice_si' => 'වර්ධනය නිරීක්ෂණය කරන්න.', 'advice_ta' => 'வளர்ச்சியைக் கவனிக்கவும்.', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 10],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-basket', 'advice' => 'Final harvest.', 'advice_si' => 'අවසාන අස්වනු නෙලීම.', 'advice_ta' => 'இறுதி அறுவடை.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }
}
