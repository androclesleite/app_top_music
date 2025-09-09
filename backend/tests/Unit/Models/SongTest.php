<?php

namespace Tests\Unit\Models;

use App\Models\Song;
use Tests\TestCase;

class SongTest extends TestCase
{
    public function test_song_can_be_created()
    {
        $song = Song::factory()->create([
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'position' => 1,
        ]);

        $this->assertInstanceOf(Song::class, $song);
        $this->assertEquals('Test Song', $song->title);
        $this->assertEquals('Test Artist', $song->artist);
        $this->assertEquals(1, $song->position);
    }

    public function test_song_has_fillable_attributes()
    {
        $song = new Song();
        $fillable = $song->getFillable();

        $expectedFillable = [
            'title',
            'artist',
            'youtube_url',
            'youtube_video_id',
            'youtube_thumbnail',
            'position',
            'plays_count',
            'is_top_five'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_song_has_correct_casts()
    {
        $song = new Song();
        $casts = $song->getCasts();

        $this->assertEquals('boolean', $casts['is_top_five']);
        $this->assertEquals('integer', $casts['plays_count']);
        $this->assertEquals('integer', $casts['position']);
    }

    public function test_is_top_five_scope()
    {
        Song::factory()->create(['is_top_five' => true]);
        Song::factory()->create(['is_top_five' => false]);
        Song::factory()->create(['is_top_five' => true]);

        $topFiveSongs = Song::isTopFive()->get();

        $this->assertCount(2, $topFiveSongs);
        $this->assertTrue($topFiveSongs->every(fn($song) => $song->is_top_five));
    }

    public function test_not_top_five_scope()
    {
        Song::factory()->create(['is_top_five' => true]);
        Song::factory()->create(['is_top_five' => false]);
        Song::factory()->create(['is_top_five' => false]);

        $notTopFiveSongs = Song::notTopFive()->get();

        $this->assertCount(2, $notTopFiveSongs);
        $this->assertTrue($notTopFiveSongs->every(fn($song) => !$song->is_top_five));
    }

    public function test_by_position_scope()
    {
        Song::factory()->create(['position' => 3]);
        Song::factory()->create(['position' => 1]);
        Song::factory()->create(['position' => 2]);

        $songs = Song::byPosition()->get();

        $this->assertEquals(1, $songs->first()->position);
        $this->assertEquals(2, $songs->skip(1)->first()->position);
        $this->assertEquals(3, $songs->last()->position);
    }

    public function test_by_plays_scope()
    {
        Song::factory()->create(['plays_count' => 100]);
        Song::factory()->create(['plays_count' => 300]);
        Song::factory()->create(['plays_count' => 200]);

        $songs = Song::byPlays()->get();

        $this->assertEquals(300, $songs->first()->plays_count);
        $this->assertEquals(200, $songs->skip(1)->first()->plays_count);
        $this->assertEquals(100, $songs->last()->plays_count);
    }
}