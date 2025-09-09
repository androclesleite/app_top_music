<?php

namespace Tests\Unit;

use App\Models\Song;
use App\Repositories\SongRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SongRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SongRepository(new Song());
    }

    /**
     * Test getTopFive returns songs with positions 1-5.
     */
    public function test_get_top_five_returns_correct_songs(): void
    {
        // Create top 5 songs
        Song::factory()->create(['title' => 'Song 1', 'position' => 1]);
        Song::factory()->create(['title' => 'Song 2', 'position' => 2]);
        Song::factory()->create(['title' => 'Song 3', 'position' => 3]);
        Song::factory()->create(['title' => 'Song 4', 'position' => 4]);
        Song::factory()->create(['title' => 'Song 5', 'position' => 5]);

        // Create other songs that shouldn't be included
        Song::factory()->create(['title' => 'Song 6', 'position' => 6]);
        Song::factory()->create(['title' => 'Song Other', 'position' => null]);

        $result = $this->repository->getTopFive();

        $this->assertCount(5, $result);
        
        // Verify they are ordered by position
        $positions = $result->pluck('position')->toArray();
        $this->assertEquals([1, 2, 3, 4, 5], $positions);
    }

    /**
     * Test getTopFive returns empty collection when no top five songs exist.
     */
    public function test_get_top_five_returns_empty_when_no_top_five(): void
    {
        // Create songs without top 5 positions
        Song::factory()->count(3)->create(['position' => null]);
        Song::factory()->create(['position' => 10]);

        $result = $this->repository->getTopFive();

        $this->assertCount(0, $result);
    }

    /**
     * Test getOthers returns songs not in top 5 with pagination.
     */
    public function test_get_others_returns_non_top_five_songs(): void
    {
        // Create top 5 songs
        Song::factory()->count(5)->create(['position' => 1]);

        // Create other songs
        Song::factory()->count(10)->create(['position' => null, 'plays_count' => 100]);
        Song::factory()->count(5)->create(['position' => 10, 'plays_count' => 200]);

        $result = $this->repository->getOthers(20);

        $this->assertEquals(15, $result->total());
        $this->assertEquals(20, $result->perPage());
        
        // Verify none of the returned songs are in top 5
        foreach ($result->items() as $song) {
            $this->assertTrue($song->position === null || $song->position > 5);
        }
    }

    /**
     * Test getOthers orders by plays count descending.
     */
    public function test_get_others_orders_by_plays_count(): void
    {
        Song::factory()->create(['position' => null, 'plays_count' => 50]);
        Song::factory()->create(['position' => null, 'plays_count' => 200]);
        Song::factory()->create(['position' => null, 'plays_count' => 100]);

        $result = $this->repository->getOthers();
        $items = $result->items();

        $this->assertEquals(200, $items[0]->plays_count);
        $this->assertEquals(100, $items[1]->plays_count);
        $this->assertEquals(50, $items[2]->plays_count);
    }

    /**
     * Test find returns correct song.
     */
    public function test_find_returns_correct_song(): void
    {
        $song = Song::factory()->create(['title' => 'Test Song']);

        $result = $this->repository->find($song->id);

        $this->assertNotNull($result);
        $this->assertEquals($song->id, $result->id);
        $this->assertEquals('Test Song', $result->title);
    }

    /**
     * Test find returns null for non-existent song.
     */
    public function test_find_returns_null_for_non_existent(): void
    {
        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    /**
     * Test create creates song with given data.
     */
    public function test_create_creates_song(): void
    {
        $data = [
            'title' => 'New Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'position' => 3,
            'plays_count' => 0,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Song::class, $result);
        $this->assertEquals('New Song', $result->title);
        $this->assertEquals(3, $result->position);
        
        $this->assertDatabaseHas('songs', $data);
    }

    /**
     * Test update updates song data.
     */
    public function test_update_updates_song_data(): void
    {
        $song = Song::factory()->create(['title' => 'Original Title']);
        $updateData = ['title' => 'Updated Title'];

        $result = $this->repository->update($song, $updateData);

        $this->assertTrue($result);
        
        $song->refresh();
        $this->assertEquals('Updated Title', $song->title);
    }

    /**
     * Test delete removes song from database.
     */
    public function test_delete_removes_song(): void
    {
        $song = Song::factory()->create();

        $result = $this->repository->delete($song);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }

    /**
     * Test incrementPlaysCount increases play count.
     */
    public function test_increment_plays_count(): void
    {
        $song = Song::factory()->create(['plays_count' => 10]);

        $result = $this->repository->incrementPlaysCount($song);

        $this->assertTrue($result);
        
        $song->refresh();
        $this->assertEquals(11, $song->plays_count);
    }

    /**
     * Test updatePositions updates multiple songs.
     */
    public function test_update_positions_updates_multiple_songs(): void
    {
        $song1 = Song::factory()->create(['position' => 1]);
        $song2 = Song::factory()->create(['position' => 2]);
        $song3 = Song::factory()->create(['position' => 3]);

        $positions = [
            $song1->id => 3,
            $song2->id => 1,
            $song3->id => 2,
        ];

        $this->repository->updatePositions($positions);

        $song1->refresh();
        $song2->refresh();
        $song3->refresh();

        $this->assertEquals(3, $song1->position);
        $this->assertEquals(1, $song2->position);
        $this->assertEquals(2, $song3->position);
    }

    /**
     * Test getByPosition returns song at specific position.
     */
    public function test_get_by_position_returns_correct_song(): void
    {
        Song::factory()->create(['title' => 'Position 1', 'position' => 1]);
        Song::factory()->create(['title' => 'Position 2', 'position' => 2]);
        Song::factory()->create(['title' => 'Position 3', 'position' => 3]);

        $result = $this->repository->getByPosition(2);

        $this->assertNotNull($result);
        $this->assertEquals('Position 2', $result->title);
        $this->assertEquals(2, $result->position);
    }

    /**
     * Test getByPosition returns null when no song at position.
     */
    public function test_get_by_position_returns_null_when_not_found(): void
    {
        Song::factory()->create(['position' => 1]);
        Song::factory()->create(['position' => 3]);

        $result = $this->repository->getByPosition(2);

        $this->assertNull($result);
    }

    /**
     * Test searchByTitle finds songs with matching title.
     */
    public function test_search_by_title_finds_matching_songs(): void
    {
        Song::factory()->create(['title' => 'Amazing Grace']);
        Song::factory()->create(['title' => 'Graceful Song']);
        Song::factory()->create(['title' => 'Another Song']);
        Song::factory()->create(['title' => 'Grace Notes']);

        $result = $this->repository->searchByTitle('grace', 10);

        $this->assertEquals(3, $result->total());
        
        foreach ($result->items() as $song) {
            $this->assertStringContainsStringIgnoringCase('grace', $song->title);
        }
    }

    /**
     * Test searchByTitle orders by plays count descending.
     */
    public function test_search_by_title_orders_by_plays_count(): void
    {
        Song::factory()->create(['title' => 'Test Song A', 'plays_count' => 50]);
        Song::factory()->create(['title' => 'Test Song B', 'plays_count' => 200]);
        Song::factory()->create(['title' => 'Test Song C', 'plays_count' => 100]);

        $result = $this->repository->searchByTitle('test', 10);
        $items = $result->items();

        $this->assertEquals(200, $items[0]->plays_count);
        $this->assertEquals(100, $items[1]->plays_count);
        $this->assertEquals(50, $items[2]->plays_count);
    }

    /**
     * Test searchByTitle returns empty results for non-matching query.
     */
    public function test_search_by_title_returns_empty_for_no_matches(): void
    {
        Song::factory()->create(['title' => 'Amazing Grace']);
        Song::factory()->create(['title' => 'Another Song']);

        $result = $this->repository->searchByTitle('nonexistent', 10);

        $this->assertEquals(0, $result->total());
        $this->assertEmpty($result->items());
    }

    /**
     * Test repository uses correct pagination for getOthers.
     */
    public function test_get_others_pagination(): void
    {
        Song::factory()->count(25)->create(['position' => null]);

        $result = $this->repository->getOthers(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
        $this->assertEquals(1, $result->currentPage());
    }

    /**
     * Test repository correctly handles mixed positions in getOthers.
     */
    public function test_get_others_handles_mixed_positions(): void
    {
        // Create mix of top 5, null, and > 5 positions
        Song::factory()->count(3)->sequence(
            ['position' => 1],
            ['position' => 2],
            ['position' => 3]
        )->create();

        Song::factory()->count(2)->create(['position' => null]);
        Song::factory()->count(2)->sequence(
            ['position' => 6],
            ['position' => 10]
        )->create();

        $result = $this->repository->getOthers();

        // Should return 4 songs (2 null + 2 > 5)
        $this->assertEquals(4, $result->total());
        
        foreach ($result->items() as $song) {
            $this->assertTrue($song->position === null || $song->position > 5);
        }
    }

    /**
     * Test getTopFive handles partial top 5 list.
     */
    public function test_get_top_five_handles_partial_list(): void
    {
        // Create only first 3 positions
        Song::factory()->create(['position' => 1, 'title' => 'First']);
        Song::factory()->create(['position' => 3, 'title' => 'Third']);
        Song::factory()->create(['position' => 5, 'title' => 'Fifth']);

        $result = $this->repository->getTopFive();

        $this->assertCount(3, $result);
        $this->assertEquals([1, 3, 5], $result->pluck('position')->toArray());
    }

    /**
     * Test increment plays count multiple times.
     */
    public function test_increment_plays_count_multiple_times(): void
    {
        $song = Song::factory()->create(['plays_count' => 0]);

        $this->repository->incrementPlaysCount($song);
        $this->repository->incrementPlaysCount($song);
        $this->repository->incrementPlaysCount($song);

        $song->refresh();
        $this->assertEquals(3, $song->plays_count);
    }
}