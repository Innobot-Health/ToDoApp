<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_logout_successfully()
    {
        $user = User::factory()->create();

        // Create a token for this user
        $token = $user->createToken('api-token')->plainTextToken;

        // Logout using the token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    /* public function cannot_access_protected_route_after_logout()
    {
        $user = User::factory()->create();

        // Create a token for this user
        $token = $user->createToken('api-token')->plainTextToken;

        // Logout using the token
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Try accessing protected route with same token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(401); // Token revoked, should be unauthorized
    } */

    /** @test */
    public function logout_fails_without_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401); // Must be unauthorized
    }
}
