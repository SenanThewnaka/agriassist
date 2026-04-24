<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->timestamp('ai_last_refreshed_at')->nullable()->after('base_market_price_per_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->dropColumn('ai_last_refreshed_at');
        });
    }
};
