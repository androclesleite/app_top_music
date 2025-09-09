<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest user can view top five songs.
     */
    public function test_guest_can_view_top_five_songs(): void
    {
        // Create top 5 songs
        Song::factory()->create(['title' => 'Song 1', 'position' => 1]);
        Song::factory()->create(['title' => 'Song 2', 'position' => 2]);
        Song::factory()->create(['title' => 'Song 3', 'position' => 3]);
        Song::factory()->create(['title' => 'Song 4', 'position' => 4]);
        Song::factory()->create(['title' => 'Song 5', 'position' => 5]);

        // Create other songs (should not appear in top five)
        Song::factory()->create(['title' => 'Other Song', 'position' => null]);

        $response = $this->getApi('/api/v1/songs/top-five');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'youtube_url',
                        'position',
                        'plays_count',
                        'youtube_thumbnail',
                        'youtube_video_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJson(['success' => true])
            ->assertJsonCount(5, 'data');

        // Verify songs are ordered by position
        $songs = $response->json('data');
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals($i + 1, $songs[$i]['position']);
        }
    }

    /**
     * Test guest user can view other songs with pagination.
     */
    public function test_guest_can_view_other_songs_with_pagination(): void
    {
        // Create top 5 songs
        Song::factory()->count(5)->topFive()->create();

        // Create other songs
        Song::factory()->count(20)->create(['position' => null]);

        $response = $this->getApi('/api/v1/songs');

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
                            'position',
                            'plays_count',
                            'youtube_thumbnail',
                            'youtube_video_id'
                        ]
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ])
            ->assertJson(['success' => true]);

        // Verify pagination
        $this->assertEquals(15, $response->json('data.per_page'));
        $this->assertEquals(20, $response->json('data.total'));
    }

    /**
     * Test guest user can view specific song.
     */
    public function test_guest_can_view_specific_song(): void
    {
        $song = Song::factory()->create([
            'title' => 'Test Song',
            'plays_count' => 100
        ]);

        $response = $this->getApi("/api/v1/songs/{$song->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'youtube_url',
                    'position',
                    'plays_count',
                    'youtube_thumbnail',
                    'youtube_video_id'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $song->id,
                    'title' => $song->title,
                    'plays_count' => 100
                ]
            ]);
    }

    /**
     * Test viewing non-existent song returns 404.
     */
    public function test_viewing_non_existent_song_returns_404(): void
    {
        $response = $this->getApi('/api/v1/songs/999');

        $response->assertStatus(404);
    }

    /**
     * Test guest user can increment play count.
     */
    public function test_guest_can_increment_play_count(): void
    {
        $song = Song::factory()->create(['plays_count' => 10]);

        $response = $this->postApi("/api/v1/songs/{$song->id}/play");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Play count incremented'
            ]);

        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'plays_count' => 11
        ]);
    }

    /**
     * Test authenticated admin can create song.
     */
    public function test_admin_can_create_song(): void
    {
        $this->authenticateAdmin();

        $songData = [
            'title' => 'New Test Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'position' => 3,
        ];

        $response = $this->postApi('/api/v1/songs', $songData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'youtube_url',
                    'position',
                    'plays_count'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'New Test Song',
                    'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'position' => 3,
                    'plays_count' => 0
                ]
            ]);

        $this->assertDatabaseHas('songs', [
            'title' => 'New Test Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'position' => 3
        ]);
    }

    /**
     * Test guest cannot create song.
     */
    public function test_guest_cannot_create_song(): void
    {
        $response = $this->postApi('/api/v1/songs', [
            'title' => 'New Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=test'
        ]);

        $this->assertUnauthorized($response);
    }

    /**
     * Test song creation validation.
     */
    public function test_song_creation_requires_valid_data(): void
    {
        $this->authenticateAdmin();

        // Test missing required fields
        $response = $this->postApi('/api/v1/songs', []);
        $this->assertValidationError($response, ['title', 'youtube_url']);

        // Test invalid YouTube URL
        $response = $this->postApi('/api/v1/songs', [
            'title' => 'Test Song',
            'youtube_url' => 'https://invalid-url.com'
        ]);
        $this->assertValidationError($response, ['youtube_url']);

        // Test invalid position
        $response = $this->postApi('/api/v1/songs', [
            'title' => 'Test Song',
            'youtube_url' => 'https://www.youtube.com/watch?v=test',
            'position' => -1
        ]);
        $this->assertValidationError($response, ['position']);
    }

    /**
     * Test admin can update song.
     */
    public function test_admin_can_update_song(): void
    {
        $this->authenticateAdmin();
        $song = Song::factory()->create();

        $updateData = [
            'title' => 'Updated Song Title',
            'youtube_url' => 'https://www.youtube.com/watch?v=updated',
            'position' => 2,
        ];

        $response = $this->putApi("/api/v1/songs/{$song->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $song->id,
                    'title' => 'Updated Song Title',
                    'youtube_url' => 'https://www.youtube.com/watch?v=updated',
                    'position' => 2
                ]
            ]);

        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'title' => 'Updated Song Title',
            'position' => 2
        ]);
    }

    /**
     * Test guest cannot update song.
     */
    public function test_guest_cannot_update_song(): void
    {
        $song = Song::factory()->create();

        $response = $this->putApi("/api/v1/songs/{$song->id}", [
            'title' => 'Updated Title'
        ]);

        $this->assertUnauthorized($response);
    }

    /**
     * Test admin can delete song.
     */
    public function test_admin_can_delete_song(): void
    {
        $this->authenticateAdmin();
        $song = Song::factory()->create();

        $response = $this->deleteApi("/api/v1/songs/{$song->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Song deleted successfully'
            ]);

        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }

    /**
     * Test guest cannot delete song.
     */
    public function test_guest_cannot_delete_song(): void
    {
        $song = Song::factory()->create();

        $response = $this->deleteApi("/api/v1/songs/{$song->id}");

        $this->assertUnauthorized($response);
    }

    /**
     * Test admin can update top five positions.
     */
    public function test_admin_can_update_top_five_positions(): void
    {
        $this->authenticateAdmin();
        
        $songs = Song::factory()->count(5)->create();
        foreach ($songs as $index => $song) {
            $song->update(['position' => $index + 1]);
        }

        // Reorder: swap positions 1 and 2
        $positions = [
            $songs[1]->id => 1, // Second song becomes first
            $songs[0]->id => 2, // First song becomes second
            $songs[2]->id => 3, // Third stays
            $songs[3]->id => 4, // Fourth stays
            $songs[4]->id => 5, // Fifth stays
        ];

        $response = $this->putApi('/api/v1/songs/positions', ['positions' => $positions]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Positions updated successfully'
            ]);

        // Verify positions were updated
        $this->assertDatabaseHas('songs', ['id' => $songs[1]->id, 'position' => 1]);
        $this->assertDatabaseHas('songs', ['id' => $songs[0]->id, 'position' => 2]);
    }

    /**
     * Test position update validation.
     */
    public function test_position_update_requires_valid_data(): void
    {
        $this->authenticateAdmin();

        // Test missing positions
        $response = $this->putApi('/api/v1/songs/positions', []);
        $this->assertValidationError($response, ['positions']);

        // Test invalid position count
        $response = $this->putApi('/api/v1/songs/positions', [
            'positions' => [1 => 1, 2 => 2] // Only 2 positions, need 5
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test deleting song from top 5 reorganizes positions.
     */
    public function test_deleting_top_five_song_reorganizes_positions(): void
    {
        $this->authenticateAdmin();

        // Create top 5 songs
        $songs = collect();
        for ($i = 1; $i <= 5; $i++) {
            $songs->push(Song::factory()->create(['position' => $i, 'title' => "Song {$i}"]));
        }

        // Delete song at position 3
        $songToDelete = $songs->where('position', 3)->first();
        $response = $this->deleteApi("/api/v1/songs/{$songToDelete->id}");
        $response->assertStatus(200);

        // Verify positions were reorganized
        $remainingSongs = Song::whereNotNull('position')->orderBy('position')->get();
        $this->assertCount(4, $remainingSongs);
        
        $expectedPositions = [1, 2, 3, 4];
        foreach ($remainingSongs as $index => $song) {
            $this->assertEquals($expectedPositions[$index], $song->position);
        }
    }

    /**
     * Test YouTube URL formats are properly handled.
     */
    public function test_youtube_url_formats_are_handled(): void
    {
        $this->authenticateAdmin();

        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        foreach ($validUrls as $url) {
            $response = $this->postApi('/api/v1/songs', [
                'title' => 'Test Song - ' . $url,
                'youtube_url' => $url,
            ]);

            $response->assertStatus(201);
            
            $song = Song::where('youtube_url', $url)->first();
            $this->assertEquals('dQw4w9WgXcQ', $song->youtube_video_id);
            $this->assertStringContains('dQw4w9WgXcQ', $song->youtube_thumbnail);
        }
    }

    /**
     * Test song search functionality.
     */
    public function test_songs_can_be_searched(): void
    {
        Song::factory()->create(['title' => 'Amazing Grace']);
        Song::factory()->create(['title' => 'Graceful Song']);
        Song::factory()->create(['title' => 'Another Song']);

        $response = $this->getApi('/api/v1/songs?search=grace');

        $response->assertStatus(200);
        
        $songs = $response->json('data.data');
        $this->assertCount(2, $songs);
        
        foreach ($songs as $song) {
            $this->assertStringContainsStringIgnoringCase('grace', $song['title']);
        }
    }

    /**
     * Test creating song with existing position adjusts other positions.
     */
    public function test_creating_song_with_existing_position_adjusts_others(): void
    {
        $this->authenticateAdmin();

        // Create initial top 5
        Song::factory()->create(['position' => 1, 'title' => 'Song 1']);
        Song::factory()->create(['position' => 2, 'title' => 'Song 2']);
        Song::factory()->create(['position' => 3, 'title' => 'Song 3']);
        Song::factory()->create(['position' => 4, 'title' => 'Song 4']);
        Song::factory()->create(['position' => 5, 'title' => 'Song 5']);

        // Insert new song at position 2
        $response = $this->postApi('/api/v1/songs', [
            'title' => 'New Song at Position 2',
            'youtube_url' => 'https://www.youtube.com/watch?v=newSong',
            'position' => 2,
        ]);

        $response->assertStatus(201);

        // Verify the new song is at position 2
        $newSong = Song::where('title', 'New Song at Position 2')->first();
        $this->assertEquals(2, $newSong->position);

        // Verify other songs were shifted down appropriately
        $topFive = Song::whereNotNull('position')->orderBy('position')->get();
        $this->assertCount(6, $topFive); // Now we have 6 songs with positions

        // The songs that were originally at positions 2-5 should now be at 3-6
        $songAtPosition3 = Song::where('title', 'Song 2')->first();
        $this->assertEquals(3, $songAtPosition3->position);
    }
}