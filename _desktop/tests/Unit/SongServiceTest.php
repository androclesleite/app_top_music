<?php

namespace Tests\Unit;

use App\Models\Song;
use App\Repositories\SongRepository;
use App\Services\SongService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class SongServiceTest extends TestCase
{
    use RefreshDatabase;

    private SongService $songService;
    private SongRepository $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(SongRepository::class);
        $this->songService = new SongService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getTopFive returns collection from repository.
     */
    public function test_get_top_five_returns_collection(): void
    {
        $expectedCollection = new Collection([
            Song::factory()->make(['position' => 1]),
            Song::factory()->make(['position' => 2]),
        ]);

        $this->mockRepository
            ->shouldReceive('getTopFive')
            ->once()
            ->andReturn($expectedCollection);

        $result = $this->songService->getTopFive();

        $this->assertSame($expectedCollection, $result);
    }

    /**
     * Test getOthers returns paginated results.
     */
    public function test_get_others_returns_paginated_results(): void
    {
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockRepository
            ->shouldReceive('getOthers')
            ->once()
            ->with(15)
            ->andReturn($expectedPaginator);

        $result = $this->songService->getOthers();

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getOthers with custom per page.
     */
    public function test_get_others_with_custom_per_page(): void
    {
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockRepository
            ->shouldReceive('getOthers')
            ->once()
            ->with(25)
            ->andReturn($expectedPaginator);

        $result = $this->songService->getOthers(25);

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getSong returns song from repository.
     */
    public function test_get_song_returns_song(): void
    {
        $expectedSong = Song::factory()->make(['id' => 1]);

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($expectedSong);

        $result = $this->songService->getSong(1);

        $this->assertSame($expectedSong, $result);
    }

    /**
     * Test createSong without position.
     */
    public function test_create_song_without_position(): void
    {
        $songData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
        ];

        $expectedSong = Song::factory()->make($songData);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($songData)
            ->andReturn($expectedSong);

        $result = $this->songService->createSong($songData);

        $this->assertSame($expectedSong, $result);
    }

    /**
     * Test createSong with position adjusts positions.
     */
    public function test_create_song_with_position_adjusts_positions(): void
    {
        $songData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'position' => 3,
        ];

        $existingSong = Song::factory()->make(['id' => 1, 'position' => 3]);
        $expectedSong = Song::factory()->make($songData);

        $this->mockRepository
            ->shouldReceive('getByPosition')
            ->times(3) // positions 3, 4, 5
            ->with(Mockery::type('integer'))
            ->andReturn($existingSong, null, null);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($existingSong, ['position' => 4])
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($songData)
            ->andReturn($expectedSong);

        $result = $this->songService->createSong($songData);

        $this->assertSame($expectedSong, $result);
    }

    /**
     * Test createSong with position > 5 doesn't adjust positions.
     */
    public function test_create_song_with_position_greater_than_five(): void
    {
        $songData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'position' => 10,
        ];

        $expectedSong = Song::factory()->make($songData);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($songData)
            ->andReturn($expectedSong);

        $result = $this->songService->createSong($songData);

        $this->assertSame($expectedSong, $result);
    }

    /**
     * Test updateSong without position change.
     */
    public function test_update_song_without_position_change(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => 2]);
        $updateData = ['title' => 'Updated Title'];

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($song, $updateData)
            ->andReturn(true);

        $result = $this->songService->updateSong($song, $updateData);

        $this->assertTrue($result);
    }

    /**
     * Test updateSong with position change adjusts positions.
     */
    public function test_update_song_with_position_change(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => 2]);
        $updateData = ['title' => 'Updated Title', 'position' => 4];

        $existingSong = Song::factory()->make(['id' => 2, 'position' => 4]);

        $this->mockRepository
            ->shouldReceive('getByPosition')
            ->times(2) // positions 4, 5
            ->with(Mockery::type('integer'))
            ->andReturn($existingSong, null);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($existingSong, ['position' => 5])
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($song, $updateData)
            ->andReturn(true);

        $result = $this->songService->updateSong($song, $updateData);

        $this->assertTrue($result);
    }

    /**
     * Test deleteSong without position.
     */
    public function test_delete_song_without_position(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => null]);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($song)
            ->andReturn(true);

        $result = $this->songService->deleteSong($song);

        $this->assertTrue($result);
    }

    /**
     * Test deleteSong with position reorganizes top five.
     */
    public function test_delete_song_with_position_reorganizes_top_five(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => 3]);

        $topFiveSongs = new Collection([
            Song::factory()->make(['id' => 2, 'position' => 1]),
            Song::factory()->make(['id' => 3, 'position' => 2]),
            Song::factory()->make(['id' => 4, 'position' => 4]),
            Song::factory()->make(['id' => 5, 'position' => 5]),
        ]);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($song)
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('getTopFive')
            ->once()
            ->andReturn($topFiveSongs);

        // No updates needed as positions are already correct
        
        $result = $this->songService->deleteSong($song);

        $this->assertTrue($result);
    }

    /**
     * Test playSong increments play count.
     */
    public function test_play_song_increments_count(): void
    {
        $song = Song::factory()->make(['id' => 1, 'plays_count' => 10]);

        $this->mockRepository
            ->shouldReceive('incrementPlaysCount')
            ->once()
            ->with($song)
            ->andReturn(true);

        $result = $this->songService->playSong($song);

        $this->assertTrue($result);
    }

    /**
     * Test updateTopFivePositions with valid positions.
     */
    public function test_update_top_five_positions_with_valid_data(): void
    {
        $positions = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
        ];

        $this->mockRepository
            ->shouldReceive('updatePositions')
            ->once()
            ->with($positions);

        $this->songService->updateTopFivePositions($positions);
    }

    /**
     * Test updateTopFivePositions throws exception with wrong count.
     */
    public function test_update_top_five_positions_wrong_count(): void
    {
        $positions = [1 => 1, 2 => 2]; // Only 2 positions

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Must provide exactly 5 positions');

        $this->songService->updateTopFivePositions($positions);
    }

    /**
     * Test updateTopFivePositions throws exception with invalid positions.
     */
    public function test_update_top_five_positions_invalid_positions(): void
    {
        $positions = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 6, // Invalid position
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Positions must be 1, 2, 3, 4, 5');

        $this->songService->updateTopFivePositions($positions);
    }

    /**
     * Test searchSongs delegates to repository.
     */
    public function test_search_songs(): void
    {
        $query = 'test song';
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockRepository
            ->shouldReceive('searchByTitle')
            ->once()
            ->with($query, 15)
            ->andReturn($expectedPaginator);

        $result = $this->songService->searchSongs($query);

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test searchSongs with custom per page.
     */
    public function test_search_songs_custom_per_page(): void
    {
        $query = 'test song';
        $perPage = 25;
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockRepository
            ->shouldReceive('searchByTitle')
            ->once()
            ->with($query, $perPage)
            ->andReturn($expectedPaginator);

        $result = $this->songService->searchSongs($query, $perPage);

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test adjustPositions method behavior when excluding current song.
     */
    public function test_adjust_positions_excludes_current_song(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => 2]);
        $updateData = ['position' => 2]; // Same position, no change needed

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($song, $updateData)
            ->andReturn(true);

        $result = $this->songService->updateSong($song, $updateData);

        $this->assertTrue($result);
    }

    /**
     * Test reorganizeTopFive with gaps in positions.
     */
    public function test_reorganize_top_five_with_position_gaps(): void
    {
        $song = Song::factory()->make(['id' => 1, 'position' => 3]);

        $topFiveSongs = new Collection([
            Song::factory()->make(['id' => 2, 'position' => 1]),
            Song::factory()->make(['id' => 3, 'position' => 2]),
            Song::factory()->make(['id' => 4, 'position' => 5]), // Gap at position 4
        ]);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($song)
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('getTopFive')
            ->once()
            ->andReturn($topFiveSongs);

        // Should update the song at position 5 to position 3
        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($topFiveSongs[2], ['position' => 3])
            ->andReturn(true);

        $result = $this->songService->deleteSong($song);

        $this->assertTrue($result);
    }
}