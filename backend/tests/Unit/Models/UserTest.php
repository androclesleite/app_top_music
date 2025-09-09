<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_has_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_user_has_hidden_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        $this->assertTrue(\Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }
}