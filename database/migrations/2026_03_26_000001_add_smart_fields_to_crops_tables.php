<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->json('ideal_months')->nullable()->after('description');
            $table->enum('climate_zone', ['wet', 'dry', 'intermediate', 'all'])->default('all')->after('ideal_months');
        });

        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->json('soil_types')->nullable()->after('notes');
            $table->integer('min_temp')->nullable()->after('soil_types');
            $table->integer('max_temp')->nullable()->after('min_temp');
            $table->integer('min_rainfall')->nullable()->after('max_temp');
            $table->enum('water_requirement', ['low', 'medium', 'high'])->default('medium')->after('min_rainfall');
        });
    }

    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropColumn(['ideal_months', 'climate_zone']);
        });

        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->dropColumn(['soil_types', 'min_temp', 'max_temp', 'min_rainfall', 'water_requirement']);
        });
    }
};