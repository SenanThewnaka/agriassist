<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FarmerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile()
    {
        $user = User::factory()->create([
            'role' => 'farmer',
            'full_name' => 'John Doe',
            'preferred_language' => 'en',
        ]);
        FarmerProfile::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patchJson('/profile', [
            'full_name' => 'Jane Doe',
            'phone_number' => '0712345678',
            'district' => 'Colombo',
            'bio' => 'A passionate farmer.',
            'preferred_language' => 'si',
            'farm_size' => '10 Acres',
            'experience_years' => 5,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Jane Doe',
            'phone_number' => '0712345678',
            'district' => 'Colombo',
            'preferred_language' => 'si',
        ]);

        $this->assertDatabaseHas('farmer_profiles', [
            'user_id' => $user->id,
            'farm_size' => '10 Acres',
            'experience_years' => 5,
        ]);
    }

    public function test_profile_update_validates_phone_number()
    {
        $user = User::factory()->create([
            'role' => 'farmer',
            'full_name' => 'John Doe',
            'preferred_language' => 'en',
        ]);

        $response = $this->actingAs($user)->patchJson('/profile', [
            'full_name' => 'John Doe',
            'phone_number' => 'invalid-phone', // Invalid phone
            'preferred_language' => 'en',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('phone_number');
    }

    public function test_profile_update_validates_full_name()
    {
        $user = User::factory()->create([
            'role' => 'farmer',
            'full_name' => 'John Doe',
            'preferred_language' => 'en',
        ]);

        $response = $this->actingAs($user)->patchJson('/profile', [
            'full_name' => 'John 123', // Invalid name
            'preferred_language' => 'en',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('full_name');
    }
}
