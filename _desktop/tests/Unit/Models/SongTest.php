<?php

namespace Tests\Unit\Models;

use App\Models\Song;
use App\Models\SongSuggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Song model has correct fillable attributes.
     */
    public function test_song_has_correct_fillable_attributes(): void
    {
        $song = new Song();
        $expected = ['title', 'youtube_url', 'position', 'plays_count'];

        $this->assertEquals($expected, $song->getFillable());
    }

    /**
     * Test Song model casts attributes correctly.
     */
    public function test_song_casts_attributes_correctly(): void
    {
        $song = Song::factory()->create([
            'position' => '3',
            'plays_count' => '100'
        ]);

        $this->assertIsInt($song->position);
        $this->assertIsInt($song->plays_count);
        $this->assertEquals(3, $song->position);
        $this->assertEquals(100, $song->plays_count);
    }

    /**
     * Test Song has suggestions relationship.
     */
    public function test_song_has_suggestions_relationship(): void
    {
        $song = Song::factory()->create();
        
        // Create suggestions for this song (conceptually - in practice might not exist)
        // This tests the relationship definition itself
        $relationship = $song->suggestions();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('song_id', $relationship->getForeignKeyName());
    }

    /**
     * Test topFive scope returns songs with positions 1-5.
     */
    public function test_top_five_scope_filters_correctly(): void
    {
        // Create songs with various positions
        Song::factory()->create(['position' => 1, 'title' => 'Position 1']);
        Song::factory()->create(['position' => 3, 'title' => 'Position 3']);
        Song::factory()->create(['position' => 5, 'title' => 'Position 5']);
        Song::factory()->create(['position' => 6, 'title' => 'Position 6']); // Should not be included
        Song::factory()->create(['position' => null, 'title' => 'No position']); // Should not be included

        $topFiveSongs = Song::topFive()->get();

        $this->assertCount(3, $topFiveSongs);
        
        foreach ($topFiveSongs as $song) {
            $this->assertTrue($song->position >= 1 && $song->position <= 5);
        }
        
        // Verify they are ordered by position
        $positions = $topFiveSongs->pluck('position')->toArray();
        $this->assertEquals([1, 3, 5], $positions);
    }

    /**
     * Test others scope excludes top five and orders by plays count.
     */
    public function test_others_scope_filters_and_orders_correctly(): void
    {
        // Create top 5 songs
        Song::factory()->create(['position' => 1, 'plays_count' => 1000]);
        Song::factory()->create(['position' => 2, 'plays_count' => 900]);
        
        // Create other songs
        Song::factory()->create(['position' => 6, 'plays_count' => 500, 'title' => 'Position 6']);
        Song::factory()->create(['position' => null, 'plays_count' => 800, 'title' => 'No position high']);
        Song::factory()->create(['position' => null, 'plays_count' => 200, 'title' => 'No position low']);

        $otherSongs = Song::others()->get();

        $this->assertCount(3, $otherSongs);
        
        // Verify none are in top 5
        foreach ($otherSongs as $song) {
            $this->assertTrue($song->position === null || $song->position > 5);
        }
        
        // Verify they are ordered by plays_count descending
        $playsCounts = $otherSongs->pluck('plays_count')->toArray();
        $this->assertEquals([800, 500, 200], $playsCounts);
    }

    /**
     * Test YouTube thumbnail attribute generates correct URL.
     */
    public function test_youtube_thumbnail_attribute_generates_correct_url(): void
    {
        // Test various YouTube URL formats
        $testCases = [
            [
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'expected_id' => 'dQw4w9WgXcQ'
            ],
            [
                'url' => 'https://youtu.be/dQw4w9WgXcQ',
                'expected_id' => 'dQw4w9WgXcQ'
            ],
            [
                'url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
                'expected_id' => 'dQw4w9WgXcQ'
            ],
        ];

        foreach ($testCases as $testCase) {
            $song = Song::factory()->create(['youtube_url' => $testCase['url']]);
            
            $expectedThumbnail = "https://img.youtube.com/vi/{$testCase['expected_id']}/hqdefault.jpg";
            $this->assertEquals($expectedThumbnail, $song->youtube_thumbnail);
        }
    }

    /**
     * Test YouTube thumbnail attribute returns placeholder for invalid URL.
     */
    public function test_youtube_thumbnail_returns_placeholder_for_invalid_url(): void
    {
        $song = Song::factory()->create(['youtube_url' => 'https://invalid-url.com/video']);
        
        $expectedPlaceholder = 'https://via.placeholder.com/480x360/cccccc/666666?text=No+Image';
        $this->assertEquals($expectedPlaceholder, $song->youtube_thumbnail);
    }

    /**
     * Test YouTube video ID attribute extracts correct ID.
     */
    public function test_youtube_video_id_extracts_correct_id(): void
    {
        $testCases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/abc123XYZ89' => 'abc123XYZ89',
            'https://youtube.com/watch?v=test_video_' => 'test_video_',
            'https://www.youtube.com/watch?v=a1B2c3D4e5F&list=playlist' => 'a1B2c3D4e5F',
        ];

        foreach ($testCases as $url => $expectedId) {
            $song = Song::factory()->create(['youtube_url' => $url]);
            $this->assertEquals($expectedId, $song->youtube_video_id);
        }
    }

    /**
     * Test YouTube video ID attribute returns null for invalid URL.
     */
    public function test_youtube_video_id_returns_null_for_invalid_url(): void
    {
        $invalidUrls = [
            'https://vimeo.com/123456789',
            'https://invalid-url.com',
            'not-a-url-at-all',
            'https://youtube.com/invalid-format',
        ];

        foreach ($invalidUrls as $url) {
            $song = Song::factory()->create(['youtube_url' => $url]);
            $this->assertNull($song->youtube_video_id);
        }
    }

    /**
     * Test Song factory creates valid model.
     */
    public function test_song_factory_creates_valid_model(): void
    {
        $song = Song::factory()->create();

        $this->assertInstanceOf(Song::class, $song);
        $this->assertNotEmpty($song->title);
        $this->assertNotEmpty($song->youtube_url);
        $this->assertIsInt($song->plays_count);
        $this->assertTrue($song->plays_count >= 0);
    }

    /**
     * Test Song factory topFive state creates song with position 1-5.
     */
    public function test_song_factory_top_five_state(): void
    {
        $song = Song::factory()->topFive()->create();

        $this->assertGreaterThanOrEqual(1, $song->position);
        $this->assertLessThanOrEqual(5, $song->position);
    }

    /**
     * Test Song factory firstPlace state creates song at position 1.
     */
    public function test_song_factory_first_place_state(): void
    {
        $song = Song::factory()->firstPlace()->create();

        $this->assertEquals(1, $song->position);
    }

    /**
     * Test Song model attributes are properly typed after database interaction.
     */
    public function test_song_attributes_are_properly_typed(): void
    {
        $song = Song::factory()->create([
            'position' => 1,
            'plays_count' => 100
        ]);

        // Refresh from database to ensure proper casting
        $song->refresh();

        $this->assertIsString($song->title);
        $this->assertIsString($song->youtube_url);
        $this->assertIsInt($song->position);
        $this->assertIsInt($song->plays_count);
    }

    /**
     * Test Song model with null position.
     */
    public function test_song_model_handles_null_position(): void
    {
        $song = Song::factory()->create(['position' => null]);

        $this->assertNull($song->position);
        
        // Should be included in others scope
        $this->assertTrue(Song::others()->where('id', $song->id)->exists());
        
        // Should not be included in topFive scope
        $this->assertFalse(Song::topFive()->where('id', $song->id)->exists());
    }

    /**
     * Test Song model timestamps are properly handled.
     */
    public function test_song_model_timestamps(): void
    {
        $song = Song::factory()->create();

        $this->assertNotNull($song->created_at);
        $this->assertNotNull($song->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $song->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $song->updated_at);
    }

    /**
     * Test Song model can be updated.
     */
    public function test_song_model_can_be_updated(): void
    {
        $song = Song::factory()->create(['title' => 'Original Title']);

        $song->update(['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $song->title);
        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'title' => 'Updated Title'
        ]);
    }

    /**
     * Test Song model validation of YouTube URL pattern.
     */
    public function test_youtube_url_pattern_validation(): void
    {
        // Valid YouTube URLs should extract video ID
        $validUrls = [
            'https://www.youtube.com/watch?v=' . str_repeat('a', 11),
            'https://youtu.be/' . str_repeat('b', 11),
            'https://youtube.com/watch?v=' . str_repeat('c', 11),
        ];

        foreach ($validUrls as $url) {
            $song = Song::factory()->make(['youtube_url' => $url]);
            $this->assertNotNull($song->youtube_video_id, "Failed for URL: {$url}");
        }

        // Invalid URLs should return null for video ID
        $invalidUrls = [
            'https://www.youtube.com/watch?v=short', // Too short
            'https://www.youtube.com/watch?v=' . str_repeat('a', 12), // Too long
            'https://vimeo.com/123456',
            'not-a-url',
        ];

        foreach ($invalidUrls as $url) {
            $song = Song::factory()->make(['youtube_url' => $url]);
            $this->assertNull($song->youtube_video_id, "Should be null for URL: {$url}");
        }
    }
}