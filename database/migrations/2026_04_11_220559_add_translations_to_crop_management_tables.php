<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::table('crop_seasons', function (Blueprint $table) {
            $table->string('crop_name_si')->nullable()->after('crop_name');
            $table->string('crop_name_ta')->nullable()->after('crop_name_si');
            $table->string('crop_variety_si')->nullable()->after('crop_variety');
            $table->string('crop_variety_ta')->nullable()->after('crop_variety_si');
            $table->text('notes_si')->nullable()->after('notes');
            $table->text('notes_ta')->nullable()->after('notes_si');
        });

        Schema::table('crop_tasks', function (Blueprint $table) {
            $table->string('task_name_si')->nullable()->after('task_name');
            $table->string('task_name_ta')->nullable()->after('task_name_si');
            $table->text('description_si')->nullable()->after('description');
            $table->text('description_ta')->nullable()->after('description_si');
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::table('crop_seasons', function (Blueprint $table) {
            $table->dropColumn(['crop_name_si', 'crop_name_ta', 'crop_variety_si', 'crop_variety_ta', 'notes_si', 'notes_ta']);
        });

        Schema::table('crop_tasks', function (Blueprint $table) {
            $table->dropColumn(['task_name_si', 'task_name_ta', 'description_si', 'description_ta']);
        });
    }
};
