<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/register', [
            'full_name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'farmer',
            'preferred_language' => 'en',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
        
        $this->assertDatabaseHas('farmer_profiles', [
            'user_id' => User::where('email', 'johndoe@example.com')->first()->id,
        ]);
    }

    public function test_registration_validates_full_name()
    {
        $response = $this->postJson('/register', [
            'full_name' => 'John 123', // Invalid name
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'farmer',
            'preferred_language' => 'en',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('full_name');
    }

    public function test_registration_validates_email()
    {
        $response = $this->postJson('/register', [
            'full_name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'farmer',
            'preferred_language' => 'en',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertAuthenticatedAs($user);
    }
}
