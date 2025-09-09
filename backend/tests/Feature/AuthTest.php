<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    public function test_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@techpines.com.br',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'admin@techpines.com.br',
            'password' => 'password123'
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token'
            ]
        ]);
    }

    public function test_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@techpines.com.br',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postApi('/api/v1/auth/login', [
            'email' => 'admin@techpines.com.br',
            'password' => 'wrongpassword'
        ]);

        $this->assertApiError($response, 400);
    }

    public function test_login_validation_errors()
    {
        $response = $this->postApi('/api/v1/auth/login', []);

        $this->assertValidationError($response, ['email', 'password']);
    }

    public function test_can_logout_authenticated_user()
    {
        $user = $this->authenticateUser();

        $response = $this->postApi('/api/v1/auth/logout');

        $this->assertApiResponse($response, 200);
    }

    public function test_cannot_logout_unauthenticated_user()
    {
        $response = $this->postApi('/api/v1/auth/logout');

        $this->assertUnauthorized($response);
    }

    public function test_can_get_authenticated_user_info()
    {
        $user = $this->authenticateUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $response = $this->getApi('/api/v1/auth/me');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email']
        ]);
        $response->assertJson([
            'data' => [
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]
        ]);
    }

    public function test_cannot_get_user_info_when_unauthenticated()
    {
        $response = $this->getApi('/api/v1/auth/me');

        $this->assertUnauthorized($response);
    }
}