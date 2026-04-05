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
        Schema::create('crop_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('crop_name');
            $table->string('crop_variety')->nullable();
            $table->date('planting_date')->nullable();
            $table->date('expected_harvest_date')->nullable();
            $table->string('crop_stage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('crop_season_id')->nullable()->constrained()->onDelete('set null');
            $table->string('activity_type'); // e.g., fertilization, irrigation, pest control
            $table->text('description')->nullable();
            $table->string('quantity')->nullable();
            $table->date('activity_date');
            $table->timestamps();
        });

        Schema::create('crop_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_season_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('stage', ['land_prep', 'sowing', 'vegetative', 'flowering', 'harvest']);
            $table->date('due_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_tasks');
        Schema::dropIfExists('farm_activities');
        Schema::dropIfExists('crop_seasons');
    }
};
