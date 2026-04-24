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
            ['name' => 'Week 1: Land Preparation', 'name_si' => '1 වන සතිය: බිම් සකස් කිරීම', 'name_ta' => 'வாரம் 1: நிலம் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply basal fertilizer (V-mixture). Clear weeds and ensure even leveling.', 'advice_si' => 'මූලික පොහොර (V-මිශ්‍රණය) යොදන්න. වල් පැලෑටි ඉවත් කර මට්ටම් කරන්න.', 'advice_ta' => 'அடிப்படை உரம் (V-கலவை) இடவும். களைகளை அகற்றி நிலத்தை சமப்படுத்தவும்.', 'description' => 'Basal: 50kg Urea, 100kg TSP, 50kg MOP per hectare. Use organic compost if possible (2 tons/acre).', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 40, 'mop_per_acre_kg' => 20],
            ['name' => 'Week 2: Sowing & Germination', 'name_si' => '2 වන සතිය: වැපිරීම සහ ප්‍රරෝහණය', 'name_ta' => 'வாரம் 2: விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Sow pre-germinated seeds. Keep soil saturated but avoid deep standing water.', 'advice_si' => 'පැළ වූ බීජ වපුරන්න. පස තෙතමනය සහිතව තබා ගන්න.', 'advice_ta' => 'முளைக்கட்டிய விதைகளை விதைக்கவும். மண்ணை ஈரப்பதமாக வைத்திருங்கள்.', 'description' => 'Check for seedling vigor. If heavy rain is expected, delay sowing for 2 days. Monitor for birds.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 4: Early Tillering', 'name_si' => '4 වන සතිය: මුල් ඇඹරුම් අවධිය', 'name_ta' => 'வாரம் 4: ஆரம்பக் கிளைத்தல்', 'days_offset' => 14, 'icon' => 'droplets', 'advice' => 'First top dressing. Maintain 2-3cm water level.', 'advice_si' => 'පළමු මතුපිට පොහොර. සෙ.මී. 2-3 ක ජල මට්ටමක් පවත්වා ගන්න.', 'advice_ta' => 'முதல் மேலுரம். 2-3 செமீ நீர் மட்டத்தை பராமரிக்கவும்.', 'description' => 'Apply Urea. This stage is critical for establishing the number of panicles. Hand-weed any escaped grasses.', 'urea_per_acre_kg' => 35, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 6: Active Tillering', 'name_si' => '6 වන සතිය: සක්‍රිය ඇඹරුම් අවධිය', 'name_ta' => 'வாரம் 6: தீவிரக் கிளைத்தல்', 'days_offset' => 28, 'icon' => 'trending-up', 'advice' => 'Monitor for leaf folders and thrips. Maintain consistent water.', 'advice_si' => 'කොළ හකුලන පණුවා ගැන සැලකිලිමත් වන්න. ජලය නියතව තබා ගන්න.', 'advice_ta' => 'இலைச் சுருட்டிப் புழுக்களைக் கண்காணிக்கவும். நிலையான நீரைப் பராமரிக்கவும்.', 'description' => 'Check the underside of leaves for pests. If yellowing occurs, check drainage. Use Neem oil spray for organic pest control.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 8: Panicle Initiation', 'name_si' => '8 වන සතිය: කරල් හටගැනීම', 'name_ta' => 'வாரம் 8: கதிர் உருவாக்கம்', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Second top dressing. Increase water level to 5cm.', 'advice_si' => 'දෙවන මතුපිට පොහොර. ජල මට්ටම සෙ.මී. 5 දක්වා වැඩි කරන්න.', 'advice_ta' => 'இரண்டாவது மேலுரம். நீர் மட்டத்தை 5 செமீ ஆக அதிகரிக்கவும்.', 'description' => 'Critical stage for grain yield. Apply Urea. Ensure the field never dries out during this transition.', 'urea_per_acre_kg' => 25, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 10: Booting & Heading', 'name_si' => '10 වන සතිය: කරල් පීදීම', 'name_ta' => 'வாரம் 10: கதிர் வெளிவருதல்', 'days_offset' => 60, 'icon' => 'flower', 'advice' => 'Apply MOP dose. Monitor for paddy bugs in early morning.', 'advice_si' => 'MOP මාත්‍රාව යොදන්න. ගොයම් මැස්සා ගැන උදෑසන පරීක්ෂා කරන්න.', 'advice_ta' => 'MOP அளவை இடவும். காலையில் நெல் பூச்சிகளைக் கண்காணிக்கவும்.', 'description' => 'Final fertilizer dose. Paddy bugs can significantly reduce quality; use light traps or botanical extracts.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 15],
            ['name' => 'Week 12: Grain Filling', 'name_si' => '12 වන සතිය: කරල් පැසීම', 'name_ta' => 'வாரம் 12: மணி பால்பிடித்தல்', 'days_offset' => 75, 'icon' => 'sun', 'advice' => 'Keep soil moist but not deeply flooded. Protect from rodents.', 'advice_si' => 'පස තෙතමනය තබා ගන්න. මීයන්ගෙන් ආරක්ෂා කර ගන්න.', 'advice_ta' => 'மண்ணை ஈரப்பதமாக வைத்திருங்கள். எலிகளிடமிருந்து பாதுகாக்கவும்.', 'description' => 'Maintain thin layer of water. Grains should be milky to doughy. Monitor for brown plant hoppers.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Harvest: Ripening Phase', 'name_si' => 'අස්වැන්න: ඉදෙන අවධිය', 'name_ta' => 'அறுவடை: முதிர்ச்சி நிலை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Drain field 10 days before harvest. Harvest when 85% of grains are golden.', 'advice_si' => 'අස්වනු නෙලීමට දින 10 කට පෙර ජලය වඩන්න. කරල් 85% ක් රන්වන් පැහැ වූ විට නෙලන්න.', 'advice_ta' => 'அறுவடைக்கு 10 நாட்களுக்கு முன் தண்ணீரை வடிக்கவும். கதிர்கள் 85% பொன்னிறமானதும் அறுவடை செய்யவும்.', 'description' => 'Grains should be hard and difficult to break with teeth. Thresh as soon as possible after harvest to avoid quality loss.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedMaize($v, $days) {
        $this->createStages($v, [
            ['name' => 'Week 1: Land Preparation', 'name_si' => '1 වන සතිය: බිම් සකස් කිරීම', 'name_ta' => 'வாரம் 1: நிலம் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply organic manure and basal fertilizer.', 'advice_si' => 'කාබනික පොහොර සහ මූලික පොහොර යොදන්න.', 'advice_ta' => 'கரிம உரம் மற்றும் அடிப்படை உரம் இடவும்.', 'description' => 'Deep ploughing ensures better root penetration. Mix 5-10 tons of well-decomposed cattle manure per acre.', 'urea_per_acre_kg' => 15, 'tsp_per_acre_kg' => 30, 'mop_per_acre_kg' => 15],
            ['name' => 'Week 2: Sowing', 'name_si' => '2 වන සතිය: වැපිරීම', 'name_ta' => 'வாரம் 2: விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x25cm. Plant 5cm deep.', 'advice_si' => 'සෙ.මී. 60x25 පරතරය තබා ගන්න. සෙ.මී. 5 ක් ගැඹුරින් සිටුවන්න.', 'advice_ta' => '60x25 செமீ இடைவெளி விடவும். 5 செமீ ஆழத்தில் நடவும்.', 'description' => 'Optimal moisture is critical for uniform germination. If dry, apply light irrigation.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 5: Top Dressing & Weeding', 'name_si' => '5 වන සතිය: මතුපිට පොහොර සහ වල් මර්ධනය', 'name_ta' => 'வாரம் 5: மேலுரமிடுதல் மற்றும் களை எடுத்தல்', 'days_offset' => 30, 'icon' => 'droplets', 'advice' => 'Apply Urea and perform earthing up.', 'advice_si' => 'යූරියා යොදා පස් දමන්න.', 'advice_ta' => 'யூரியா இட்டு பாத்திகளை அணைக்கவும்.', 'description' => 'Watch for Fall Armyworm (Sena) infestation. Check whorls of the plants. Use organic neem-based solutions if early damage is seen.', 'urea_per_acre_kg' => 40, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 10],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when husks are brown.', 'advice_si' => 'කොපු දුඹුරු පැහැ වූ විට අස්වනු නෙලන්න.', 'advice_ta' => 'தோகைகள் பழுப்பு நிறமானதும் அறுவடை செய்யவும்.', 'description' => 'Grains should be hard and dry. Dry the cobs in the sun for 2-3 days before shelling.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedTomato($v, $days) {
        $this->createStages($v, [
            ['name' => 'Week 1: Nursery Care', 'name_si' => '1 වන සතිය: තවාන් කළමනාකරණය', 'name_ta' => 'வாரம் 1: நாற்றங்கால் மேலாண்மை', 'days_offset' => -21, 'icon' => 'thermometer', 'advice' => 'Use sterilized soil mix. Keep seeds moist but not waterlogged.', 'advice_si' => 'විෂබීජහරණය කළ පස් මිශ්‍රණයක් භාවිතා කරන්න.', 'advice_ta' => 'சுத்திகரிக்கப்பட்ட மண் கலவையைப் பயன்படுத்தவும்.', 'description' => 'Protect seedlings from high heat and direct heavy rain. Use a net house if possible.', 'urea_per_acre_kg' => 5, 'tsp_per_acre_kg' => 10, 'mop_per_acre_kg' => 5],
            ['name' => 'Week 3: Seedling Hardening', 'name_si' => '3 වන සතිය: පැළ දැඩි කිරීම', 'name_ta' => 'வாரம் 3: நாற்றுக்களை உறுதிப்படுத்துதல்', 'days_offset' => -7, 'icon' => 'sun', 'advice' => 'Reduce watering slightly and expose to more sunlight.', 'advice_si' => 'ජලය සැපයීම තරමක් අඩු කර හිරු එළියට නිරාවරණය කරන්න.', 'advice_ta' => 'நீர்ப்பாசனத்தை சற்று குறைத்து சூரிய ஒளியில் வைக்கவும்.', 'description' => 'This prepares the seedlings for the shock of transplanting. Monitor for early signs of damping-off.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 4: Transplanting', 'name_si' => '4 වන සතිය: පැළ සිටුවීම', 'name_ta' => 'வாரம் 4: நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x45cm. Irrigate immediately after planting.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය තබා සිටුවන්න. වහාම ජලය සපයන්න.', 'advice_ta' => '60x45 செமீ இடைவெளியில் நடவும். நட்டவுடன் நீர்ப்பாசனம் செய்யவும்.', 'description' => 'Deep transplanting helps root stability. Apply organic mulch (straw or dried leaves) to retain moisture.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 6: Early Vegetative', 'name_si' => '6 වන සතිය: මුල් වර්ධන අවධිය', 'name_ta' => 'வாரம் 6: ஆரம்ப வளர்ச்சி நிலை', 'days_offset' => 14, 'icon' => 'trending-up', 'advice' => 'Monitor for leaf miners. Provide support stakes for tall varieties.', 'advice_si' => 'කොළ කන පණුවා ගැන සැලකිලිමත් වන්න. ආධාරක කූරු සපයන්න.', 'advice_ta' => 'இலை சுரங்கப் புழுக்களைக் கண்காணிக்கவும். முட்டுக்கொடுக்கவும்.', 'description' => 'Check for yellowing. If plants look pale, use liquid organic fertilizer (compost tea).', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 8: First Top Dressing', 'name_si' => '8 වන සතිය: පළමු මතුපිට පොහොර', 'name_ta' => 'வாரம் 8: முதல் மேலுரம்', 'days_offset' => 28, 'icon' => 'droplets', 'advice' => 'Apply Urea and MOP. Remove side suckers to encourage fruit size.', 'advice_si' => 'යූරියා සහ MOP යොදන්න. අතුරු අංකුර ඉවත් කරන්න.', 'advice_ta' => 'யூரியா மற்றும் MOP இடவும். பக்கக் கிளைகளை அகற்றவும்.', 'description' => 'Pruning is essential for good ventilation and reducing fungal risks. Monitor for Whiteflies.', 'urea_per_acre_kg' => 25, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 15],
            ['name' => 'Week 10: Flowering & Fruit Set', 'name_si' => '10 වන සතිය: මල් පිපීම සහ ගෙඩි හටගැනීම', 'name_ta' => 'வாரம் 10: பூக்கும் மற்றும் காய் பிடிக்கும் நிலை', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Consistent watering is critical to prevent blossom-end rot.', 'advice_si' => 'ජලය නියතව සැපයීම අත්‍යවශ්‍ය වේ.', 'advice_ta' => 'சீரான நீர்ப்பாசனம் மிகவும் அவசியம்.', 'description' => 'Do not allow soil to dry out completely. Monitor for early blight symptoms (brown spots on leaves).', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 12: Fruit Development', 'name_si' => '12 වන සතිය: ගෙඩි වර්ධනය', 'name_ta' => 'வாரம் 12: காய் வளர்ச்சி', 'days_offset' => 60, 'icon' => 'target', 'advice' => 'Protect from heavy rain splashes. Monitor fruit for caterpillar damage.', 'advice_si' => 'වර්ෂාවෙන් ආරක්ෂා කරන්න. ගෙඩි කන පණුවා ගැන සැලකිලිමත් වන්න.', 'advice_ta' => 'கடும் மழையிலிருந்து பாதுகாக்கவும். புழுக்களைக் கண்காணிக்கவும்.', 'description' => 'Use Neem-based sprays for organic pest management. Ensure good fruit-to-ground clearance.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Week 14+: Harvesting Phase', 'name_si' => '14 වන සතිය+: අස්වනු නෙලීම', 'name_ta' => 'வாரம் 14+: அறுவடை நிலை', 'days_offset' => 80, 'icon' => 'shopping-basket', 'advice' => 'Pick at breaker (pink) stage for transport. Pick fully ripe for local sale.', 'advice_si' => 'අවශ්‍යතාවය අනුව නෙලන්න.', 'advice_ta' => 'தேவைக்கேற்ப அறுவடை செய்யவும்.', 'description' => 'Continue picking every 3 days. Avoid picking when wet to prevent post-harvest rot.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedChili($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -28, 'icon' => 'thermometer', 'advice' => 'Protect from heavy rain.', 'advice_si' => 'අධික වර්ෂාවෙන් ආරක්ෂා කරන්න.', 'advice_ta' => 'பலத்த மழையிலிருந்து பாதுகாக்கவும்.', 'description' => 'Ensure good drainage in nursery beds. Keep seedlings under partial shade during the first 2 weeks.', 'urea_per_acre_kg' => 5, 'tsp_per_acre_kg' => 10, 'mop_per_acre_kg' => 5],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use 60x45cm spacing.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය භාවිතා කරන්න.', 'advice_ta' => '60x45 செமீ இடைவெளி விடவும்.', 'description' => 'Avoid waterlogging at all costs. Add well-composted organic matter to the planting holes.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Flowering Dressing', 'name_si' => 'මල් පිපෙන අවධියේ පොහොර', 'name_ta' => 'பூக்கும் கால உரம்', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Apply MOP for fruit set.', 'advice_si' => 'ගෙඩි හටගැනීම සඳහා MOP පොහොර යොදන්න.', 'advice_ta' => 'காய் பிடிப்பதற்கு MOP உரமிடவும்.', 'description' => 'Watch for leaf curl virus and thrips. Use yellow sticky traps for monitoring.', 'urea_per_acre_kg' => 30, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 20],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 75, 'icon' => 'shopping-basket', 'advice' => 'Harvest green or red.', 'advice_si' => 'අමු හෝ ඉදුණු මිරිස් නෙලන්න.', 'advice_ta' => 'பச்சை அல்லது பழுத்த மிளகாயை அறுவடை செய்யவும்.', 'description' => 'Pick at regular intervals to stimulate further flowering.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedOkra($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Soak seeds before sowing.', 'advice_si' => 'වැපිරීමට පෙර බීජ පොඟවන්න.', 'advice_ta' => 'விதைப்பதற்கு முன் விதைகளை ஊறவைக்கவும்.', 'description' => 'Basal: 25kg Urea, 50kg TSP, 25kg MOP per acre. Mix with organic manure.', 'urea_per_acre_kg' => 25, 'tsp_per_acre_kg' => 50, 'mop_per_acre_kg' => 25],
            ['name' => 'Flowering', 'name_si' => 'මල් පිපීම', 'name_ta' => 'பூக்கும் நிலை', 'days_offset' => 35, 'icon' => 'flower', 'advice' => 'Apply Urea top dressing.', 'advice_si' => 'යූරියා මතුපිට පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மேலுரமிடவும்.', 'description' => 'Apply 30kg Urea per acre. Ensure the soil has enough moisture during flowering.', 'urea_per_acre_kg' => 30, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'First Harvest', 'name_si' => 'පළමු අස්වැන්න', 'name_ta' => 'முதல் அறுவடை', 'days_offset' => 45, 'icon' => 'shopping-basket', 'advice' => 'Harvest tender pods daily.', 'advice_si' => 'ලාබාල කරල් දිනපතා නෙලන්න.', 'advice_ta' => 'இளம் காய்களை தினமும் பறிக்கவும்.', 'description' => 'Fibrous pods are unmarketable. Harvest every morning.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedKurakkan($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Apply basal fertilizer.', 'advice_si' => 'මූලික පොහොර යොදන්න.', 'advice_ta' => 'அடிப்படை உரமிடவும்.', 'description' => 'Mix seeds with sand for uniform distribution.', 'urea_per_acre_kg' => 15, 'tsp_per_acre_kg' => 30, 'mop_per_acre_kg' => 15],
            ['name' => 'Heading', 'name_si' => 'කරල් පීදීම', 'name_ta' => 'கதிர் விடும் நிலை', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Apply Urea top dressing.', 'advice_si' => 'යූරියා මතුපිට පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மேலுரமிடவும்.', 'description' => 'Critical for grain filling. Ensure the crop is free of weeds at this stage.', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Dry heads in sun.', 'advice_si' => 'අස්වැන්න අව්වේ වියළන්න.', 'advice_ta' => 'கதிர்களை வெயிலில் காயவைக்கவும்.', 'description' => 'Store at low moisture in a cool dry place.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }

    private function seedDefault($v, $days) {
        $this->createStages($v, [
            ['name' => 'Preparation', 'name_si' => 'සූදානම් කිරීම', 'name_ta' => 'தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'General preparation.', 'advice_si' => 'පොදු සූදානම් කිරීම.', 'advice_ta' => 'பொதுவான தயாரிப்பு.', 'urea_per_acre_kg' => 10, 'tsp_per_acre_kg' => 20, 'mop_per_acre_kg' => 10],
            ['name' => 'Growth', 'name_si' => 'වර්ධනය', 'name_ta' => 'வளர்ச்சி', 'days_offset' => round($days * 0.4), 'icon' => 'trending-up', 'advice' => 'Monitor growth.', 'advice_si' => 'වර්ධනය නිරීක්ෂණය කරන්න.', 'advice_ta' => 'வளர்ச்சியைக் கவனிக்கவும்.', 'urea_per_acre_kg' => 20, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 10],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுවடை', 'days_offset' => $days, 'icon' => 'shopping-basket', 'advice' => 'Final harvest.', 'advice_si' => 'අවසාන අස්වනු නෙලීම.', 'advice_ta' => 'இறுதி அறுவடை.', 'urea_per_acre_kg' => 0, 'tsp_per_acre_kg' => 0, 'mop_per_acre_kg' => 0]
        ]);
    }
}
