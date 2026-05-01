<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::create('farmer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('farm_size')->nullable();
            $table->string('farming_type')->nullable(); // e.g., organic, conventional
            $table->string('irrigation_type')->nullable();
            $table->integer('experience_years')->default(0);
            $table->text('main_crops')->nullable();
            $table->timestamps();
        });

        Schema::create('merchant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('store_name')->nullable();
            $table->string('store_logo')->nullable();
            $table->text('description')->nullable();
            $table->string('store_location')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('delivery_available')->default(false);
            $table->timestamps();
        });

        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('farm_name');
            $table->string('soil_type')->nullable();
            $table->string('farm_size')->nullable();
            $table->string('irrigation_source')->nullable();
            $table->decimal('elevation', 8, 2)->nullable();
            $table->string('district')->nullable();
            $table->timestamps();
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::dropIfExists('farms');
        Schema::dropIfExists('merchant_profiles');
        Schema::dropIfExists('farmer_profiles');
    }
};
