<?php

namespace App\Console\Commands;

use App\Models\CropSeason;
use App\Models\PestAlert;
use App\Services\AnalysisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Command: ForecastPestSwarms
 * 
 * Scheduled task that proactively analyzes environmental conditions against 
 * active crop seasons to predict and alert farmers of imminent pest threats.
 */
class ForecastPestSwarms extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'agri:forecast-pests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Predicts pest swarm risks based on 14-day weather forecasts and active cultivation data.';

    public function __construct(
        private readonly AnalysisService $analysisService
    ) {
        parent::__construct();
    }

    /**
     * Executes the pest forecasting pipeline.
     * 
     * Iterates through all active crop seasons, retrieves localized weather data, 
     * and utilizes the intelligence engine to assess biological risk factors.
     * 
     * @return void
     */
    public function handle(): void
    {
        $this->info('Initializing biological risk assessment pipeline...');

        $activeSeasons = CropSeason::with('farm')
            ->where('expected_harvest_date', '>=', now()->toDateString())
            ->get();

        if ($activeSeasons->isEmpty()) {
            $this->info('No active cultivation cycles detected. Pipeline terminated.');
            return;
        }

        foreach ($activeSeasons as $season) {
            $farm = $season->farm;
            
            if (!$farm || !$farm->latitude || !$farm->longitude) {
                continue;
            }

            $this->info("Analyzing biological vectors for: {$farm->farm_name} [Subject: {$season->crop_name}]");

            try {
                $weatherData = $this->fetchWeather($farm->latitude, $farm->longitude);
                
                if (empty($weatherData)) {
                    $this->warn("Telemetry failure: Unable to retrieve meteorological data for Farm ID {$farm->id}.");
                    continue;
                }

                $predictions = $this->analysisService->predictPests(
                    $season->crop_name,
                    $weatherData,
                    $farm->district ?? 'Unknown'
                );

                foreach ($predictions as $risk) {
                    // Deduplication: Prevent alert fatigue by suppressing identical warnings within a 72-hour window.
                    $recentAlertExists = PestAlert::where('farm_id', $farm->id)
                        ->where('pest_name', $risk['pest_name'])
                        ->where('created_at', '>', now()->subDays(3))
                        ->exists();

                    if (!$recentAlertExists) {
                        PestAlert::create([
                            'farm_id'            => $farm->id,
                            'district'           => $farm->district,
                            'crop_name'          => $season->crop_name,
                            'pest_name'          => $risk['pest_name'],
                            'risk_level'         => $risk['risk_level'],
                            'message'            => $risk['message'],
                            'recommended_action' => $risk['recommended_action'],
                        ]);
                        
                        $this->info("Critical vector identified: {$risk['pest_name']} [Farm: {$farm->farm_name}]");
                    }
                }

            } catch (\Exception $e) {
                Log::error("Biological risk assessment failed [Season ID: {$season->id}]", [
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString()
                ]);
                $this->error("Pipeline exception for Season ID {$season->id}. Consult system logs.");
            }
        }

        $this->info('Biological risk assessment pipeline completed successfully.');
    }

    /**
     * Retrieves and formats 14-day meteorological telemetry for the specified coordinates.
     * 
     * @param float $lat
     * @param float $lon
     * @return array<int, array<string, mixed>>
     */
    private function fetchWeather(float $lat, float $lon): array
    {
        try {
            $endpoint = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&daily=temperature_2m_max,precipitation_sum,relative_humidity_2m_max&timezone=auto&forecast_days=14";
            $response = Http::timeout(10)->get($endpoint);

            if ($response->successful()) {
                $payload = $response->json();
                $telemetry = [];
                
                foreach ($payload['daily']['time'] as $index => $date) {
                    $telemetry[] = [
                        'date'     => $date,
                        'temp_max' => $payload['daily']['temperature_2m_max'][$index],
                        'rain_sum' => $payload['daily']['precipitation_sum'][$index],
                        'humidity' => $payload['daily']['relative_humidity_2m_max'][$index] ?? 70,
                    ];
                }
                
                return $telemetry;
            }
        } catch (\Exception $e) {
            Log::warning("Meteorological telemetry provider unreachable.", ['exception' => $e->getMessage()]);
        }
        
        return [];
    }
}
