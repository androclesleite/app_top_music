<?php

namespace Tests\Feature;

use App\Models\Song;
use Tests\TestCase;

class SongTest extends TestCase
{
    public function test_can_get_paginated_songs()
    {
        Song::factory()->count(10)->create();

        $response = $this->getApi('/api/v1/songs');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'title', 'artist', 'youtube_url', 
                    'position', 'plays_count', 'is_top_five'
                ]
            ],
            'meta' => [
                'current_page', 'per_page', 'total', 'last_page'
            ]
        ]);
    }

    public function test_can_get_top_five_songs()
    {
        Song::factory()->count(5)->create(['is_top_five' => true]);
        Song::factory()->count(3)->create(['is_top_five' => false]);

        $response = $this->getApi('/api/v1/songs/top-five');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'title', 'artist', 'youtube_url', 
                    'position', 'plays_count', 'is_top_five'
                ]
            ]
        ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);
        foreach ($data as $song) {
            $this->assertTrue($song['is_top_five']);
        }
    }

    public function test_authenticated_user_can_create_song()
    {
        $this->authenticateUser();

        $songData = [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => $this->generateYouTubeUrl()
        ];

        $response = $this->postApi('/api/v1/songs', $songData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'artist', 'youtube_url',
                'youtube_video_id', 'youtube_thumbnail'
            ]
        ]);

        $this->assertDatabaseHas('songs', [
            'title' => 'Test Song',
            'artist' => 'Test Artist'
        ]);
    }

    public function test_unauthenticated_user_cannot_create_song()
    {
        $songData = [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => $this->generateYouTubeUrl()
        ];

        $response = $this->postApi('/api/v1/songs', $songData);

        $this->assertUnauthorized($response);
    }

    public function test_create_song_validation_errors()
    {
        $this->authenticateUser();

        $response = $this->postApi('/api/v1/songs', []);

        $this->assertValidationError($response, ['title', 'artist', 'youtube_url']);
    }

    public function test_create_song_with_invalid_youtube_url()
    {
        $this->authenticateUser();

        $response = $this->postApi('/api/v1/songs', [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => 'https://invalid-url.com'
        ]);

        $this->assertValidationError($response, ['youtube_url']);
    }

    public function test_can_update_song()
    {
        $this->authenticateUser();
        $song = Song::factory()->create([
            'title' => 'Original Title'
        ]);

        $response = $this->putApi("/api/v1/songs/{$song->id}", [
            'title' => 'Updated Title',
            'artist' => $song->artist,
            'youtube_url' => $song->youtube_url
        ]);

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_can_delete_song()
    {
        $this->authenticateUser();
        $song = Song::factory()->create();

        $response = $this->deleteApi("/api/v1/songs/{$song->id}");

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }

    public function test_can_increment_play_count()
    {
        $song = Song::factory()->create(['plays_count' => 10]);

        $response = $this->postApi("/api/v1/songs/{$song->id}/play");

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'plays_count' => 11
        ]);
    }

    public function test_can_reorder_songs()
    {
        $this->authenticateUser();
        $songs = Song::factory()->count(3)->create();
        $songIds = $songs->pluck('id')->toArray();

        $response = $this->postApi('/api/v1/songs/reorder', [
            'song_ids' => array_reverse($songIds)
        ]);

        $this->assertApiResponse($response, 200);
    }

    public function test_cannot_update_nonexistent_song()
    {
        $this->authenticateUser();

        $response = $this->putApi('/api/v1/songs/999', [
            'title' => 'Updated Title'
        ]);

        $this->assertApiError($response, 404);
    }

    public function test_cannot_delete_nonexistent_song()
    {
        $this->authenticateUser();

        $response = $this->deleteApi('/api/v1/songs/999');

        $this->assertApiError($response, 404);
    }
}