<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Updating existing status to include more granular tracking
            $table->string('order_status')->default('pending')->change(); 
            // statuses: pending, accepted, rejected, completed, cancelled
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status')->nullable()->change();
        });
    }
};
