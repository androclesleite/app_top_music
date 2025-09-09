<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /**
     * Test successful authentication with valid credentials.
     */
    public function test_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->authService->authenticate('test@example.com', 'password123');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertIsString($result['token']);
        $this->assertNotEmpty($result['token']);
    }

    /**
     * Test authentication fails with invalid email.
     */
    public function test_authenticate_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        $this->authService->authenticate('wrong@example.com', 'password123');
    }

    /**
     * Test authentication fails with invalid password.
     */
    public function test_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        $this->authService->authenticate('test@example.com', 'wrongpassword');
    }

    /**
     * Test authentication fails with non-existent user.
     */
    public function test_authenticate_with_non_existent_user(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        $this->authService->authenticate('nonexistent@example.com', 'password');
    }

    /**
     * Test successful logout.
     */
    public function test_logout_deletes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Simulate user being authenticated with the token
        Sanctum::actingAs($user, ['*'], $token->accessToken);

        $this->authService->logout($user);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id
        ]);
    }

    /**
     * Test logout all devices deletes all user tokens.
     */
    public function test_logout_all_devices_deletes_all_tokens(): void
    {
        $user = User::factory()->create();
        
        // Create multiple tokens for the user
        $token1 = $user->createToken('token1');
        $token2 = $user->createToken('token2');
        $token3 = $user->createToken('token3');

        $this->authService->logoutAllDevices($user);

        // Verify all tokens were deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token1->accessToken->id
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token2->accessToken->id
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token3->accessToken->id
        ]);
    }

    /**
     * Test refresh token deletes old token and creates new one.
     */
    public function test_refresh_token_replaces_current_token(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('old-token');
        
        // Simulate user being authenticated with the old token
        Sanctum::actingAs($user, ['*'], $oldToken->accessToken);

        $newTokenString = $this->authService->refreshToken($user);

        // Verify old token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $oldToken->accessToken->id
        ]);

        // Verify new token was created
        $this->assertIsString($newTokenString);
        $this->assertNotEmpty($newTokenString);
        $this->assertNotEquals($oldToken->plainTextToken, $newTokenString);
    }

    /**
     * Test password hashing is verified correctly.
     */
    public function test_password_verification_uses_hash_check(): void
    {
        $plainPassword = 'mySecurePassword123!';
        $hashedPassword = Hash::make($plainPassword);
        
        $user = User::factory()->create([
            'email' => 'hash@example.com',
            'password' => $hashedPassword,
        ]);

        // Should succeed with correct password
        $result = $this->authService->authenticate('hash@example.com', $plainPassword);
        $this->assertNotNull($result);

        // Should fail with incorrect password
        $this->expectException(ValidationException::class);
        $this->authService->authenticate('hash@example.com', 'wrongPassword');
    }

    /**
     * Test authentication generates unique tokens.
     */
    public function test_authentication_generates_unique_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'unique@example.com',
            'password' => Hash::make('password'),
        ]);

        $result1 = $this->authService->authenticate('unique@example.com', 'password');
        $result2 = $this->authService->authenticate('unique@example.com', 'password');

        $this->assertNotEquals($result1['token'], $result2['token']);
    }

    /**
     * Test user model is fresh instance from database.
     */
    public function test_authenticate_returns_fresh_user_instance(): void
    {
        $originalUser = User::factory()->create([
            'email' => 'fresh@example.com',
            'password' => Hash::make('password'),
            'name' => 'Original Name'
        ]);

        // Update user in database
        $originalUser->update(['name' => 'Updated Name']);

        $result = $this->authService->authenticate('fresh@example.com', 'password');

        // The returned user should have the updated name
        $this->assertEquals('Updated Name', $result['user']->name);
        $this->assertEquals($originalUser->id, $result['user']->id);
    }

    /**
     * Test email comparison is case-insensitive.
     */
    public function test_authenticate_email_case_insensitive(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Should work with different cases
        $result1 = $this->authService->authenticate('test@example.com', 'password123');
        $result2 = $this->authService->authenticate('TEST@EXAMPLE.COM', 'password123');
        $result3 = $this->authService->authenticate('Test@Example.Com', 'password123');

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertNotNull($result3);
        
        // All should return the same user
        $this->assertEquals($result1['user']->id, $result2['user']->id);
        $this->assertEquals($result2['user']->id, $result3['user']->id);
    }

    /**
     * Test validation exception contains correct error structure.
     */
    public function test_validation_exception_structure(): void
    {
        try {
            $this->authService->authenticate('wrong@email.com', 'wrongpassword');
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertContains('The provided credentials are incorrect.', $errors['email']);
        }
    }
}