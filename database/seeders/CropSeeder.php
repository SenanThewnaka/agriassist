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
            'name_si' => 'වී',
            'name_ta' => 'நெல்',
            'category' => 'grain',
            'description' => 'Staple food crop of Sri Lanka, grown in Maha and Yala seasons in paddy fields.',
            'description_si' => 'ශ්‍රී ලංකාවේ ප්‍රධාන ආහාර බෝගය වන මෙය මහ සහ යල කන්නවලදී කුඹුරුවල වගා කෙරේ.',
            'description_ta' => 'இலங்கையின் முக்கிய உணவுப் பயிர், இது மகா மற்றும் யால பருவங்களில் வயல்களில் விளைவிக்கப்படுகிறது.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'all',
        ]);

        foreach ([
            ['Bg 300', 'බී.ජී. 300', 'பி.ஜி. 300', 90, 'both', ['Alluvial', 'Clay Loam', 'Sandy Loam'], 22, 34, 150, 'high'],
            ['Bg 352', 'බී.ජී. 352', 'பி.ஜி. 352', 105, 'both', ['Alluvial', 'Clay', 'Clay Loam'], 22, 34, 150, 'high'],
            ['Bg 94-1', 'බී.ජී. 94-1', 'பி.ஜி. 94-1', 120, 'maha', ['Alluvial', 'Clay', 'Lateritic'], 22, 32, 180, 'high'],
            ['At 307', 'ඒ.ටී. 307', 'ஏ.டி. 307', 105, 'both', ['Alluvial', 'Sandy Loam', 'Clay Loam'], 24, 35, 140, 'high'],
            ['Samba Mahee', 'සම්බා මහී', 'சம்பா மாஹி', 135, 'maha', ['Alluvial', 'Clay'], 20, 30, 200, 'high'],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create([
                'crop_id' => $rice->id,
                'variety_name' => $vname,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => json_encode($soils),
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water
            ]);
        }

        $maize = Crop::create([
            'name' => 'Maize',
            'name_si' => 'බඩඉරිඟු',
            'name_ta' => 'சோளம்',
            'category' => 'grain',
            'description' => 'Widely grown as food and feed crop in dry and intermediate zones.',
            'description_si' => 'වියළි හා අතරමැදි කලාපවල ආහාර සහ සත්ව ආහාර බෝගයක් ලෙස බහුලව වගා කෙරේ.',
            'description_ta' => 'உலர்ந்த மற்றும் இடைநிலை மண்டலங்களில் உணவு மற்றும் தீவனப் பயிராக பரவலாக வளர்க்கப்படுகிறது.',
            'ideal_months' => json_encode([4, 5, 9, 10, 11]),
            'climate_zone' => 'dry',
        ]);
        foreach ([
            ['Ruwan', 'රුවන්', 'ருவான்', 105, 'both', ['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic'], 18, 35, 60, 'medium'],
            ['DMRSL-01', 'ඩී.එම්.ආර්.එස්.එල්. 01', 'டி.எம்.ஆர்.எஸ்.எல். 01', 100, 'both', ['Sandy Loam', 'Lateritic'], 20, 35, 60, 'medium'],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create([
                'crop_id' => $maize->id,
                'variety_name' => $vname,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => json_encode($soils),
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water
            ]);
        }

        $kurakkan = Crop::create([
            'name' => 'Kurakkan (Finger Millet)',
            'name_si' => 'කුරක්කන්',
            'name_ta' => 'குறக்கான் (கேழ்வரகு)',
            'category' => 'grain',
            'description' => 'Drought-tolerant traditional grain, ideal for dry zone small-holders.',
            'description_si' => 'නියඟයට ඔරොත්තු දෙන සාම්ප්‍රදායික ධාන්‍ය වර්ගයක් වන මෙය වියළි කලාපීය සුළු ගොවීන්ට ඉතා යෝග්‍ය වේ.',
            'description_ta' => 'வறட்சியைத் தாங்கும் பாரம்பரிய தானியம், உலர்ந்த மண்டல சிறு விவசாயிகளுக்கு ஏற்றது.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $kurakkan->id,
            'variety_name' => 'Rawana',
            'variety_name_si' => 'රාවණා',
            'variety_name_ta' => 'ராவணா',
            'growth_days' => 90,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Lateritic', 'Sandy']),
            'min_temp' => 18,
            'max_temp' => 38,
            'min_rainfall' => 40,
            'water_requirement' => 'low'
        ]);

        $sesame = Crop::create([
            'name' => 'Sesame (Thal)',
            'name_si' => 'තල',
            'name_ta' => 'எள்',
            'category' => 'grain',
            'description' => 'Oil seed crop well suited to dry zone conditions.',
            'description_si' => 'වියළි කලාපීය තත්ත්වයන්ට හොඳින් ගැලපෙන තෙල් බීජ බෝගයකි.',
            'description_ta' => 'உலர்ந்த மண்டல நிலைமைகளுக்கு மிகவும் பொருத்தமான எண்ணெய் வித்து பயிர்.',
            'ideal_months' => json_encode([3, 4, 8, 9]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $sesame->id,
            'variety_name' => 'MI 4',
            'variety_name_si' => 'එම්.අයි. 4',
            'variety_name_ta' => 'எம்.ஐ. 4',
            'growth_days' => 85,
            'season' => 'yala',
            'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 25,
            'max_temp' => 38,
            'min_rainfall' => 40,
            'water_requirement' => 'low'
        ]);

        // =========================================================
        // VEGETABLES
        // =========================================================

        $tomato = Crop::create([
            'name' => 'Tomato',
            'name_si' => 'තක්කාලි',
            'name_ta' => 'தக்காளி',
            'category' => 'vegetable',
            'description' => 'High-demand vegetable for fresh consumption and processing.',
            'description_si' => 'නැවුම් පරිභෝජනය සහ සැකසීම සඳහා ඉහළ ඉල්ලුමක් ඇති එළවළුවකි.',
            'description_ta' => 'புதிய நுகர்வு மற்றும் பதப்படுத்துதலுக்கு அதிக தேவையுள்ள காயறி.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'intermediate',
        ]);
        foreach ([
            ['Thilina', 'තිළිණ', 'திலினா', 90, 'yala', ['Sandy Loam', 'Red Yellow Podzolic', 'Lateritic'], 15, 30, 60, 'medium'],
            ['Lanka Cherry', 'ලංකා චෙරි', 'லங்கா செர்ரி', 75, 'both', ['Sandy Loam', 'Alluvial'], 18, 32, 60, 'medium'],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create([
                'crop_id' => $tomato->id,
                'variety_name' => $vname,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => json_encode($soils),
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water
            ]);
        }

        $chili = Crop::create([
            'name' => 'Chili',
            'name_si' => 'මිරිස්',
            'name_ta' => 'மிளகாய்',
            'category' => 'vegetable',
            'description' => 'Essential spice crop for Sri Lankan cuisine, exported widely.',
            'description_si' => 'ශ්‍රී ලාංකික ආහාර සඳහා අත්‍යවශ්‍ය කුළුබඩු බෝගයක් වන අතර එය බහුලව අපනයනය කෙරේ.',
            'description_ta' => 'இலங்கை உணவு வகைகளுக்கு அவசியமான மசாலா பயிர், பரவலாக ஏற்றுமதி செய்யப்படுகிறது.',
            'ideal_months' => json_encode([9, 10, 11, 12]),
            'climate_zone' => 'dry',
        ]);
        foreach ([
            ['MI 2', 'එම්.අයි. 2', 'எம்.ஐ. 2', 120, 'maha', ['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic'], 18, 35, 60, 'medium'],
            ['Kuliyapitiya Local', 'කුලියාපිටිය දේශීය', 'குளியாப்பிட்டிய உள்ளூர்', 135, 'maha', ['Sandy Loam', 'Alluvial'], 18, 32, 80, 'medium'],
        ] as [$vname, $vname_si, $vname_ta, $days, $season, $soils, $minT, $maxT, $minR, $water]) {
            CropVariety::create([
                'crop_id' => $chili->id,
                'variety_name' => $vname,
                'variety_name_si' => $vname_si,
                'variety_name_ta' => $vname_ta,
                'growth_days' => $days,
                'season' => $season,
                'soil_types' => json_encode($soils),
                'min_temp' => $minT,
                'max_temp' => $maxT,
                'min_rainfall' => $minR,
                'water_requirement' => $water
            ]);
        }

        $brinjal = Crop::create([
            'name' => 'Brinjal (Eggplant)',
            'name_si' => 'වම්බටු',
            'name_ta' => 'கத்தரிக்காய்',
            'category' => 'vegetable',
            'description' => 'Versatile vegetable widely grown across all climate zones.',
            'description_si' => 'සියලුම දේශගුණික කලාප පුරා බහුලව වගා කරන බහුවිධ එළවළුවකි.',
            'description_ta' => 'அனைத்து காலநிலை மண்டலங்களிலும் பரவலாக வளர்க்கப்படும் ஒரு காய்கறி.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 9, 10, 11, 12]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create([
            'crop_id' => $brinjal->id,
            'variety_name' => 'Padagoda',
            'variety_name_si' => 'පාදගොඩ',
            'variety_name_ta' => 'படாகொட',
            'growth_days' => 120,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Clay Loam', 'Lateritic']),
            'min_temp' => 22,
            'max_temp' => 35,
            'min_rainfall' => 50,
            'water_requirement' => 'medium'
        ]);

        $okra = Crop::create([
            'name' => 'Okra (Ladies\' Fingers)',
            'name_si' => 'බණ්ඩක්කා',
            'name_ta' => 'வெண்டைக்காய்',
            'category' => 'vegetable',
            'description' => 'Fast-growing vegetable suited to warm humid conditions.',
            'description_si' => 'උණුසුම් තෙතමනය සහිත තත්ත්වයන්ට ගැලපෙන වේගයෙන් වර්ධනය වන එළවළුවකි.',
            'description_ta' => 'வெப்பமான ஈரப்பதமான நிலைமைகளுக்கு ஏற்ற வேகமாக வளரும் காய்கறி.',
            'ideal_months' => json_encode([3, 4, 5, 6, 9, 10]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create([
            'crop_id' => $okra->id,
            'variety_name' => 'MI Super',
            'variety_name_si' => 'එම්.අයි. සුපර්',
            'variety_name_ta' => 'எம்.ஐ. சூப்பர்',
            'growth_days' => 60,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Lateritic']),
            'min_temp' => 22,
            'max_temp' => 38,
            'min_rainfall' => 50,
            'water_requirement' => 'medium'
        ]);

        $bitterGourd = Crop::create([
            'name' => 'Bitter Gourd',
            'name_si' => 'කරවිල',
            'name_ta' => 'பாகற்காய்',
            'category' => 'vegetable',
            'description' => 'Popular gourd vegetable, grows well in warm and humid areas.',
            'description_si' => 'ජනප්‍රිය වැල් බෝගයක් වන මෙය උණුසුම් හා තෙතමනය සහිත ප්‍රදේශවල හොඳින් වර්ධනය වේ.',
            'description_ta' => 'பிரபலமான காய்கறி, வெப்பமான மற்றும் ஈரப்பதமான பகுதிகளில் நன்றாக வளரும்.',
            'ideal_months' => json_encode([2, 3, 4, 5, 6, 7, 8, 9]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create([
            'crop_id' => $bitterGourd->id,
            'variety_name' => 'MC 43',
            'variety_name_si' => 'එම්.සී. 43',
            'variety_name_ta' => 'எம்.சி. 43',
            'growth_days' => 75,
            'season' => 'yala',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Red Yellow Podzolic']),
            'min_temp' => 24,
            'max_temp' => 36,
            'min_rainfall' => 80,
            'water_requirement' => 'medium'
        ]);

        $beans = Crop::create([
            'name' => 'Beans (Bush Beans)',
            'name_si' => 'බෝංචි',
            'name_ta' => 'பீன்ஸ்',
            'category' => 'vegetable',
            'description' => 'Quick maturing legume-vegetable grown in upcountry areas.',
            'description_si' => 'උඩරට ප්‍රදේශවල වගා කරන ඉක්මනින් පීදෙන රනිල කුලයේ එළවළුවකි.',
            'description_ta' => 'மலைப்பாங்கான பகுதிகளில் வளர்க்கப்படும் விரைவாக முதிர்ச்சியடையும் காய்கறி.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12, 1]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create([
            'crop_id' => $beans->id,
            'variety_name' => 'Wade',
            'variety_name_si' => 'වේඩ්',
            'variety_name_ta' => 'வேட்',
            'growth_days' => 65,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Red Yellow Podzolic', 'Lateritic']),
            'min_temp' => 15,
            'max_temp' => 28,
            'min_rainfall' => 60,
            'water_requirement' => 'medium'
        ]);

        $cabbage = Crop::create([
            'name' => 'Cabbage',
            'name_si' => 'ගෝවා',
            'name_ta' => 'கோவா',
            'category' => 'vegetable',
            'description' => 'Cool-weather crop mainly grown in Nuwara Eliya highlands.',
            'description_si' => 'ප්‍රධාන වශයෙන් නුවරඑළිය උඩරට ප්‍රදේශවල වගා කරන සිසිල් දේශගුණික බෝගයකි.',
            'description_ta' => 'குளிர் காலநிலைப் பயிர், முக்கியமாக நுவரெலியா மலைப்பகுதிகளில் வளர்க்கப்படுகிறது.',
            'ideal_months' => json_encode([6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create([
            'crop_id' => $cabbage->id,
            'variety_name' => 'KY Cross',
            'variety_name_si' => 'කේ.වයි. ක්‍රොස්',
            'variety_name_ta' => 'கே.ஒய். கிராஸ்',
            'growth_days' => 90,
            'season' => 'both',
            'soil_types' => json_encode(['Red Yellow Podzolic', 'Sandy Loam']),
            'min_temp' => 10,
            'max_temp' => 24,
            'min_rainfall' => 80,
            'water_requirement' => 'medium'
        ]);

        $pumpkin = Crop::create([
            'name' => 'Pumpkin',
            'name_si' => 'වට්ටක්කා',
            'name_ta' => 'பூசணிக்காய்',
            'category' => 'vegetable',
            'description' => 'Hardy vine vegetable tolerant of dry conditions.',
            'description_si' => 'වියළි තත්ත්වයන්ට ඔරොත්තු දෙන ශක්තිමත් වැල් බෝගයකි.',
            'description_ta' => 'வறண்ட நிலைகளைத் தாங்கும் ஒரு கொடி காய்கறி.',
            'ideal_months' => json_encode([3, 4, 5, 8, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $pumpkin->id,
            'variety_name' => 'Local Red',
            'variety_name_si' => 'දේශීය රතු',
            'variety_name_ta' => 'உள்ளூர் சிவப்பு',
            'growth_days' => 90,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic', 'Alluvial']),
            'min_temp' => 22,
            'max_temp' => 38,
            'min_rainfall' => 40,
            'water_requirement' => 'low'
        ]);

        $carrot = Crop::create([
            'name' => 'Carrot',
            'name_si' => 'කැරට්',
            'name_ta' => 'கேரட்',
            'category' => 'vegetable',
            'description' => 'Root vegetable best grown in cool upcountry regions.',
            'description_si' => 'සිසිල් උඩරට ප්‍රදේශවල හොඳින්ම වැඩෙන අල බෝගයකි.',
            'description_ta' => 'குளிர்ச்சியான மலைப்பகுதிகளில் சிறப்பாக வளரும் ஒரு கிழங்கு வகை காய்கறி.',
            'ideal_months' => json_encode([7, 8, 9, 10, 11, 12, 1]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create([
            'crop_id' => $carrot->id,
            'variety_name' => 'Nantes',
            'variety_name_si' => 'නැන්ටේස්',
            'variety_name_ta' => 'நாண்டஸ்',
            'growth_days' => 100,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Red Yellow Podzolic']),
            'min_temp' => 12,
            'max_temp' => 25,
            'min_rainfall' => 60,
            'water_requirement' => 'medium'
        ]);

        $leek = Crop::create([
            'name' => 'Leek',
            'name_si' => 'ලීක්ස්',
            'name_ta' => 'லீக்ஸ்',
            'category' => 'vegetable',
            'description' => 'Grown in the upcountry area, mostly Nuwara Eliya region.',
            'description_si' => 'ප්‍රධාන වශයෙන් නුවරඑළිය උඩරට ප්‍රදේශවල වගා කෙරේ.',
            'description_ta' => 'மலைப்பாங்கான பகுதிகளில், முக்கியமாக நுவரெலியா பகுதியில் வளர்க்கப்படுகிறது.',
            'ideal_months' => json_encode([7, 8, 9, 10, 11]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create([
            'crop_id' => $leek->id,
            'variety_name' => 'Lanka White',
            'variety_name_si' => 'ලංකා සුදු',
            'variety_name_ta' => 'லங்கா வெள்ளை',
            'growth_days' => 120,
            'season' => 'both',
            'soil_types' => json_encode(['Red Yellow Podzolic', 'Sandy Loam']),
            'min_temp' => 10,
            'max_temp' => 22,
            'min_rainfall' => 80,
            'water_requirement' => 'medium'
        ]);

        // =========================================================
        // LEGUMES
        // =========================================================

        $cowpea = Crop::create([
            'name' => 'Cowpea (Mung)',
            'name_si' => 'කව්පි',
            'name_ta' => 'கௌபி',
            'category' => 'vegetable',
            'description' => 'Short season legume suited to dry zone inter-cropping.',
            'description_si' => 'වියළි කලාපයේ අතුරු බෝග වගාවට සුදුසු කෙටි කාලීන රනිල බෝගයකි.',
            'description_ta' => 'உலர்ந்த மண்டல ஊடுபயிர்க்கு ஏற்ற குறுகிய கால பருப்பு வகை பயிர்.',
            'ideal_months' => json_encode([3, 4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $cowpea->id,
            'variety_name' => 'Waruni',
            'variety_name_si' => 'වාරුණි',
            'variety_name_ta' => 'வாருணி',
            'growth_days' => 70,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 20,
            'max_temp' => 38,
            'min_rainfall' => 40,
            'water_requirement' => 'low'
        ]);

        $groundnut = Crop::create([
            'name' => 'Groundnut (Peanut)',
            'name_si' => 'රටකජු',
            'name_ta' => 'நிலக்கடலை',
            'category' => 'grain',
            'description' => 'Oil and protein rich crop, major in dry zone.',
            'description_si' => 'තෙල් සහ ප්‍රෝටීන් බහුල බෝගයක් වන අතර වියළි කලාපයේ ප්‍රධාන වශයෙන් වගා කෙරේ.',
            'description_ta' => 'எண்ணெய் மற்றும் புரதம் நிறைந்த பயிர், உலர்ந்த மண்டலத்தில் முக்கியமானது.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $groundnut->id,
            'variety_name' => 'Tikiri',
            'variety_name_si' => 'ටිකිරි',
            'variety_name_ta' => 'திகிரி',
            'growth_days' => 110,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 22,
            'max_temp' => 36,
            'min_rainfall' => 60,
            'water_requirement' => 'low'
        ]);

        $soybean = Crop::create([
            'name' => 'Soybean',
            'name_si' => 'සෝයා බෝංචි',
            'name_ta' => 'சோயா அவரை',
            'category' => 'grain',
            'description' => 'High-protein cash legume adaptable across zones.',
            'description_si' => 'විවිධ කලාපවලට අනුවර්තනය විය හැකි ඉහළ ප්‍රෝටීන් සහිත රනිල බෝගයකි.',
            'description_ta' => 'அனைத்து மண்டலங்களுக்கும் ஏற்ற அதிக புரதமுள்ள பருப்பு வகை பயிர்.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'intermediate',
        ]);
        CropVariety::create([
            'crop_id' => $soybean->id,
            'variety_name' => 'PB 1',
            'variety_name_si' => 'පී.බී. 1',
            'variety_name_ta' => 'பி.பி. 1',
            'growth_days' => 95,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Clay Loam']),
            'min_temp' => 20,
            'max_temp' => 32,
            'min_rainfall' => 60,
            'water_requirement' => 'medium'
        ]);

        // =========================================================
        // FRUITS
        // =========================================================

        $banana = Crop::create([
            'name' => 'Banana',
            'name_si' => 'කෙසෙල්',
            'name_ta' => 'வாழை',
            'category' => 'fruit',
            'description' => 'Most widely grown fruit crop in Sri Lanka, year-round cultivation.',
            'description_si' => 'ශ්‍රී ලංකාවේ බහුලවම වගා කරන පලතුරු බෝගය වන මෙය වසර පුරා වගා කළ හැකිය.',
            'description_ta' => 'இலங்கையில் மிகவும் பரவலாக வளர்க்கப்படும் பழப் பயிர், ஆண்டு முழுவதும் சாகுபடி செய்யப்படுகிறது.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create([
            'crop_id' => $banana->id,
            'variety_name' => 'Embul (Sour Banana)',
            'variety_name_si' => 'ඇඹුල් කෙසෙල්',
            'variety_name_ta' => 'ஆனப்பாவாடை (Embul) வாழை',
            'growth_days' => 300,
            'season' => 'both',
            'soil_types' => json_encode(['Alluvial', 'Sandy Loam', 'Clay Loam']),
            'min_temp' => 24,
            'max_temp' => 38,
            'min_rainfall' => 100,
            'water_requirement' => 'high'
        ]);

        $papaya = Crop::create([
            'name' => 'Papaya',
            'name_si' => 'පැපොල්',
            'name_ta' => 'பப்பாளி',
            'category' => 'fruit',
            'description' => 'Fast-fruiting tropical fruit grown across all zones.',
            'description_si' => 'සියලුම කලාපවල වගා කරන වේගයෙන් පල දරන නිවර්තන පලතුරකි.',
            'description_ta' => 'அனைத்து மண்டலங்களிலும் வளர்க்கப்படும் வேகமாக காய்க்கும் வெப்பமண்டல பழம்.',
            'ideal_months' => json_encode([3, 4, 5, 9, 10]),
            'climate_zone' => 'all',
        ]);
        CropVariety::create([
            'crop_id' => $papaya->id,
            'variety_name' => 'Red Lady',
            'variety_name_si' => 'රතු ලේඩි',
            'variety_name_ta' => 'ரெட் லேடி',
            'growth_days' => 270,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Lateritic']),
            'min_temp' => 22,
            'max_temp' => 38,
            'min_rainfall' => 80,
            'water_requirement' => 'medium'
        ]);

        $watermelon = Crop::create([
            'name' => 'Watermelon',
            'name_si' => 'පැණි කොමඩු',
            'name_ta' => 'தர்பூசணி',
            'category' => 'fruit',
            'description' => 'High-value seasonal fruit crop best in dry zone sandy soils.',
            'description_si' => 'වියළි කලාපයේ වැලි සහිත පසෙහි හොඳින්ම වැවෙන ඉහළ වටිනාකමකින් යුතු සෘතුමය පලතුරු බෝගයකි.',
            'description_ta' => 'உலர்ந்த மண்டல மணல் பகுதிகளில் சிறப்பாக வளரும் அதிக மதிப்புள்ள பருவகால பழப் பயிர்.',
            'ideal_months' => json_encode([2, 3, 4, 5, 6]),
            'climate_zone' => 'dry',
        ]);
        CropVariety::create([
            'crop_id' => $watermelon->id,
            'variety_name' => 'Sugar Baby',
            'variety_name_si' => 'ෂුගර් බේබි',
            'variety_name_ta' => 'சுகர் பேபி',
            'growth_days' => 80,
            'season' => 'yala',
            'soil_types' => json_encode(['Sandy', 'Sandy Loam']),
            'min_temp' => 25,
            'max_temp' => 40,
            'min_rainfall' => 30,
            'water_requirement' => 'medium'
        ]);

        $pineapple = Crop::create([
            'name' => 'Pineapple',
            'name_si' => 'අන්නාසි',
            'name_ta' => 'அன்னாசி',
            'category' => 'fruit',
            'description' => 'Grown mainly in the wet and intermediate zones.',
            'description_si' => 'ප්‍රධාන වශයෙන් තෙත් සහ අතරමැදි කලාපවල වගා කෙරේ.',
            'description_ta' => 'முக்கியமாக ஈரமான மற்றும் இடைநிலை மண்டலங்களில் வளர்க்கப்படுகிறது.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create([
            'crop_id' => $pineapple->id,
            'variety_name' => 'Mauritius',
            'variety_name_si' => 'මොරිෂස්',
            'variety_name_ta' => 'மொரிஷியஸ்',
            'growth_days' => 540,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Lateritic', 'Red Yellow Podzolic']),
            'min_temp' => 22,
            'max_temp' => 35,
            'min_rainfall' => 100,
            'water_requirement' => 'medium'
        ]);

        // =========================================================
        // CASH CROPS
        // =========================================================

        $coconut = Crop::create([
            'name' => 'Coconut',
            'name_si' => 'පොල්',
            'name_ta' => 'தேங்காய்',
            'category' => 'fruit',
            'description' => 'Sri Lanka\'s national tree — major export and subsistence crop.',
            'description_si' => 'ශ්‍රී ලංකාවේ ජාතික වෘක්ෂය වන මෙය ප්‍රධාන අපනයන සහ දේශීය පරිභෝජන බෝගයකි.',
            'description_ta' => 'இலங்கையின் தேசிய மரம் - முக்கிய ஏற்றுமதி மற்றும் வாழ்வாதாரப் பயிர்.',
            'ideal_months' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create([
            'crop_id' => $coconut->id,
            'variety_name' => 'Sri Lanka Tall',
            'variety_name_si' => 'ලංකා උස පොල්',
            'variety_name_ta' => 'இலங்கை உயரமான தென்னை',
            'growth_days' => 2555,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Alluvial', 'Sandy', 'Lateritic']),
            'min_temp' => 20,
            'max_temp' => 38,
            'min_rainfall' => 100,
            'water_requirement' => 'medium'
        ]);

        $cinnamon = Crop::create([
            'name' => 'Cinnamon',
            'name_si' => 'කුරුඳු',
            'name_ta' => 'கருவா',
            'category' => 'grain',
            'description' => 'World-renowned Sri Lankan spice crop, true cinnamon.',
            'description_si' => 'ලෝක ප්‍රසිද්ධ ශ්‍රී ලංකා කුළුබඩු බෝගයකි.',
            'description_ta' => 'உலகப் புகழ்பெற்ற இலங்கை மசாலா பயிர், உண்மையான கருவா.',
            'ideal_months' => json_encode([4, 5, 9, 10]),
            'climate_zone' => 'wet',
        ]);
        CropVariety::create([
            'crop_id' => $cinnamon->id,
            'variety_name' => 'Sri Gemunu',
            'variety_name_si' => 'ශ්‍රී ගැමුණු',
            'variety_name_ta' => 'ஸ்ரீ கெமுனு',
            'growth_days' => 730,
            'season' => 'both',
            'soil_types' => json_encode(['Sandy Loam', 'Sandy', 'Lateritic']),
            'min_temp' => 22,
            'max_temp' => 35,
            'min_rainfall' => 120,
            'water_requirement' => 'medium'
        ]);
    }
}
