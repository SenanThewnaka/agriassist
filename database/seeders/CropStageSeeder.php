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
                case 'papaya': $this->seedPapaya($variety, $meta['days']); break;
                case 'kurakkan': $this->seedKurakkan($variety, $meta['days']); break;
                case 'sesame': $this->seedSesame($variety, $meta['days']); break;
                case 'brinjal': $this->seedBrinjal($variety, $meta['days']); break;
                case 'bitter_gourd': $this->seedBitterGourd($variety, $meta['days']); break;
                case 'beans': $this->seedBeans($variety, $meta['days']); break;
                case 'cabbage': $this->seedCabbage($variety, $meta['days']); break;
                case 'pumpkin': $this->seedPumpkin($variety, $meta['days']); break;
                case 'carrot': $this->seedCarrot($variety, $meta['days']); break;
                case 'leek': $this->seedLeek($variety, $meta['days']); break;
                case 'cowpea': $this->seedCowpea($variety, $meta['days']); break;
                case 'groundnut': $this->seedGroundnut($variety, $meta['days']); break;
                case 'soybean': $this->seedSoybean($variety, $meta['days']); break;
                case 'banana': $this->seedBanana($variety, $meta['days']); break;
                case 'watermelon': $this->seedWatermelon($variety, $meta['days']); break;
                case 'pineapple': $this->seedPineapple($variety, $meta['days']); break;
                case 'coconut': $this->seedCoconut($variety, $meta['days']); break;
                case 'cinnamon': $this->seedCinnamon($variety, $meta['days']); break;
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
            ['name' => 'Paddy Field Preparation', 'name_si' => 'කුඹුරු සකස් කිරීම', 'name_ta' => 'வயல் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply basal fertilizer (V-mixture). Clear weeds.', 'advice_si' => 'මූලික පොහොර (V-මිශ්‍රණය) යොදන්න. වල් පැලෑටි ඉවත් කරන්න.', 'advice_ta' => 'அடிப்படை உரம் (V-கலவை) இடவும். களைகளை அகற்றவும்.', 'description' => 'Basal: 50kg Urea, 100kg TSP, 50kg MOP per hectare.'],
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Sow pre-germinated seeds.', 'advice_si' => 'පැළ වූ බීජ වපුරන්න.', 'advice_ta' => 'முளைக்கட்டிய விதைகளை விதைக்கவும்.', 'description' => 'Maintain saturated soil but no standing water for first 3 days.'],
            ['name' => 'First Top Dressing', 'name_si' => 'පළමු මතුපිට පොහොර', 'name_ta' => 'முதல் மேலுரம்', 'days_offset' => 14, 'icon' => 'droplets', 'advice' => 'Apply Urea. Maintain 3-5cm water.', 'advice_si' => 'යූරියා යොදන්න. සෙ.මී. 3-5 ක ජල මට්ටමක් පවත්වා ගන්න.', 'advice_ta' => 'யூரியா இடவும். 3-5 செமீ நீர் மட்டத்தை பராமரிக்கவும்.', 'description' => 'Promotes tillering and early growth.'],
            ['name' => 'Heading Phase', 'name_si' => 'කරල් පීදීම', 'name_ta' => 'கதிர் விடும் நிலை', 'days_offset' => round($days * 0.75), 'icon' => 'flower', 'advice' => 'Apply final MOP dose. Critical water stage.', 'advice_si' => 'අවසාන MOP මාත්‍රාව යොදන්න. ජලය ඉතා අත්‍යවශ්‍ය අවධියකි.', 'advice_ta' => 'இறுதி MOP அளவை இடவும். முக்கியமான நீர் தேவைப்படும் நிலை.', 'description' => 'Do not allow the field to dry during grain filling.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Drain field 10 days before harvest.', 'advice_si' => 'අස්වනු නෙලීමට දින 10 කට පෙර ජලය ඉවත් කරන්න.', 'advice_ta' => 'அறுவடைக்கு 10 நாட்களுக்கு முன்பு தண்ணீரை வடிக்கவும்.', 'description' => 'Target 14% moisture for storage.']
        ]);
    }

    private function seedMaize($v, $days) {
        $this->createStages($v, [
            ['name' => 'Land Preparation', 'name_si' => 'බිම් සකස් කිරීම', 'name_ta' => 'நிலம் தயாரித்தல்', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Apply organic manure and basal fertilizer.', 'advice_si' => 'කාබනික පොහොර සහ මූලික පොහොර යොදන්න.', 'advice_ta' => 'கரிம உரம் மற்றும் அடிப்படை உரம் இடவும்.', 'description' => 'Deep ploughing ensures better root penetration.'],
            ['name' => 'Sowing', 'name_si' => 'වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x25cm. Plant 5cm deep.', 'advice_si' => 'සෙ.මී. 60x25 පරතරය තබා ගන්න. සෙ.මී. 5 ක් ගැඹුරින් සිටුවන්න.', 'advice_ta' => '60x25 செமீ இடைவெளி விடவும். 5 செமீ ஆழத்தில் நடவும்.', 'description' => 'Optimal moisture is critical for uniform germination.'],
            ['name' => 'Top Dressing', 'name_si' => 'මතුපිට පොහොර යෙදීම', 'name_ta' => 'மேலுரமிடுதல்', 'days_offset' => 30, 'icon' => 'droplets', 'advice' => 'Apply Urea and perform earthing up.', 'advice_si' => 'යූරියා යොදා පස් දමන්න.', 'advice_ta' => 'யூரியா இட்டு பாத்திகளை அணைக்கவும்.', 'description' => 'Watch for Fall Armyworm (Sena) infestation.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when husks are brown.', 'advice_si' => 'කොපු දුඹුරු පැහැ වූ විට අස්වනු නෙලන්න.', 'advice_ta' => 'தோகைகள் பழுப்பு நிறமானதும் அறுவடை செய்யவும்.', 'description' => 'Grains should be hard and dry.']
        ]);
    }

    private function seedTomato($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -21, 'icon' => 'thermometer', 'advice' => 'Use sterilized soil mix.', 'advice_si' => 'විෂබීජහරණය කළ පස් මිශ්‍රණයක් භාවිතා කරන්න.', 'advice_ta' => 'சுத்திகரிக்கப்பட்ட மண் கலவையைப் பயன்படுத்தவும்.', 'description' => 'Protect seedlings from high heat.'],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 60x45cm. Irrigate immediately.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය භාවිතා කරන්න. වහාම ජලය සපයන්න.', 'advice_ta' => '60x45 செமீ இடைவெளி விடவும். உடனே நீர்ப்பாசனம் செய்யவும்.', 'description' => 'Deep transplanting helps root stability.'],
            ['name' => 'Staking & Pruning', 'name_si' => 'ආධාරක සැපයීම සහ කප්පාදු කිරීම', 'name_ta' => 'முட்டுக்கொடுத்தல் மற்றும் கத்தரித்தல்', 'days_offset' => 21, 'icon' => 'trending-up', 'advice' => 'Support vines with stakes.', 'advice_si' => 'වැල් වලට ආධාරක සපයන්න.', 'advice_ta' => 'கொடிகளுக்கு முட்டுக்கொடுக்கவும்.', 'description' => 'Apply first top dressing of Urea/MOP.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுවடை', 'days_offset' => 65, 'icon' => 'shopping-basket', 'advice' => 'Pick at breaker (pink) stage.', 'advice_si' => 'රෝස පැහැයට හැරෙන විට නෙලන්න.', 'advice_ta' => 'இளஞ்சிவப்பு நிறமாகும் போது பறிக்கவும்.', 'description' => 'Continue picking every 3 days.']
        ]);
    }

    private function seedChili($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -28, 'icon' => 'thermometer', 'advice' => 'Protect from heavy rain.', 'advice_si' => 'අධික වර්ෂාවෙන් ආරක්ෂා කරන්න.', 'advice_ta' => 'பலத்த மழையிலிருந்து பாதுகாக்கவும்.', 'description' => 'Ensure good drainage in nursery beds.'],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use 60x45cm spacing.', 'advice_si' => 'සෙ.මී. 60x45 පරතරය භාවිතා කරන්න.', 'advice_ta' => '60x45 செமீ இடைவெளி விடவும்.', 'description' => 'Avoid waterlogging at all costs.'],
            ['name' => 'Flowering', 'name_si' => 'මල් පිපීම', 'name_ta' => 'பூக்கும் நிலை', 'days_offset' => 45, 'icon' => 'flower-2', 'advice' => 'Apply MOP for fruit set.', 'advice_si' => 'ගෙඩි හටගැනීම සඳහා MOP පොහොර යොදන්න.', 'advice_ta' => 'காய் பிடிப்பதற்கு MOP உரமிடவும்.', 'description' => 'Watch for leaf curl virus (thrips/mites).'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 75, 'icon' => 'shopping-basket', 'advice' => 'Harvest green or red.', 'advice_si' => 'අමු හෝ ඉදුණු මිරිස් නෙලන්න.', 'advice_ta' => 'பச்சை அல்லது பழுத்த மிளகாயை அறுவடை செய்யவும்.', 'description' => 'Expected yield: 10-15 tons/ha (fresh).']
        ]);
    }

    private function seedOkra($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Soak seeds before sowing.', 'advice_si' => 'වැපිරීමට පෙර බීජ පොඟවන්න.', 'advice_ta' => 'விதைப்பதற்கு முன் விதைகளை ஊறவைக்கவும்.', 'description' => 'Space at 60x30cm. 2 seeds per pit.'],
            ['name' => 'Flowering', 'name_si' => 'මල් පිපීම', 'name_ta' => 'பூக்கும் நிலை', 'days_offset' => 35, 'icon' => 'flower', 'advice' => 'High water demand.', 'advice_si' => 'වැඩි ජල ප්‍රමාණයක් අවශ්‍ය වේ.', 'advice_ta' => 'அதிக நீர் தேவை.', 'description' => 'Apply Urea top dressing.'],
            ['name' => 'First Harvest', 'name_si' => 'පළමු අස්වැන්න', 'name_ta' => 'முதல் அறுவடை', 'days_offset' => 45, 'icon' => 'shopping-basket', 'advice' => 'Harvest tender pods daily.', 'advice_si' => 'ලාබාල කරල් දිනපතා නෙලන්න.', 'advice_ta' => 'இளம் காய்களை தினமும் பறிக்கவும்.', 'description' => 'Fibrous pods are unmarketable.']
        ]);
    }

    private function seedKurakkan($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Broadcast or drill sow.', 'advice_si' => 'බීජ ඉසීම හෝ පේළි වල වැපිරීම සිදු කරන්න.', 'advice_ta' => 'விதைக்க அல்லது தூவவும்.', 'description' => 'Mix seeds with fine sand for uniform broadcasting.'],
            ['name' => 'Thinning', 'name_si' => 'පැළ තුනී කිරීම', 'name_ta' => 'நாற்றுக்களை கலைத்தல்', 'days_offset' => 14, 'icon' => 'scissors', 'advice' => 'Maintain 10cm between plants.', 'advice_si' => 'පැළ අතර සෙ.මී. 10 ක පරතරයක් තබන්න.', 'advice_ta' => 'செடிகளுக்கு இடையில் 10 செமீ இடைவெளி விடவும்.', 'description' => 'Ensures better grain head development.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when ear-heads turn brown.', 'advice_si' => 'කරල් දුඹුරු පැහැ වූ විට නෙලන්න.', 'advice_ta' => 'கதிர்கள் பழுப்பு நிறமானதும் அறுவடை செய்யவும்.', 'description' => 'Dry thoroughly before threshing.']
        ]);
    }

    private function seedBrinjal($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery Stage', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால் நிலை', 'days_offset' => -30, 'icon' => 'thermometer', 'advice' => 'Maintain clean nursery.', 'advice_si' => 'තවාන පිරිසිදුව තබා ගන්න.', 'advice_ta' => 'நாற்றங்காலை சுத்தமாக பராமரிக்கவும்.', 'description' => 'Seedlings ready in 4-5 weeks.'],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 90x60cm.', 'advice_si' => 'සෙ.මී. 90x60 පරතරය භාවිතා කරන්න.', 'advice_ta' => '90x60 செமீ இடைவெளி விடவும்.', 'description' => 'Heavy feeders; apply organic compost.'],
            ['name' => 'Fruiting', 'name_si' => 'ගෙඩි හටගැනීම', 'name_ta' => 'காய் பிடிக்கும் நிலை', 'days_offset' => 60, 'icon' => 'apple', 'advice' => 'Watch for Fruit/Shoot borer.', 'advice_si' => 'කඳ සහ ගෙඩි විදින පණුවා ගැන විමසිලිමත් වන්න.', 'advice_ta' => 'தண்டு/காய் துளைப்பானைக் கவனிக்கவும்.', 'description' => 'Regular irrigation prevents bitterness.']
        ]);
    }

    private function seedBeans($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Plant 2 seeds per hole.', 'advice_si' => 'එක් වළක බීජ 2 බැගින් සිටුවන්න.', 'advice_ta' => 'ஒரு குழிக்கு 2 விதைகள் வீதம் நடவும்.', 'description' => 'Space at 45x15cm for bush beans.'],
            ['name' => 'Flowering', 'name_si' => 'මල් පිපීම', 'name_ta' => 'பூக்கும் நிலை', 'days_offset' => 35, 'icon' => 'flower-2', 'advice' => 'Apply Urea top dressing.', 'advice_si' => 'යූරියා මතුපිට පොහොර යොදන්න.', 'advice_ta' => 'யூரியா மேலுரமிடவும்.', 'description' => 'Keep soil consistently moist but not wet.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 55, 'icon' => 'shopping-basket', 'advice' => 'Pick before seeds bulge.', 'advice_si' => 'බීජ නෙරා ඒමට පෙර කරල් නෙලන්න.', 'advice_ta' => 'விதைகள் பருக்கத் தொடங்கும் முன் பறிக்கவும்.', 'description' => 'Regular harvesting extends production.']
        ]);
    }

    private function seedCabbage($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால்', 'days_offset' => -25, 'icon' => 'thermometer', 'advice' => 'Protect from heavy rain.', 'advice_si' => 'අධික වර්ෂාවෙන් ආරක්ෂා කරන්න.', 'advice_ta' => 'பலத்த மழையிலிருந்து பாதுகாக்கவும்.', 'description' => 'Seedlings ready in 3-4 weeks.'],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 40x40cm.', 'advice_si' => 'සෙ.මී. 40x40 පරතරය භාවිතා කරන්න.', 'advice_ta' => '40x40 செமீ இடைவெளி விடவும்.', 'description' => 'Use high organic matter in soil.'],
            ['name' => 'Head Formation', 'name_si' => 'ගෝවා ගෙඩි සැකසීම', 'name_ta' => 'தலை உருவாகும் நிலை', 'days_offset' => 45, 'icon' => 'circle', 'advice' => 'Maintain consistent water.', 'advice_si' => 'ස්ථාවර ජල සැපයුමක් පවත්වා ගන්න.', 'advice_ta' => 'சீரான நீர் வழங்கலைப் பராமரிக்கவும்.', 'description' => 'Watch for diamondback moth caterpillars.']
        ]);
    }

    private function seedCarrot($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Direct sow in ridges.', 'advice_si' => 'වැටිවල ඍජුවම වපුරන්න.', 'advice_ta' => 'பாத்திகளில் நேரடியாக விதைக்கவும்.', 'description' => 'Mix seeds with sand for uniform sowing.'],
            ['name' => 'Thinning', 'name_si' => 'පැළ තුනී කිරීම', 'name_ta' => 'நாற்றுக்களை கலைத்தல்', 'days_offset' => 21, 'icon' => 'scissors', 'advice' => 'Space at 5-10cm.', 'advice_si' => 'සෙ.මී. 5-10 පරතරයක් තබන්න.', 'advice_ta' => '5-10 செமீ இடைவெளி விடவும்.', 'description' => 'Essential for root expansion.'],
            ['name' => 'Root Expansion', 'name_si' => 'අල වර්ධනය', 'name_ta' => 'கிழங்கு வளர்ச்சி', 'days_offset' => 60, 'icon' => 'trending-up', 'advice' => 'Apply Potassium (MOP).', 'advice_si' => 'පොටෑසියම් (MOP) යොදන්න.', 'advice_ta' => 'பொட்டாசியம் (MOP) இடவும்.', 'description' => 'Keep ridges moist but not soggy.']
        ]);
    }

    private function seedBanana($v, $days) {
        $this->createStages($v, [
            ['name' => 'Pit Preparation', 'name_si' => 'වළවල් සකස් කිරීම', 'name_ta' => 'குழி தயாரித்தல்', 'days_offset' => -14, 'icon' => 'hole', 'advice' => 'Dig 2x2x2ft pits.', 'advice_si' => 'අඩි 2x2x2 වළවල් හාරන්න.', 'advice_ta' => '2x2x2 அடி குழிகளைத் தோண்டவும்.', 'description' => 'Fill with manure and topsoil.'],
            ['name' => 'Planting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use healthy suckers.', 'advice_si' => 'නිරෝගී කෙසෙල් මොර සිටුවන්න.', 'advice_ta' => 'ஆரோக்கியமான கன்றுகளை நடவும்.', 'description' => 'Irrigate immediately after planting.'],
            ['name' => 'Fruiting', 'name_si' => 'කෙසෙල් මුව හටගැනීම', 'name_ta' => 'பழம் வரும் நிலை', 'days_offset' => 210, 'icon' => 'apple', 'advice' => 'Support tree with props.', 'advice_si' => 'ගසට ආධාරක සපයන්න.', 'advice_ta' => 'மரத்திற்கு முட்டுக்கொடுக்கவும்.', 'description' => 'Apply balanced fertilizer every 3 months.']
        ]);
    }

    private function seedPapaya($v, $days) {
        $this->createStages($v, [
            ['name' => 'Pit Preparation', 'name_si' => 'වළවල් සකස් කිරීම', 'name_ta' => 'குழி தயாரித்தல்', 'days_offset' => -14, 'icon' => 'hole', 'advice' => 'Ensure good drainage.', 'advice_si' => 'හොඳ ජලාපවහනයක් ඇති බව තහවුරු කරගන්න.', 'advice_ta' => 'நல்ல வடிகால் வசதியை உறுதி செய்யவும்.', 'description' => 'Dig 2x2x2ft pits.'],
            ['name' => 'Planting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use 3 seedlings per pit.', 'advice_si' => 'වළකට පැළ 3 බැගින් සිටුවන්න.', 'advice_ta' => 'ஒரு குழிக்கு 3 நாற்றுகள் வீதம் நடவும்.', 'description' => 'Thin out later based on gender.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 240, 'icon' => 'shopping-basket', 'advice' => 'Harvest when yellow streaks appear.', 'advice_si' => 'කහ පැහැ ඉරි මතුවන විට නෙලන්න.', 'advice_ta' => 'மஞ்சள் கோடுகள் தோன்றும் போது அறுவடை செய்யவும்.', 'description' => 'Handle carefully to avoid bruising.']
        ]);
    }

    private function seedCoconut($v, $days) {
        $this->createStages($v, [
            ['name' => 'Pit Preparation', 'name_si' => 'වළවල් සකස් කිරීම', 'name_ta' => 'குழி தயாரித்தல்', 'days_offset' => -30, 'icon' => 'hole', 'advice' => 'Dig 3x3x3ft pits.', 'advice_si' => 'අඩි 3x3x3 වළවල් හාරන්න.', 'advice_ta' => '3x3x3 அடி குழிகளைத் தோண்டவும்.', 'description' => 'Apply coconut fertilizer mixture.'],
            ['name' => 'Planting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Plant 1-year old seedlings.', 'advice_si' => 'වසරක් පැරණි පැළ සිටුවන්න.', 'advice_ta' => 'ஒரு வருட வயதுடைய நாற்றுகளை நடவும்.', 'description' => 'Orient seedlings north-south.'],
            ['name' => 'First Nut', 'name_si' => 'පළමු පලදාව', 'name_ta' => 'முதல் காய்', 'days_offset' => 1825, 'icon' => 'circle', 'advice' => 'Ensure base is mulched.', 'advice_si' => 'ගස් පාමුල වසුන් කරන්න.', 'advice_ta' => 'மரத்தின் அடியில் மூடாக்கு இடவும்.', 'description' => 'Harvest mature nuts monthly.']
        ]);
    }

    private function seedCinnamon($v, $days) {
        $this->createStages($v, [
            ['name' => 'Planting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 1.2x0.6m.', 'advice_si' => 'සෙ.මී. 1.2x0.6 පරතරය තබා ගන්න.', 'advice_ta' => '1.2x0.6 மீ இடைவெளி விடவும்.', 'description' => 'Plant in high-density blocks.'],
            ['name' => 'First Training', 'name_si' => 'පළමු කප්පාදුව', 'name_ta' => 'முதல் பயிற்சி', 'days_offset' => 365, 'icon' => 'scissors', 'advice' => 'Cut the main stem to promote branching.', 'advice_si' => 'අතු බෙදීම දිරිමත් කිරීමට ප්‍රධාන කඳ කපන්න.', 'advice_ta' => 'கிளைகளை ஊக்குவிக்க முக்கிய தண்டைக் கத்தரிக்கவும்.', 'description' => 'Encourages lateral growth.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 730, 'icon' => 'axe', 'advice' => 'Peel bark when sap flows.', 'advice_si' => 'පොතු ගලවන්න.', 'advice_ta' => 'மரப்பட்டையை உரிக்கவும்.', 'description' => 'Ensure high humidity for easy peeling.']
        ]);
    }

    private function seedSesame($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Direct sow in dry soil.', 'advice_si' => 'වියළි පසෙහි ඍජුවම වපුරන්න.', 'advice_ta' => 'உலர்ந்த மண்ணில் நேரடியாக விதைக்கவும்.', 'description' => 'Space at 30x10cm.'],
            ['name' => 'Thinning', 'name_si' => 'පැළ තුනී කිරීම', 'name_ta' => 'நாற்றுக்களை கலைத்தல்', 'days_offset' => 14, 'icon' => 'scissors', 'advice' => 'Thin to 1 plant per spot.', 'advice_si' => 'ස්ථානයකට එක් පැළයක් බැගින් තුනී කරන්න.', 'advice_ta' => 'ஒரு இடத்திற்கு ஒரு செடியாக கலைக்கவும்.', 'description' => 'Prevents competition for light.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when lower pods yellow.', 'advice_si' => 'පහළ කරල් කහ පැහැ වූ විට නෙලන්න.', 'advice_ta' => 'கீழ் காய்கள் மஞ்சளானதும் அறுவடை செய்யவும்.', 'description' => 'Dry bundles vertically.']
        ]);
    }

    private function seedCowpea($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 30x15cm.', 'advice_si' => 'සෙ.මී. 30x15 පරතරය තබා ගන්න.', 'advice_ta' => '30x15 செமீ இடைவெளி விடவும்.', 'description' => 'Plant 2 seeds per hole.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 60, 'icon' => 'shopping-basket', 'advice' => 'Harvest when pods are dry.', 'advice_si' => 'කරල් වියළි වූ විට නෙලන්න.', 'advice_ta' => 'காய்கள் காய்ந்ததும் அறுவடை செய்யவும்.', 'description' => 'Continue picking until final maturity.']
        ]);
    }

    private function seedGroundnut($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Sow in loose, sandy soil.', 'advice_si' => 'බුරුල් වැලි සහිත පසෙහි වපුරන්න.', 'advice_ta' => 'தளர்வான மணல் மண்ணில் விதைக்கவும்.', 'description' => 'Space at 45x15cm.'],
            ['name' => 'Earthing Up', 'name_si' => 'පස් දැමීම', 'name_ta' => 'மண் அணைத்தல்', 'days_offset' => 35, 'icon' => 'trending-up', 'advice' => 'Earth up to cover pegging flowers.', 'advice_si' => 'මල් වසා පස් දමන්න.', 'advice_ta' => 'மண் அணைக்கவும்.', 'description' => 'Essential for pod formation.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Uproot when shell veins dark.', 'advice_si' => 'ලෙලි වල නහර තද පැහැ වූ විට ගලවන්න.', 'advice_ta' => 'தோட்டின் நரம்புகள் கருமையானதும் பிடுங்கவும்.', 'description' => 'Dry pods in sun for 3 days.']
        ]);
    }

    private function seedWatermelon($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Sow in raised beds.', 'advice_si' => 'උස් පාත්තිවල වපුරන්න.', 'advice_ta' => 'உயர்த்தப்பட்ட பாத்திகளில் விதைக்கவும்.', 'description' => 'Space at 1.5x1.0m.'],
            ['name' => 'Fruiting', 'name_si' => 'ගෙඩි හටගැනීම', 'name_ta' => 'காய் பிடிக்கும் நிலை', 'days_offset' => 45, 'icon' => 'apple', 'advice' => 'Reduce water slowly.', 'advice_si' => 'ජලය සපයන ප්‍රමාණය ක්‍රමයෙන් අඩු කරන්න.', 'advice_ta' => 'தண்ணீரைக் மெதுவாகக் குறைக்கவும்.', 'description' => 'Prevents fruit cracking.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 75, 'icon' => 'shopping-basket', 'advice' => 'Check for dull thud sound.', 'advice_si' => 'තට්ටු කර ශබ්දය පරීක්ෂා කරන්න.', 'advice_ta' => 'தட்டும் ஓசையைச் சரிபார்க்கவும்.', 'description' => 'Tendril near fruit should be dry.']
        ]);
    }

    private function seedPineapple($v, $days) {
        $this->createStages($v, [
            ['name' => 'Planting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Use healthy suckers/slips.', 'advice_si' => 'නිරෝගී පැළ සිටුවන්න.', 'advice_ta' => 'ஆரோக்கியமான கன்றுகளை நடவும்.', 'description' => 'Space at 90x60cm.'],
            ['name' => 'Forcing', 'name_si' => 'මල් හටගැනීම උත්තේජනය', 'name_ta' => 'பூக்கத் தூண்டுதல்', 'days_offset' => 365, 'icon' => 'zap', 'advice' => 'Apply calcium carbide for uniform flowering.', 'advice_si' => 'මල් හටගැනීම උත්තේජනය කිරීමට කැල්සියම් කාබයිඩ් යොදන්න.', 'advice_ta' => 'பூக்கத் தூண்டுவதற்கு கால்சியம் கார்பைடு இடவும்.', 'description' => 'Ensures synchronized harvesting.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 540, 'icon' => 'shopping-basket', 'advice' => 'Harvest when 25% yellow.', 'advice_si' => 'ගෙඩිය 25% ක් කහ පැහැ වූ විට නෙලන්න.', 'advice_ta' => '25% மஞ்சளானதும் அறுவடை செய்யவும்.', 'description' => 'Cut with a short stalk.']
        ]);
    }

    private function seedBitterGourd($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Direct sow 2 seeds per pit.', 'advice_si' => 'වළකට බීජ 2 බැගින් ඍජුවම වපුරන්න.', 'advice_ta' => 'நேரடி விதைப்பு குழிக்கு 2 விதைகள்.', 'description' => 'Space pits at 1.5x1.0m.'],
            ['name' => 'Trellising', 'name_si' => 'පන්දලම් ගැසීම', 'name_ta' => 'பந்தல் அமைத்தல்', 'days_offset' => 21, 'icon' => 'grid', 'advice' => 'Support vines with trellis.', 'advice_si' => 'වැල් වලට පන්දලම් ආධාරක සපයන්න.', 'advice_ta' => 'பந்தல் அமைத்து கொடிகளுக்கு ஆதரவளிக்கவும்.', 'description' => 'Improves fruit quality and shape.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 60, 'icon' => 'shopping-basket', 'advice' => 'Harvest when full size but green.', 'advice_si' => 'නියමිත ප්‍රමාණයට වැඩුණු පසු කොළ පැහැයෙන් තිබියදීම නෙලන්න.', 'advice_ta' => 'முழு அளவில் இருக்கும் போது பச்சை நிறத்திலேயே அறுவடை செய்யவும்.', 'description' => 'Overripe fruits turn orange and soft.']
        ]);
    }

    private function seedPumpkin($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Space at 2x2m.', 'advice_si' => 'මීටර් 2x2 පරතරය තබා ගන්න.', 'advice_ta' => '2x2 மீ இடைவெளி விடவும்.', 'description' => 'Add heavy organic manure to pits.'],
            ['name' => 'Vine Management', 'name_si' => 'වැල් කළමනාකරණය', 'name_ta' => 'கொடி மேலாண்மை', 'days_offset' => 30, 'icon' => 'trending-up', 'advice' => 'Allow space for spreading.', 'advice_si' => 'වැල් පැතිරීමට ඉඩ ලබා දෙන්න.', 'advice_ta' => 'கொடி படர இடமளிக்கவும்.', 'description' => 'Keep base well mulched.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => 90, 'icon' => 'shopping-basket', 'advice' => 'Harvest when stem dries.', 'advice_si' => 'නැට්ට වියළී ගිය පසු අස්වනු නෙලන්න.', 'advice_ta' => 'காம்பு காய்ந்ததும் அறுவடை செய்யவும்.', 'description' => 'Shell should be hard to fingernail.']
        ]);
    }

    private function seedLeek($v, $days) {
        $this->createStages($v, [
            ['name' => 'Nursery', 'name_si' => 'තවාන් කළමනාකරණය', 'name_ta' => 'நாற்றங்கால்', 'days_offset' => -45, 'icon' => 'thermometer', 'advice' => 'Long nursery period needed.', 'advice_si' => 'දිගු කාලීන තවාන් අවධියක් අවශ්‍ය වේ.', 'advice_ta' => 'நீண்ட நாற்றங்கால் காலம் தேவை.', 'description' => 'Seedlings ready at pencil thickness.'],
            ['name' => 'Transplanting', 'name_si' => 'පැළ සිටුවීම', 'name_ta' => 'நாற்று நடுதல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Plant in deep trenches.', 'advice_si' => 'ගැඹුරු අගල් වල සිටුවන්න.', 'advice_ta' => 'ஆழமான பள்ளங்களில் நடவும்.', 'description' => 'Space at 15x10cm.'],
            ['name' => 'Earthing Up', 'name_si' => 'පස් දැමීම', 'name_ta' => 'மண் அணைத்தல்', 'days_offset' => 60, 'icon' => 'trending-up', 'advice' => 'Fill trenches to blanch stems.', 'advice_si' => 'කඳ සුදු කිරීම සඳහා අගල් වලට පස් පුරවන්න.', 'advice_ta' => 'தண்டுகளை வெண்மையாக்க பள்ளங்களில் மண் அணைக்கவும்.', 'description' => 'Produces long, white edible stems.']
        ]);
    }

    private function seedSoybean($v, $days) {
        $this->createStages($v, [
            ['name' => 'Sowing', 'name_si' => 'බීජ වැපිරීම', 'name_ta' => 'விதைத்தல்', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Inoculate seeds with Rhizobium.', 'advice_si' => 'බීජ සඳහා රයිසෝබියම් ප්‍රතිකාර කරන්න.', 'advice_ta' => 'விதை நேர்த்தி செய்யவும்.', 'description' => 'Space at 40x10cm.'],
            ['name' => 'Pod Filling', 'name_si' => 'කරල් පිරීම', 'name_ta' => 'காய் முதிர்ச்சி', 'days_offset' => 60, 'icon' => 'circle', 'advice' => 'Critical moisture stage.', 'advice_si' => 'ජලය ඉතා අත්‍යවශ්‍ය අවධියකි.', 'advice_ta' => 'முக்கியமான நீர் தேவைப்படும் நிலை.', 'description' => 'Apply Urea top dressing if needed.'],
            ['name' => 'Harvest', 'name_si' => 'අස්වනු නෙලීම', 'name_ta' => 'அறுவடை', 'days_offset' => $days, 'icon' => 'shopping-bag', 'advice' => 'Harvest when 90% leaves fall.', 'advice_si' => '90% ක් පත්‍ර හැලුණු පසු අස්වනු නෙලන්න.', 'advice_ta' => '90% இலைகள் உதிர்ந்ததும் அறுவடை செய்யவும்.', 'description' => 'Pods should rattle when shaken.']
        ]);
    }

    private function seedLeekStages($v, $days) { $this->seedLeek($v, $days); }
    private function seedSesameStages($v, $days) { $this->seedSesame($v, $days); }
}
