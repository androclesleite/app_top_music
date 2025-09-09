<?php

namespace Tests\Feature;

use App\Models\SongSuggestion;
use Tests\TestCase;

class SuggestionTest extends TestCase
{
    public function test_can_create_suggestion_without_authentication()
    {
        $suggestionData = [
            'title' => 'Test Suggestion',
            'artist' => 'Test Artist',
            'youtube_url' => $this->generateYouTubeUrl(),
            'suggested_by_name' => 'John Doe',
            'suggested_by_email' => 'john@example.com'
        ];

        $response = $this->postApi('/api/v1/suggestions', $suggestionData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'artist', 'youtube_url',
                'suggested_by_name', 'suggested_by_email', 'status'
            ]
        ]);

        $this->assertDatabaseHas('song_suggestions', [
            'title' => 'Test Suggestion',
            'artist' => 'Test Artist',
            'status' => 'pending'
        ]);
    }

    public function test_create_suggestion_validation_errors()
    {
        $response = $this->postApi('/api/v1/suggestions', []);

        $this->assertValidationError($response, ['title', 'artist', 'youtube_url']);
    }

    public function test_create_suggestion_with_invalid_youtube_url()
    {
        $response = $this->postApi('/api/v1/suggestions', [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => 'https://invalid-url.com'
        ]);

        $this->assertValidationError($response, ['youtube_url']);
    }

    public function test_authenticated_user_can_get_paginated_suggestions()
    {
        $this->authenticateUser();
        SongSuggestion::factory()->count(10)->create();

        $response = $this->getApi('/api/v1/suggestions');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'title', 'artist', 'youtube_url',
                    'suggested_by_name', 'suggested_by_email', 'status'
                ]
            ],
            'meta' => [
                'current_page', 'per_page', 'total', 'last_page'
            ]
        ]);
    }

    public function test_unauthenticated_user_cannot_get_suggestions()
    {
        $response = $this->getApi('/api/v1/suggestions');

        $this->assertUnauthorized($response);
    }

    public function test_can_filter_suggestions_by_status()
    {
        $this->authenticateUser();
        SongSuggestion::factory()->count(3)->create(['status' => 'pending']);
        SongSuggestion::factory()->count(2)->create(['status' => 'approved']);

        $response = $this->getApi('/api/v1/suggestions?status=pending');

        $this->assertApiResponse($response, 200);
        
        $data = $response->json('data');
        $this->assertCount(3, $data);
        foreach ($data as $suggestion) {
            $this->assertEquals('pending', $suggestion['status']);
        }
    }

    public function test_can_search_suggestions_by_title()
    {
        $this->authenticateUser();
        SongSuggestion::factory()->create(['title' => 'Amazing Song']);
        SongSuggestion::factory()->create(['title' => 'Another Song']);
        SongSuggestion::factory()->create(['title' => 'Different Title']);

        $response = $this->getApi('/api/v1/suggestions?search=Amazing');

        $this->assertApiResponse($response, 200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Amazing', $data[0]['title']);
    }

    public function test_can_update_suggestion_status()
    {
        $this->authenticateUser();
        $suggestion = SongSuggestion::factory()->create(['status' => 'pending']);

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => 'approved',
            'admin_notes' => 'Approved by admin'
        ]);

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseHas('song_suggestions', [
            'id' => $suggestion->id,
            'status' => 'approved',
            'admin_notes' => 'Approved by admin'
        ]);
    }

    public function test_can_delete_suggestion()
    {
        $this->authenticateUser();
        $suggestion = SongSuggestion::factory()->create();

        $response = $this->deleteApi("/api/v1/suggestions/{$suggestion->id}");

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseMissing('song_suggestions', ['id' => $suggestion->id]);
    }

    public function test_unauthenticated_user_cannot_update_suggestion()
    {
        $suggestion = SongSuggestion::factory()->create();

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => 'approved'
        ]);

        $this->assertUnauthorized($response);
    }

    public function test_unauthenticated_user_cannot_delete_suggestion()
    {
        $suggestion = SongSuggestion::factory()->create();

        $response = $this->deleteApi("/api/v1/suggestions/{$suggestion->id}");

        $this->assertUnauthorized($response);
    }

    public function test_cannot_update_nonexistent_suggestion()
    {
        $this->authenticateUser();

        $response = $this->putApi('/api/v1/suggestions/999', [
            'status' => 'approved'
        ]);

        $this->assertApiError($response, 404);
    }

    public function test_cannot_delete_nonexistent_suggestion()
    {
        $this->authenticateUser();

        $response = $this->deleteApi('/api/v1/suggestions/999');

        $this->assertApiError($response, 404);
    }

    public function test_suggestion_status_validation()
    {
        $this->authenticateUser();
        $suggestion = SongSuggestion::factory()->create();

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => 'invalid_status'
        ]);

        $this->assertValidationError($response, ['status']);
    }
}