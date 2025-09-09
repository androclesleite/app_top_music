<?php

namespace Tests\Unit\Services;

use App\Models\Song;
use App\Services\SongService;
use App\Repositories\SongRepository;
use Tests\TestCase;
use Mockery;

class SongServiceTest extends TestCase
{
    protected SongService $songService;
    protected $songRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->songRepository = Mockery::mock(SongRepository::class);
        $this->songService = new SongService($this->songRepository);
    }

    public function test_can_get_paginated_songs()
    {
        $songs = Song::factory()->count(5)->make();
        $paginatedData = [
            'data' => $songs,
            'current_page' => 1,
            'per_page' => 15,
            'total' => 5,
            'last_page' => 1
        ];

        $this->songRepository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(1, 15, [])
            ->andReturn($paginatedData);

        $result = $this->songService->getPaginatedSongs(1, 15, []);

        $this->assertEquals($paginatedData, $result);
    }

    public function test_can_get_top_five_songs()
    {
        $topFiveSongs = Song::factory()->count(5)->make();

        $this->songRepository
            ->shouldReceive('getTopFive')
            ->once()
            ->andReturn($topFiveSongs);

        $result = $this->songService->getTopFiveSongs();

        $this->assertEquals($topFiveSongs, $result);
    }

    public function test_can_create_song()
    {
        $songData = [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => 'https://www.youtube.com/watch?v=test123',
            'youtube_video_id' => 'test123',
            'youtube_thumbnail' => 'https://img.youtube.com/vi/test123/hqdefault.jpg',
            'position' => 1,
            'plays_count' => 0,
            'is_top_five' => true
        ];

        $song = Song::factory()->make($songData);

        $this->songRepository
            ->shouldReceive('create')
            ->once()
            ->with($songData)
            ->andReturn($song);

        $result = $this->songService->createSong($songData);

        $this->assertEquals($song, $result);
    }

    public function test_can_update_song()
    {
        $song = Song::factory()->make(['id' => 1]);
        $updateData = [
            'title' => 'Updated Song',
            'artist' => 'Updated Artist'
        ];

        $this->songRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($song);

        $this->songRepository
            ->shouldReceive('update')
            ->once()
            ->with($song, $updateData)
            ->andReturn($song);

        $result = $this->songService->updateSong(1, $updateData);

        $this->assertEquals($song, $result);
    }

    public function test_can_delete_song()
    {
        $song = Song::factory()->make(['id' => 1]);

        $this->songRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($song);

        $this->songRepository
            ->shouldReceive('delete')
            ->once()
            ->with($song)
            ->andReturn(true);

        $result = $this->songService->deleteSong(1);

        $this->assertTrue($result);
    }

    public function test_can_increment_play_count()
    {
        $song = Song::factory()->make(['id' => 1, 'plays_count' => 10]);

        $this->songRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($song);

        $this->songRepository
            ->shouldReceive('incrementPlayCount')
            ->once()
            ->with($song)
            ->andReturn($song);

        $result = $this->songService->incrementPlayCount(1);

        $this->assertEquals($song, $result);
    }

    public function test_can_reorder_songs()
    {
        $songIds = [1, 2, 3];

        $this->songRepository
            ->shouldReceive('reorderSongs')
            ->once()
            ->with($songIds)
            ->andReturn(true);

        $result = $this->songService->reorderSongs($songIds);

        $this->assertTrue($result);
    }

    public function test_throws_exception_when_song_not_found()
    {
        $this->songRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Música não encontrada');

        $this->songService->updateSong(999, []);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}