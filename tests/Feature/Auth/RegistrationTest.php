<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['id', 'name', 'email', 'role']);
    }

    /** @test */
    public function registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    /** @test */
    public function registration_fails_if_email_already_exists()
    {
        // Create an existing user with the same email
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Attempt to register with that email
        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user'
        ]);

        // Assert the API responds with a validation error for 'email'
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Invalid Email',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_fails_with_short_password()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Short Password',
            'email' => 'short@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'user'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

}
