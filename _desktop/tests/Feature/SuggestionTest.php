<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\SongSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest user can create suggestion.
     */
    public function test_guest_can_create_suggestion(): void
    {
        $suggestionData = [
            'title' => 'New Song Suggestion',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'suggested_by' => 'John Doe',
        ];

        $response = $this->postApi('/api/v1/suggestions', $suggestionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'youtube_url',
                    'suggested_by',
                    'status',
                    'youtube_thumbnail',
                    'youtube_video_id',
                    'created_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'New Song Suggestion',
                    'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'suggested_by' => 'John Doe',
                    'status' => SongSuggestion::STATUS_PENDING
                ]
            ]);

        $this->assertDatabaseHas('song_suggestions', [
            'title' => 'New Song Suggestion',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'suggested_by' => 'John Doe',
            'status' => SongSuggestion::STATUS_PENDING
        ]);
    }

    /**
     * Test suggestion creation validation.
     */
    public function test_suggestion_creation_requires_valid_data(): void
    {
        // Test missing required fields
        $response = $this->postApi('/api/v1/suggestions', []);
        $this->assertValidationError($response, ['title', 'youtube_url', 'suggested_by']);

        // Test invalid YouTube URL
        $response = $this->postApi('/api/v1/suggestions', [
            'title' => 'Test Song',
            'youtube_url' => 'https://invalid-url.com',
            'suggested_by' => 'Test User'
        ]);
        $this->assertValidationError($response, ['youtube_url']);

        // Test duplicate YouTube URL
        SongSuggestion::factory()->create([
            'youtube_url' => 'https://www.youtube.com/watch?v=duplicate'
        ]);

        $response = $this->postApi('/api/v1/suggestions', [
            'title' => 'Another Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=duplicate',
            'suggested_by' => 'Test User'
        ]);
        $this->assertValidationError($response, ['youtube_url']);
    }

    /**
     * Test suggestion cannot be created with existing song URL.
     */
    public function test_suggestion_cannot_duplicate_existing_song(): void
    {
        Song::factory()->create([
            'youtube_url' => 'https://www.youtube.com/watch?v=existing'
        ]);

        $response = $this->postApi('/api/v1/suggestions', [
            'title' => 'Duplicate Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=existing',
            'suggested_by' => 'Test User'
        ]);

        $this->assertValidationError($response, ['youtube_url']);
    }

    /**
     * Test admin can view all suggestions.
     */
    public function test_admin_can_view_all_suggestions(): void
    {
        $this->authenticateAdmin();

        SongSuggestion::factory()->pending()->count(3)->create();
        SongSuggestion::factory()->approved()->count(2)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        $response = $this->getApi('/api/v1/suggestions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'youtube_url',
                            'suggested_by',
                            'status',
                            'reviewed_by',
                            'reviewed_at',
                            'created_at'
                        ]
                    ],
                    'current_page',
                    'total'
                ]
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals(6, $response->json('data.total'));
    }

    /**
     * Test guest cannot view suggestions.
     */
    public function test_guest_cannot_view_suggestions(): void
    {
        $response = $this->getApi('/api/v1/suggestions');

        $this->assertUnauthorized($response);
    }

    /**
     * Test admin can view pending suggestions only.
     */
    public function test_admin_can_view_pending_suggestions(): void
    {
        $this->authenticateAdmin();

        SongSuggestion::factory()->pending()->count(5)->create();
        SongSuggestion::factory()->approved()->count(3)->create();
        SongSuggestion::factory()->rejected()->count(2)->create();

        $response = $this->getApi('/api/v1/suggestions/pending');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $suggestions = $response->json('data.data');
        $this->assertCount(5, $suggestions);

        foreach ($suggestions as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion['status']);
        }
    }

    /**
     * Test admin can view suggestion stats.
     */
    public function test_admin_can_view_suggestion_stats(): void
    {
        $this->authenticateAdmin();

        SongSuggestion::factory()->pending()->count(10)->create();
        SongSuggestion::factory()->approved()->count(5)->create();
        SongSuggestion::factory()->rejected()->count(3)->create();

        $response = $this->getApi('/api/v1/suggestions/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total',
                    'pending',
                    'approved',
                    'rejected'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 18,
                    'pending' => 10,
                    'approved' => 5,
                    'rejected' => 3
                ]
            ]);
    }

    /**
     * Test admin can view specific suggestion.
     */
    public function test_admin_can_view_specific_suggestion(): void
    {
        $this->authenticateAdmin();
        $suggestion = SongSuggestion::factory()->pending()->create();

        $response = $this->getApi("/api/v1/suggestions/{$suggestion->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'youtube_url',
                    'suggested_by',
                    'status',
                    'reviewed_by',
                    'reviewed_at',
                    'youtube_thumbnail',
                    'youtube_video_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'status' => SongSuggestion::STATUS_PENDING
                ]
            ]);
    }

    /**
     * Test admin can approve suggestion.
     */
    public function test_admin_can_approve_suggestion(): void
    {
        $admin = $this->authenticateAdmin();
        $suggestion = SongSuggestion::factory()->pending()->create();

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => SongSuggestion::STATUS_APPROVED
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Suggestion updated successfully',
                'data' => [
                    'id' => $suggestion->id,
                    'status' => SongSuggestion::STATUS_APPROVED
                ]
            ]);

        $this->assertDatabaseHas('song_suggestions', [
            'id' => $suggestion->id,
            'status' => SongSuggestion::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
        ]);

        // Verify reviewed_at was set
        $updatedSuggestion = SongSuggestion::find($suggestion->id);
        $this->assertNotNull($updatedSuggestion->reviewed_at);
    }

    /**
     * Test admin can reject suggestion.
     */
    public function test_admin_can_reject_suggestion(): void
    {
        $admin = $this->authenticateAdmin();
        $suggestion = SongSuggestion::factory()->pending()->create();

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => SongSuggestion::STATUS_REJECTED
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Suggestion updated successfully',
                'data' => [
                    'status' => SongSuggestion::STATUS_REJECTED
                ]
            ]);

        $this->assertDatabaseHas('song_suggestions', [
            'id' => $suggestion->id,
            'status' => SongSuggestion::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
        ]);
    }

    /**
     * Test guest cannot approve/reject suggestions.
     */
    public function test_guest_cannot_update_suggestions(): void
    {
        $suggestion = SongSuggestion::factory()->pending()->create();

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => SongSuggestion::STATUS_APPROVED
        ]);

        $this->assertUnauthorized($response);
    }

    /**
     * Test approving suggestion creates song automatically.
     */
    public function test_approving_suggestion_creates_song(): void
    {
        $this->authenticateAdmin();
        $suggestion = SongSuggestion::factory()->pending()->create([
            'title' => 'Approved Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=approved'
        ]);

        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => SongSuggestion::STATUS_APPROVED,
            'create_song' => true
        ]);

        $response->assertStatus(200);

        // Verify song was created
        $this->assertDatabaseHas('songs', [
            'title' => 'Approved Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=approved',
            'plays_count' => 0,
            'position' => null
        ]);
    }

    /**
     * Test suggestion update validation.
     */
    public function test_suggestion_update_requires_valid_status(): void
    {
        $this->authenticateAdmin();
        $suggestion = SongSuggestion::factory()->pending()->create();

        // Test invalid status
        $response = $this->putApi("/api/v1/suggestions/{$suggestion->id}", [
            'status' => 'invalid_status'
        ]);

        $this->assertValidationError($response, ['status']);
    }

    /**
     * Test suggestion workflow from creation to approval.
     */
    public function test_complete_suggestion_workflow(): void
    {
        // 1. Guest creates suggestion
        $response = $this->postApi('/api/v1/suggestions', [
            'title' => 'Workflow Test Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=workflow',
            'suggested_by' => 'Test User'
        ]);

        $response->assertStatus(201);
        $suggestionId = $response->json('data.id');

        // 2. Verify suggestion is pending
        $suggestion = SongSuggestion::find($suggestionId);
        $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);

        // 3. Admin reviews and approves
        $admin = $this->authenticateAdmin();
        $response = $this->putApi("/api/v1/suggestions/{$suggestionId}", [
            'status' => SongSuggestion::STATUS_APPROVED,
            'create_song' => true
        ]);

        $response->assertStatus(200);

        // 4. Verify suggestion is approved and song is created
        $suggestion->refresh();
        $this->assertEquals(SongSuggestion::STATUS_APPROVED, $suggestion->status);
        $this->assertEquals($admin->id, $suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);

        $this->assertDatabaseHas('songs', [
            'title' => 'Workflow Test Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=workflow'
        ]);
    }

    /**
     * Test suggestion filtering by status.
     */
    public function test_suggestions_can_be_filtered_by_status(): void
    {
        $this->authenticateAdmin();

        SongSuggestion::factory()->pending()->count(3)->create();
        SongSuggestion::factory()->approved()->count(2)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        // Test pending filter
        $response = $this->getApi('/api/v1/suggestions?status=pending');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));

        // Test approved filter
        $response = $this->getApi('/api/v1/suggestions?status=approved');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));

        // Test rejected filter
        $response = $this->getApi('/api/v1/suggestions?status=rejected');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test YouTube URL extraction in suggestions.
     */
    public function test_suggestion_youtube_url_extraction(): void
    {
        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        foreach ($validUrls as $url) {
            $response = $this->postApi('/api/v1/suggestions', [
                'title' => 'Test Song - ' . $url,
                'youtube_url' => $url,
                'suggested_by' => 'Test User'
            ]);

            $response->assertStatus(201);
            
            $suggestion = SongSuggestion::where('youtube_url', $url)->first();
            $this->assertEquals('dQw4w9WgXcQ', $suggestion->youtube_video_id);
            $this->assertStringContains('dQw4w9WgXcQ', $suggestion->youtube_thumbnail);
        }
    }

    /**
     * Test suggestion pagination.
     */
    public function test_suggestions_are_paginated(): void
    {
        $this->authenticateAdmin();

        SongSuggestion::factory()->count(25)->create();

        $response = $this->getApi('/api/v1/suggestions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertEquals(15, $response->json('data.per_page'));
        $this->assertEquals(25, $response->json('data.total'));
        $this->assertEquals(2, $response->json('data.last_page'));
    }

    /**
     * Test suggestion ordering.
     */
    public function test_suggestions_are_ordered_by_creation_date(): void
    {
        $this->authenticateAdmin();

        $oldSuggestion = SongSuggestion::factory()->create([
            'title' => 'Old Suggestion',
            'created_at' => now()->subDays(5)
        ]);

        $newSuggestion = SongSuggestion::factory()->create([
            'title' => 'New Suggestion',
            'created_at' => now()->subHours(1)
        ]);

        $response = $this->getApi('/api/v1/suggestions');

        $suggestions = $response->json('data.data');
        $this->assertEquals('New Suggestion', $suggestions[0]['title']);
        $this->assertEquals('Old Suggestion', $suggestions[1]['title']);
    }
}