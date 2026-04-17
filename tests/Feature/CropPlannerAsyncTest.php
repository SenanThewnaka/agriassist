<?php

namespace Tests\Feature;

use App\Jobs\GenerateCropPlanJob;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\User;
use App\Services\AnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use Mockery;

class CropPlannerAsyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test Case 1: Verify apiCalculate returns a jobId for unknown crops.
     */
    public function test_api_calculate_dispatches_job_for_unknown_crop()
    {
        Bus::fake();

        $response = $this->actingAs($this->user)->postJson('/api/crop-plan', [
            'custom_crop_name' => 'Dragon Fruit',
            'custom_variety_name' => 'Red Ruby',
            'planting_date' => '2026-05-01',
            'district' => 'Colombo'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'job_id', 'message'])
            ->assertJson(['status' => 'processing']);

        $jobId = $response->json('job_id');
        $this->assertNotNull($jobId);

        Bus::assertDispatched(GenerateCropPlanJob::class, function ($job) use ($jobId) {
            return $job->cropName === 'Dragon Fruit' && $job->jobId === $jobId;
        });
    }

    /**
     * Test Case 2: Verify two simultaneous calls for the same crop return the same jobId.
     */
    public function test_api_calculate_deduplicates_simultaneous_requests()
    {
        Bus::fake();

        // First call
        $response1 = $this->actingAs($this->user)->postJson('/api/crop-plan', [
            'custom_crop_name' => 'Dragon Fruit',
            'custom_variety_name' => 'Red Ruby',
            'planting_date' => '2026-05-01',
            'district' => 'Colombo'
        ]);

        $jobId1 = $response1->json('job_id');

        // Second call with same crop and district (which determines soil type)
        $response2 = $this->actingAs($this->user)->postJson('/api/crop-plan', [
            'custom_crop_name' => 'Dragon Fruit',
            'custom_variety_name' => 'Red Ruby',
            'planting_date' => '2026-05-01',
            'district' => 'Colombo'
        ]);

        $jobId2 = $response2->json('job_id');

        $this->assertEquals($jobId1, $jobId2);
        Bus::assertDispatchedTimes(GenerateCropPlanJob::class, 1);
    }

    /**
     * Test Case 3: Verify the Job correctly persists data and updates Cache status.
     */
    public function test_generate_crop_plan_job_persists_data_and_updates_status()
    {
        $jobId = (string)Str::uuid();
        $cropName = 'Dragon Fruit';
        $varietyName = 'Red Ruby';
        
        $mockAnalysisService = Mockery::mock(AnalysisService::class);
        $mockAnalysisService->shouldReceive('generateCropPlanWithRetries')
            ->once()
            ->with($cropName, 'en', $varietyName)
            ->andReturn([
                'crop' => $cropName,
                'crop_si' => 'ඩ්‍රැගන් ෆෘට්',
                'crop_ta' => 'டிராகன் பழம்',
                'variety' => $varietyName,
                'variety_si' => 'රතු රූබි',
                'variety_ta' => 'சிவப்பு ரூபி',
                'growth_days' => 120,
                'yield_per_acre_kg' => 8000,
                'seed_per_acre_kg' => 10,
                'base_market_price_per_kg' => 600,
                'suitable_soil_types' => ['Reddish Brown Earth'],
                'stages' => [
                    ['name' => 'Planting', 'days_from_start' => 0, 'advice' => 'Plant in well-drained soil', 'icon' => 'sprout', 'description' => 'Detailed planting instructions'],
                    ['name' => 'Flowering', 'days_from_start' => 60, 'advice' => 'Ensure adequate water', 'icon' => 'flower', 'description' => 'Detailed flowering instructions']
                ]
            ]);

        $job = new GenerateCropPlanJob($cropName, 'Reddish Brown Earth', 'en', $this->user->id, $jobId, $varietyName);
        $job->handle($mockAnalysisService);

        // Verify DB persistence
        $this->assertDatabaseHas('crops', ['name' => $cropName]);
        $this->assertDatabaseHas('crop_varieties', ['variety_name' => $varietyName]);
        $this->assertDatabaseHas('crop_stages', ['name' => 'Planting']);

        // Verify Cache status
        $statusKey = sprintf(AnalysisService::STATUS_KEY, $jobId);
        $status = Cache::get($statusKey);
        
        $this->assertEquals('completed', $status['status']);
        $this->assertNotNull($status['crop_id']);
        $this->assertNotNull($status['variety_id']);
    }

    /**
     * Test Case 4: Verify the status endpoint returns the correct status sequence.
     */
    public function test_api_check_status_returns_correct_data()
    {
        $jobId = (string)Str::uuid();
        $statusKey = sprintf(AnalysisService::STATUS_KEY, $jobId);

        // 1. Test 404 for non-existent job
        $response = $this->getJson("/api/planner/status/{$jobId}");
        $response->assertStatus(404);

        // 2. Test processing status
        Cache::put($statusKey, ['status' => 'processing'], now()->addMinutes(5));
        $response = $this->getJson("/api/planner/status/{$jobId}");
        $response->assertStatus(200)
            ->assertJson(['status' => 'processing']);

        // 3. Test completed status
        Cache::put($statusKey, [
            'status' => 'completed',
            'crop_id' => 1,
            'variety_id' => 1
        ], now()->addMinutes(5));
        
        $response = $this->getJson("/api/planner/status/{$jobId}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed',
                'crop_id' => 1,
                'variety_id' => 1
            ]);
    }

    /**
     * Test Case 5: Verify the Job handles existing variety (deduplication).
     */
    public function test_generate_crop_plan_job_handles_existing_variety()
    {
        $crop = Crop::create(['name' => 'Dragon Fruit', 'category' => 'vegetable', 'ideal_months' => [1]]);
        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'variety_name' => 'Red Ruby',
            'growth_days' => 120,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earth'],
            'yield_per_acre_kg' => 5000,
            'seed_per_acre_kg' => 2,
            'base_market_price_per_kg' => 150
        ]);

        $jobId = (string)Str::uuid();
        $mockAnalysisService = Mockery::mock(AnalysisService::class);
        $mockAnalysisService->shouldNotReceive('generateCropPlanWithRetries');

        $job = new GenerateCropPlanJob('Dragon Fruit', 'Reddish Brown Earth', 'en', $this->user->id, $jobId, 'Red Ruby');
        $job->handle($mockAnalysisService);

        // Verify Cache status
        $statusKey = sprintf(AnalysisService::STATUS_KEY, $jobId);
        $status = Cache::get($statusKey);
        
        $this->assertEquals('completed', $status['status']);
        $this->assertEquals($crop->id, $status['crop_id']);
        $this->assertEquals($variety->id, $status['variety_id']);
    }

    /**
     * Test Case 6: Verify apiCalculate returns variety data directly if it exists.
     */
    public function test_api_calculate_returns_data_directly_for_known_crop()
    {
        $crop = Crop::create(['name' => 'Dragon Fruit', 'category' => 'vegetable', 'ideal_months' => [1]]);
        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'variety_name' => 'Red Ruby',
            'growth_days' => 120,
            'season' => 'both',
            'soil_types' => ['Reddish Brown Earth'],
            'yield_per_acre_kg' => 5000,
            'seed_per_acre_kg' => 2,
            'base_market_price_per_kg' => 150
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/crop-plan', [
            'custom_crop_name' => 'Dragon Fruit',
            'custom_variety_name' => 'Red Ruby',
            'planting_date' => '2026-05-01',
            'district' => 'Colombo'
        ]);

        $response->assertStatus(200)
            ->assertJson(['crop' => 'Dragon Fruit', 'variety' => 'Red Ruby']);
        
        $this->assertArrayNotHasKey('job_id', $response->json());
    }
}
