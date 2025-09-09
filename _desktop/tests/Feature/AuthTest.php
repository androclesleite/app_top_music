<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_user_cannot_login_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    /**
     * Test login fails with invalid password.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    /**
     * Test login validation errors.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postApi('/api/v1/auth/login', []);

        $this->assertValidationError($response, ['email', 'password']);
    }

    /**
     * Test login requires valid email format.
     */
    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $this->assertValidationError($response, ['email']);
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postApi('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        // Verify the token is invalidated
        $response = $this->getApi('/api/v1/auth/me');
        $this->assertUnauthorized($response);
    }

    /**
     * Test unauthenticated user cannot logout.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postApi('/api/v1/auth/logout');

        $this->assertUnauthorized($response);
    }

    /**
     * Test authenticated user can get their data.
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->authenticateUser();

        $response = $this->getApi('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ]);
    }

    /**
     * Test unauthenticated user cannot get profile.
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getApi('/api/v1/auth/me');

        $this->assertUnauthorized($response);
    }

    /**
     * Test authenticated user can refresh token.
     */
    public function test_authenticated_user_can_refresh_token(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postApi('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test unauthenticated user cannot refresh token.
     */
    public function test_unauthenticated_user_cannot_refresh_token(): void
    {
        $response = $this->postApi('/api/v1/auth/refresh');

        $this->assertUnauthorized($response);
    }

    /**
     * Test accessing protected routes without authentication.
     */
    public function test_protected_routes_require_authentication(): void
    {
        $protectedRoutes = [
            ['method' => 'get', 'uri' => '/api/v1/auth/me'],
            ['method' => 'post', 'uri' => '/api/v1/auth/logout'],
            ['method' => 'post', 'uri' => '/api/v1/auth/refresh'],
            ['method' => 'get', 'uri' => '/api/v1/suggestions'],
            ['method' => 'post', 'uri' => '/api/v1/songs'],
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->{$route['method'] . 'Api'}($route['uri']);
            $this->assertUnauthorized($response, "Route {$route['method']} {$route['uri']} should require authentication");
        }
    }

    /**
     * Test token invalidation after logout.
     */
    public function test_token_is_invalid_after_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // First, verify the token works
        $response = $this->getApi('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Logout using the token
        $response = $this->postApi('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // Verify the token is now invalid
        $response = $this->getApi('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $this->assertUnauthorized($response);
    }
}