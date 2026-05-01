<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::create('disease_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('set null');
            $table->string('crop_name');
            $table->string('disease_name');
            $table->float('confidence_score');
            $table->text('treatment_recommendation')->nullable();
            $table->string('image_path')->nullable();
            $table->string('ai_model_used')->nullable();
            $table->timestamps();
        });

        Schema::create('weather_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->float('temperature');
            $table->float('humidity');
            $table->float('rainfall')->nullable();
            $table->float('wind_speed')->nullable();
            $table->string('weather_condition')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('weather_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('alert_type');
            $table->text('message');
            $table->string('severity'); // low, medium, high
            $table->timestamp('alert_date');
            $table->timestamps();
        });

        Schema::create('pest_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('pest_name');
            $table->string('risk_level'); // low, medium, high
            $table->text('message');
            $table->text('recommended_action')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('set null');
            $table->string('insight_type'); // yield_prediction, soil_health, etc.
            $table->text('insight_text');
            $table->timestamps();
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
        Schema::dropIfExists('pest_alerts');
        Schema::dropIfExists('weather_alerts');
        Schema::dropIfExists('weather_records');
        Schema::dropIfExists('disease_detections');
    }
};
