<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for Auth API endpoints.
 *
 * Tests that:
 *  - Unauthenticated requests to protected routes return 401
 *  - Login issues a Sanctum token
 *  - Register creates a user and issues a token
 *  - Logout revokes the token
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'id'       => (string) Str::uuid(),
            'name'     => 'Test User',
            'phone'    => '0912345678',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'citizen',
        ], $overrides));
    }

    // ── Unauthenticated access ────────────────────────────────────────────

    public function test_unauthenticated_request_to_protected_route_returns_401(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_unauthenticated_logout_returns_401(): void
    {
        $this->postJson('/api/v1/logout')->assertStatus(401);
    }

    // ── Login ─────────────────────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/v1/login', [
            'phone'    => '0912345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createUser();

        $this->postJson('/api/v1/login', [
            'phone'    => '0912345678',
            'password' => 'wrongpassword',
        ])->assertStatus(401);
    }

    public function test_login_is_rate_limited(): void
    {
        $this->createUser();

        // Exceed the 5 req/min throttle defined on /v1/login
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/login', [
                'phone'    => '0912345678',
                'password' => 'wrong',
            ]);
        }

        $this->postJson('/api/v1/login', [
            'phone'    => '0912345678',
            'password' => 'wrong',
        ])->assertStatus(429);
    }

    // ── Register ──────────────────────────────────────────────────────────

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'New User',
            'phone'                 => '0900000001',
            'email'                 => 'newuser@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', ['phone' => '0900000001']);
    }

    // ── Logout ────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/v1/logout')
             ->assertStatus(200);

        // Token should be revoked from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
