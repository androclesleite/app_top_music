<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->authService->login('test@example.com', 'password123');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->id, $result['user']['id']);
        $this->assertIsString($result['token']);
    }

    public function test_cannot_login_with_invalid_email()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credenciais inválidas');

        $this->authService->login('wrong@example.com', 'password123');
    }

    public function test_cannot_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credenciais inválidas');

        $this->authService->login('test@example.com', 'wrongpassword');
    }

    public function test_can_logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Mock the authenticated user
        $this->actingAs($user, 'sanctum');

        $result = $this->authService->logout($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    public function test_can_get_authenticated_user()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $result = $this->authService->me($user);

        $this->assertIsArray($result);
        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }
}