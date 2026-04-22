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
        Schema::table('listings', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description');
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('status')->default('active')->after('price'); // active, sold, archived
            $table->string('unit')->nullable()->after('quantity'); // kg, g, tons, units, bunches, etc.
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('listing_id')->nullable()->after('receiver_id')->constrained('listings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['images', 'latitude', 'longitude', 'status', 'unit']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('listing_id');
        });
    }
};
