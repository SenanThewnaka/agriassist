<?php

namespace App\Console\Commands;

use App\Models\CropVariety;
use App\Jobs\GenerateCropPlanJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RefreshAiRoadmaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agri:refresh-roadmaps {--force : Refresh all roadmaps regardless of date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periodically refresh existing crop roadmaps using AI to keep them detailed and up-to-date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Identifying roadmaps for refresh...');

        $query = CropVariety::with('crop');

        if (!$this->option('force')) {
            // Find varieties that haven't been refreshed in 30 days 
            // OR were never AI-refreshed (like standard seeded ones)
            $query->where(function($q) {
                $q->whereNull('ai_last_refreshed_at')
                  ->orWhere('ai_last_refreshed_at', '<', now()->subDays(30));
            });
        }

        $varieties = $query->get();

        if ($varieties->isEmpty()) {
            $this->info('All roadmaps are up to date.');
            return;
        }

        $this->info("Queuing refresh for {$varieties->count()} varieties...");

        foreach ($varieties as $variety) {
            $this->line("- Refreshing: {$variety->crop->name} ({$variety->variety_name})");
            
            // Dispatch the generation job
            // This will use the improved prompt and update the ai_last_refreshed_at
            GenerateCropPlanJob::dispatch(
                $variety->crop->name,
                $variety->soil_types[0] ?? 'Alluvial Soils',
                'en',
                0, // System user
                (string) Str::uuid(),
                $variety->variety_name
            );
        }

        $this->info('All refresh jobs have been queued.');
    }
}
