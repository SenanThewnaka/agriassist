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
            $table->decimal('yield_per_acre_kg', 10, 2)->nullable()->after('water_requirement');
            $table->decimal('seed_per_acre_kg', 10, 2)->nullable()->after('yield_per_acre_kg');
            $table->decimal('base_market_price_per_kg', 10, 2)->nullable()->after('seed_per_acre_kg');
        });

        Schema::table('crop_stages', function (Blueprint $table) {
            $table->decimal('urea_per_acre_kg', 8, 2)->default(0)->after('description_ta');
            $table->decimal('tsp_per_acre_kg', 8, 2)->default(0)->after('urea_per_acre_kg');
            $table->decimal('mop_per_acre_kg', 8, 2)->default(0)->after('tsp_per_acre_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->dropColumn(['yield_per_acre_kg', 'seed_per_acre_kg', 'base_market_price_per_kg']);
        });

        Schema::table('crop_stages', function (Blueprint $table) {
            $table->dropColumn(['urea_per_acre_kg', 'tsp_per_acre_kg', 'mop_per_acre_kg']);
        });
    }
};
