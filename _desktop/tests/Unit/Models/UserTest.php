<?php

namespace Tests\Unit\Models;

use App\Models\SongSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User model has correct fillable attributes.
     */
    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = new User();
        $expected = ['name', 'email', 'password'];

        $this->assertEquals($expected, $user->getFillable());
    }

    /**
     * Test User model has correct hidden attributes.
     */
    public function test_user_has_correct_hidden_attributes(): void
    {
        $user = new User();
        $expected = ['password', 'remember_token'];

        $this->assertEquals($expected, $user->getHidden());
    }

    /**
     * Test User model casts attributes correctly.
     */
    public function test_user_casts_attributes_correctly(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-01-01 12:00:00',
        ]);

        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
        
        if ($user->email_verified_at) {
            $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        }
    }

    /**
     * Test User uses correct traits.
     */
    public function test_user_uses_correct_traits(): void
    {
        $user = new User();
        
        $traits = class_uses_recursive(get_class($user));
        
        $this->assertContains(\Illuminate\Database\Eloquent\Factories\HasFactory::class, $traits);
        $this->assertContains(\Illuminate\Notifications\Notifiable::class, $traits);
        $this->assertContains(\Laravel\Sanctum\HasApiTokens::class, $traits);
    }

    /**
     * Test User has reviewedSuggestions relationship.
     */
    public function test_user_has_reviewed_suggestions_relationship(): void
    {
        $user = new User();
        $relationship = $user->reviewedSuggestions();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('reviewed_by', $relationship->getForeignKeyName());
    }

    /**
     * Test reviewedSuggestions relationship returns correct suggestions.
     */
    public function test_reviewed_suggestions_relationship_returns_correct_suggestions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create suggestions reviewed by the user
        $suggestion1 = SongSuggestion::factory()->approved()->create([
            'reviewed_by' => $user->id,
            'title' => 'Reviewed by User'
        ]);

        $suggestion2 = SongSuggestion::factory()->rejected()->create([
            'reviewed_by' => $user->id,
            'title' => 'Also Reviewed by User'
        ]);

        // Create suggestion reviewed by other user
        SongSuggestion::factory()->approved()->create([
            'reviewed_by' => $otherUser->id,
            'title' => 'Reviewed by Other User'
        ]);

        // Create pending suggestion (not reviewed)
        SongSuggestion::factory()->pending()->create([
            'title' => 'Not Reviewed'
        ]);

        $reviewedSuggestions = $user->reviewedSuggestions;

        $this->assertCount(2, $reviewedSuggestions);
        $this->assertTrue($reviewedSuggestions->contains('title', 'Reviewed by User'));
        $this->assertTrue($reviewedSuggestions->contains('title', 'Also Reviewed by User'));
        $this->assertFalse($reviewedSuggestions->contains('title', 'Reviewed by Other User'));
        $this->assertFalse($reviewedSuggestions->contains('title', 'Not Reviewed'));
    }

    /**
     * Test User factory creates valid model.
     */
    public function test_user_factory_creates_valid_model(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertNotEmpty($user->password);
        $this->assertTrue(filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Test User password is hashed correctly.
     */
    public function test_user_password_is_hashed(): void
    {
        $plainPassword = 'password123';
        $user = User::factory()->create(['password' => $plainPassword]);

        // Password should be hashed, not plain text
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(password_verify($plainPassword, $user->password));
    }

    /**
     * Test User email is unique constraint (database level test).
     */
    public function test_user_email_uniqueness(): void
    {
        $email = 'unique@example.com';
        
        User::factory()->create(['email' => $email]);

        // Attempting to create another user with same email should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }

    /**
     * Test User model attributes are properly typed.
     */
    public function test_user_attributes_are_properly_typed(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->refresh();

        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsString($user->password);
        
        if ($user->email_verified_at) {
            $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        }
    }

    /**
     * Test User model timestamps.
     */
    public function test_user_model_timestamps(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->updated_at);
    }

    /**
     * Test User model can be updated.
     */
    public function test_user_model_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $user->update(['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $user->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    /**
     * Test User can create API tokens (Sanctum integration).
     */
    public function test_user_can_create_api_tokens(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token');

        $this->assertInstanceOf(\Laravel\Sanctum\NewAccessToken::class, $token);
        $this->assertNotEmpty($token->plainTextToken);
        $this->assertInstanceOf(PersonalAccessToken::class, $token->accessToken);
        $this->assertEquals($user->id, $token->accessToken->tokenable_id);
        $this->assertEquals('test-token', $token->accessToken->name);
    }

    /**
     * Test User can have multiple API tokens.
     */
    public function test_user_can_have_multiple_api_tokens(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('token-1');
        $token2 = $user->createToken('token-2');
        $token3 = $user->createToken('token-3');

        $this->assertCount(3, $user->tokens);
        $this->assertNotEquals($token1->plainTextToken, $token2->plainTextToken);
        $this->assertNotEquals($token2->plainTextToken, $token3->plainTextToken);
    }

    /**
     * Test User tokens can be deleted.
     */
    public function test_user_tokens_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('token-1');
        $token2 = $user->createToken('token-2');

        // Delete specific token
        $token1->accessToken->delete();

        $user->refresh();
        $this->assertCount(1, $user->tokens);
        $this->assertEquals('token-2', $user->tokens->first()->name);
    }

    /**
     * Test User can delete all tokens.
     */
    public function test_user_can_delete_all_tokens(): void
    {
        $user = User::factory()->create();

        $user->createToken('token-1');
        $user->createToken('token-2');
        $user->createToken('token-3');

        $this->assertCount(3, $user->tokens);

        // Delete all tokens
        $user->tokens()->delete();

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test User hidden attributes are not serialized.
     */
    public function test_user_hidden_attributes_not_serialized(): void
    {
        $user = User::factory()->create();

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    /**
     * Test User factory creates unique emails.
     */
    public function test_user_factory_creates_unique_emails(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->assertNotEquals($user1->email, $user2->email);
        $this->assertNotEquals($user2->email, $user3->email);
        $this->assertNotEquals($user1->email, $user3->email);
    }

    /**
     * Test User notifications functionality (from Notifiable trait).
     */
    public function test_user_notifications_functionality(): void
    {
        $user = User::factory()->create();

        // Check that notifications relationship exists
        $this->assertTrue(method_exists($user, 'notifications'));
        $this->assertTrue(method_exists($user, 'routeNotificationFor'));
        $this->assertTrue(method_exists($user, 'notify'));
    }

    /**
     * Test User relationship with suggestions through foreign key.
     */
    public function test_user_suggestions_foreign_key_constraint(): void
    {
        $user = User::factory()->create();
        
        // Create suggestion with this user as reviewer
        $suggestion = SongSuggestion::factory()->approved()->create([
            'reviewed_by' => $user->id
        ]);

        // Verify the relationship works
        $this->assertCount(1, $user->reviewedSuggestions);
        $this->assertEquals($suggestion->id, $user->reviewedSuggestions->first()->id);
    }

    /**
     * Test User model with no reviewed suggestions.
     */
    public function test_user_with_no_reviewed_suggestions(): void
    {
        $user = User::factory()->create();
        
        // Create suggestions reviewed by other users or not reviewed
        SongSuggestion::factory()->approved()->count(2)->create();
        SongSuggestion::factory()->pending()->count(3)->create();

        $this->assertCount(0, $user->reviewedSuggestions);
    }
}