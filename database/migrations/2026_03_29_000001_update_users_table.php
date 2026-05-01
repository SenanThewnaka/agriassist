<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('name');
            $table->enum('role', ['farmer', 'buyer', 'seller'])->default('farmer')->after('full_name');
            $table->enum('preferred_language', ['en', 'si', 'ta'])->default('en')->after('role');
            $table->string('phone_number')->nullable()->after('preferred_language');
            $table->string('profile_photo')->nullable()->after('phone_number');
            $table->string('district')->nullable()->after('profile_photo');
            $table->text('bio')->nullable()->after('district');
        });
    }

    // Reverse the migrations.
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'role', 'preferred_language', 'phone_number', 'profile_photo', 'district', 'bio']);
        });
    }
};
