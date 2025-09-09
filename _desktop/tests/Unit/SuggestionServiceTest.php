<?php

namespace Tests\Unit;

use App\Models\Song;
use App\Models\SongSuggestion;
use App\Repositories\SongRepository;
use App\Repositories\SuggestionRepository;
use App\Services\SuggestionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class SuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SuggestionService $suggestionService;
    private SuggestionRepository $mockSuggestionRepository;
    private SongRepository $mockSongRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockSuggestionRepository = Mockery::mock(SuggestionRepository::class);
        $this->mockSongRepository = Mockery::mock(SongRepository::class);
        $this->suggestionService = new SuggestionService(
            $this->mockSuggestionRepository,
            $this->mockSongRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getAllSuggestions without status filter.
     */
    public function test_get_all_suggestions_without_status(): void
    {
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockSuggestionRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(15, null)
            ->andReturn($expectedPaginator);

        $result = $this->suggestionService->getAllSuggestions();

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getAllSuggestions with status filter.
     */
    public function test_get_all_suggestions_with_status(): void
    {
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockSuggestionRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(10, 'pending')
            ->andReturn($expectedPaginator);

        $result = $this->suggestionService->getAllSuggestions(10, 'pending');

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getPendingSuggestions.
     */
    public function test_get_pending_suggestions(): void
    {
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockSuggestionRepository
            ->shouldReceive('getPending')
            ->once()
            ->with(15)
            ->andReturn($expectedPaginator);

        $result = $this->suggestionService->getPendingSuggestions();

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getSuggestion.
     */
    public function test_get_suggestion(): void
    {
        $expectedSuggestion = SongSuggestion::factory()->make(['id' => 1]);

        $this->mockSuggestionRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($expectedSuggestion);

        $result = $this->suggestionService->getSuggestion(1);

        $this->assertSame($expectedSuggestion, $result);
    }

    /**
     * Test createSuggestion with valid data.
     */
    public function test_create_suggestion_with_valid_data(): void
    {
        $suggestionData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'suggested_by' => 'Test User',
        ];

        $expectedSuggestion = SongSuggestion::factory()->make($suggestionData);

        // Mock database checks for uniqueness
        SongSuggestion::shouldReceive('where->first')->andReturn(null);
        Song::shouldReceive('where->first')->andReturn(null);

        $this->mockSuggestionRepository
            ->shouldReceive('create')
            ->once()
            ->with(array_merge($suggestionData, ['status' => SongSuggestion::STATUS_PENDING]))
            ->andReturn($expectedSuggestion);

        $result = $this->suggestionService->createSuggestion($suggestionData);

        $this->assertSame($expectedSuggestion, $result);
    }

    /**
     * Test createSuggestion throws exception for duplicate URL in suggestions.
     */
    public function test_create_suggestion_throws_exception_for_duplicate_suggestion_url(): void
    {
        $suggestionData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=duplicate',
            'suggested_by' => 'Test User',
        ];

        $existingSuggestion = SongSuggestion::factory()->make(['youtube_url' => $suggestionData['youtube_url']]);

        // Mock existing suggestion found
        SongSuggestion::shouldReceive('where->first')->andReturn($existingSuggestion);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A suggestion with this YouTube URL already exists');

        $this->suggestionService->createSuggestion($suggestionData);
    }

    /**
     * Test createSuggestion throws exception for duplicate URL in songs.
     */
    public function test_create_suggestion_throws_exception_for_duplicate_song_url(): void
    {
        $suggestionData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=existing',
            'suggested_by' => 'Test User',
        ];

        $existingSong = Song::factory()->make(['youtube_url' => $suggestionData['youtube_url']]);

        // Mock no existing suggestion but existing song
        SongSuggestion::shouldReceive('where->first')->andReturn(null);
        Song::shouldReceive('where->first')->andReturn($existingSong);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A song with this YouTube URL already exists');

        $this->suggestionService->createSuggestion($suggestionData);
    }

    /**
     * Test approveSuggestion creates song.
     */
    public function test_approve_suggestion_creates_song(): void
    {
        $suggestion = SongSuggestion::factory()->make([
            'id' => 1,
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'status' => SongSuggestion::STATUS_PENDING,
        ]);

        $reviewerId = 123;
        $expectedSong = Song::factory()->make([
            'title' => $suggestion->title,
            'youtube_url' => $suggestion->youtube_url,
            'position' => null,
            'plays_count' => 0,
        ]);

        $this->mockSuggestionRepository
            ->shouldReceive('approve')
            ->once()
            ->with($suggestion, $reviewerId)
            ->andReturn(true);

        $this->mockSongRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'title' => $suggestion->title,
                'youtube_url' => $suggestion->youtube_url,
                'position' => null,
                'plays_count' => 0,
            ])
            ->andReturn($expectedSong);

        $result = $this->suggestionService->approveSuggestion($suggestion, $reviewerId);

        $this->assertSame($expectedSong, $result);
    }

    /**
     * Test approveSuggestion throws exception for non-pending suggestion.
     */
    public function test_approve_suggestion_throws_exception_for_non_pending(): void
    {
        $suggestion = SongSuggestion::factory()->make([
            'status' => SongSuggestion::STATUS_APPROVED,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending suggestions can be approved');

        $this->suggestionService->approveSuggestion($suggestion, 123);
    }

    /**
     * Test rejectSuggestion updates status.
     */
    public function test_reject_suggestion_updates_status(): void
    {
        $suggestion = SongSuggestion::factory()->make([
            'status' => SongSuggestion::STATUS_PENDING,
        ]);

        $reviewerId = 123;

        $this->mockSuggestionRepository
            ->shouldReceive('reject')
            ->once()
            ->with($suggestion, $reviewerId)
            ->andReturn(true);

        $result = $this->suggestionService->rejectSuggestion($suggestion, $reviewerId);

        $this->assertTrue($result);
    }

    /**
     * Test rejectSuggestion throws exception for non-pending suggestion.
     */
    public function test_reject_suggestion_throws_exception_for_non_pending(): void
    {
        $suggestion = SongSuggestion::factory()->make([
            'status' => SongSuggestion::STATUS_REJECTED,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending suggestions can be rejected');

        $this->suggestionService->rejectSuggestion($suggestion, 123);
    }

    /**
     * Test getRecentApprovedSuggestions.
     */
    public function test_get_recent_approved_suggestions(): void
    {
        $expectedCollection = new Collection([
            SongSuggestion::factory()->make(['status' => SongSuggestion::STATUS_APPROVED]),
        ]);

        $this->mockSuggestionRepository
            ->shouldReceive('getRecentApproved')
            ->once()
            ->with(10)
            ->andReturn($expectedCollection);

        $result = $this->suggestionService->getRecentApprovedSuggestions();

        $this->assertSame($expectedCollection, $result);
    }

    /**
     * Test getRecentApprovedSuggestions with custom limit.
     */
    public function test_get_recent_approved_suggestions_custom_limit(): void
    {
        $expectedCollection = new Collection([
            SongSuggestion::factory()->make(['status' => SongSuggestion::STATUS_APPROVED]),
        ]);

        $this->mockSuggestionRepository
            ->shouldReceive('getRecentApproved')
            ->once()
            ->with(5)
            ->andReturn($expectedCollection);

        $result = $this->suggestionService->getRecentApprovedSuggestions(5);

        $this->assertSame($expectedCollection, $result);
    }

    /**
     * Test searchSuggestions.
     */
    public function test_search_suggestions(): void
    {
        $query = 'test song';
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockSuggestionRepository
            ->shouldReceive('searchByTitle')
            ->once()
            ->with($query, 15)
            ->andReturn($expectedPaginator);

        $result = $this->suggestionService->searchSuggestions($query);

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test searchSuggestions with custom per page.
     */
    public function test_search_suggestions_custom_per_page(): void
    {
        $query = 'test song';
        $perPage = 25;
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockSuggestionRepository
            ->shouldReceive('searchByTitle')
            ->once()
            ->with($query, $perPage)
            ->andReturn($expectedPaginator);

        $result = $this->suggestionService->searchSuggestions($query, $perPage);

        $this->assertSame($expectedPaginator, $result);
    }

    /**
     * Test getSuggestionStats returns correct format.
     */
    public function test_get_suggestion_stats(): void
    {
        $pendingCollection = new Collection([
            SongSuggestion::factory()->make(['status' => SongSuggestion::STATUS_PENDING]),
            SongSuggestion::factory()->make(['status' => SongSuggestion::STATUS_PENDING]),
        ]);

        $approvedCollection = new Collection([
            SongSuggestion::factory()->make(['status' => SongSuggestion::STATUS_APPROVED]),
        ]);

        $rejectedCollection = new Collection([]);

        $this->mockSuggestionRepository
            ->shouldReceive('getByStatus')
            ->once()
            ->with(SongSuggestion::STATUS_PENDING)
            ->andReturn($pendingCollection);

        $this->mockSuggestionRepository
            ->shouldReceive('getByStatus')
            ->once()
            ->with(SongSuggestion::STATUS_APPROVED)
            ->andReturn($approvedCollection);

        $this->mockSuggestionRepository
            ->shouldReceive('getByStatus')
            ->once()
            ->with(SongSuggestion::STATUS_REJECTED)
            ->andReturn($rejectedCollection);

        $result = $this->suggestionService->getSuggestionStats();

        $this->assertEquals([
            'pending' => 2,
            'approved' => 1,
            'rejected' => 0,
        ], $result);
    }

    /**
     * Test suggestion creation sets pending status automatically.
     */
    public function test_create_suggestion_sets_pending_status(): void
    {
        $suggestionData = [
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'suggested_by' => 'Test User',
        ];

        SongSuggestion::shouldReceive('where->first')->andReturn(null);
        Song::shouldReceive('where->first')->andReturn(null);

        $this->mockSuggestionRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['status'] === SongSuggestion::STATUS_PENDING;
            }))
            ->andReturn(SongSuggestion::factory()->make());

        $this->suggestionService->createSuggestion($suggestionData);
    }

    /**
     * Test approved suggestion creates song with correct defaults.
     */
    public function test_approved_suggestion_creates_song_with_defaults(): void
    {
        $suggestion = SongSuggestion::factory()->make([
            'title' => 'Test Song',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'status' => SongSuggestion::STATUS_PENDING,
        ]);

        $this->mockSuggestionRepository
            ->shouldReceive('approve')
            ->once()
            ->andReturn(true);

        $this->mockSongRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['position'] === null && $data['plays_count'] === 0;
            }))
            ->andReturn(Song::factory()->make());

        $this->suggestionService->approveSuggestion($suggestion, 123);
    }
}