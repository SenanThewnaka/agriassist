<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->string('name_si')->nullable()->after('name');
            $table->string('name_ta')->nullable()->after('name_si');
            $table->text('description_si')->nullable()->after('description');
            $table->text('description_ta')->nullable()->after('description_si');
        });

        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->string('variety_name_si')->nullable()->after('variety_name');
            $table->string('variety_name_ta')->nullable()->after('variety_name_si');
            $table->text('notes_si')->nullable()->after('notes');
            $table->text('notes_ta')->nullable()->after('notes_si');
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropColumn(['name_si', 'name_ta', 'description_si', 'description_ta']);
        });

        Schema::table('crop_varieties', function (Blueprint $table) {
            $table->dropColumn(['variety_name_si', 'variety_name_ta', 'notes_si', 'notes_ta']);
        });
    }
};