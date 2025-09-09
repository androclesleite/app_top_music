<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Authenticate a user and return the user instance.
     */
    protected function authenticateUser(?User $user = null): User
    {
        $user = $user ?: User::factory()->create();
        Sanctum::actingAs($user);
        
        return $user;
    }

    /**
     * Create an admin user and authenticate.
     */
    protected function authenticateAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);
        
        Sanctum::actingAs($admin);
        
        return $admin;
    }

    /**
     * Assert that response has the correct JSON structure for API responses.
     */
    protected function assertApiResponse(TestResponse $response, int $status = 200): TestResponse
    {
        $response->assertStatus($status);
        
        if ($status >= 200 && $status < 300) {
            $response->assertJsonStructure([
                'data',
                'message',
                'success'
            ]);
            $response->assertJson(['success' => true]);
        }
        
        return $response;
    }

    /**
     * Assert that response is an error with proper structure.
     */
    protected function assertApiError(TestResponse $response, int $status = 400): TestResponse
    {
        $response->assertStatus($status);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);
        $response->assertJson(['success' => false]);
        
        return $response;
    }

    /**
     * Assert that response is a validation error.
     */
    protected function assertValidationError(TestResponse $response, array $fields = []): TestResponse
    {
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors'
        ]);
        
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $response->assertJsonValidationErrors($field);
            }
        }
        
        return $response;
    }

    /**
     * Assert that response is unauthorized.
     */
    protected function assertUnauthorized(TestResponse $response): TestResponse
    {
        return $response->assertStatus(401);
    }

    /**
     * Get API headers for JSON requests.
     */
    protected function getApiHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Make a GET request to API endpoint.
     */
    protected function getApi(string $uri, array $headers = []): TestResponse
    {
        return $this->get($uri, array_merge($this->getApiHeaders(), $headers));
    }

    /**
     * Make a POST request to API endpoint.
     */
    protected function postApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->post($uri, $data, array_merge($this->getApiHeaders(), $headers));
    }

    /**
     * Make a PUT request to API endpoint.
     */
    protected function putApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->put($uri, $data, array_merge($this->getApiHeaders(), $headers));
    }

    /**
     * Make a DELETE request to API endpoint.
     */
    protected function deleteApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->delete($uri, $data, array_merge($this->getApiHeaders(), $headers));
    }

    /**
     * Generate a valid YouTube URL for testing.
     */
    protected function generateYouTubeUrl(): string
    {
        $videoId = \Illuminate\Support\Str::random(11);
        return "https://www.youtube.com/watch?v={$videoId}";
    }

    /**
     * Generate an invalid YouTube URL for testing.
     */
    protected function generateInvalidUrl(): string
    {
        return 'https://invalid-url.com/video';
    }
}
