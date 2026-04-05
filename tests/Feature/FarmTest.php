<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_farm()
    {
        $user = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($user)->postJson('/farms', [
            'farm_name' => 'Test Farm',
            'latitude' => 7.8731,
            'longitude' => 80.7718,
            'farm_size' => '5 Acres',
            'district' => 'Anuradhapura',
            'soil_type' => 'Reddish Brown Earth',
            'irrigation_source' => 'rainfed',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Farm registered successfully. Initializing local intelligence...',
                 ]);

        $this->assertDatabaseHas('farms', [
            'farm_name' => 'Test Farm',
            'farmer_id' => $user->id,
            'district' => 'Anuradhapura',
        ]);
    }

    public function test_farm_creation_requires_mandatory_fields()
    {
        $user = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($user)->postJson('/farms', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['farm_name', 'latitude', 'longitude']);
    }

    public function test_user_can_delete_their_own_farm()
    {
        $user = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::create([
            'farmer_id' => $user->id,
            'farm_name' => 'To Be Deleted',
            'latitude' => 7.0,
            'longitude' => 80.0,
        ]);

        $response = $this->actingAs($user)->deleteJson("/farms/{$farm->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('farms', ['id' => $farm->id]);
    }

    public function test_user_cannot_delete_others_farm()
    {
        $user1 = User::factory()->create(['role' => 'farmer']);
        $user2 = User::factory()->create(['role' => 'farmer']);
        
        $farm = Farm::create([
            'farmer_id' => $user1->id,
            'farm_name' => 'User 1 Farm',
            'latitude' => 7.0,
            'longitude' => 80.0,
        ]);

        $response = $this->actingAs($user2)->deleteJson("/farms/{$farm->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('farms', ['id' => $farm->id]);
    }
}
