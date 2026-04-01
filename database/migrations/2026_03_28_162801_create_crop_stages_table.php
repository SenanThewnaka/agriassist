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
        Schema::create('crop_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_variety_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_si')->nullable();
            $table->string('name_ta')->nullable();
            $table->integer('days_offset');
            $table->string('icon')->nullable();
            $table->text('advice');
            $table->text('advice_si')->nullable();
            $table->text('advice_ta')->nullable();
            $table->text('description')->nullable();
            $table->text('description_si')->nullable();
            $table->text('description_ta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_stages');
    }
};