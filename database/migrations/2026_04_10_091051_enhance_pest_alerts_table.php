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
        Schema::table('pest_alerts', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->change();
            $table->string('district')->nullable()->after('farm_id');
            $table->string('crop_name')->nullable()->after('district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pest_alerts', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable(false)->change();
            $table->dropColumn(['district', 'crop_name']);
        });
    }
};
