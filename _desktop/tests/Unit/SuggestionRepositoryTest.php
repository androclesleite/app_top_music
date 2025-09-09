<?php

namespace Tests\Unit;

use App\Models\SongSuggestion;
use App\Models\User;
use App\Repositories\SuggestionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SuggestionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SuggestionRepository(new SongSuggestion());
    }

    /**
     * Test getPending returns only pending suggestions.
     */
    public function test_get_pending_returns_only_pending_suggestions(): void
    {
        SongSuggestion::factory()->pending()->count(3)->create();
        SongSuggestion::factory()->approved()->count(2)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        $result = $this->repository->getPending();

        $this->assertEquals(3, $result->total());
        
        foreach ($result->items() as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);
        }
    }

    /**
     * Test getPending orders by created_at descending.
     */
    public function test_get_pending_orders_by_created_at_desc(): void
    {
        $old = SongSuggestion::factory()->pending()->create([
            'title' => 'Old Suggestion',
            'created_at' => now()->subDays(2)
        ]);

        $new = SongSuggestion::factory()->pending()->create([
            'title' => 'New Suggestion',
            'created_at' => now()->subHours(1)
        ]);

        $middle = SongSuggestion::factory()->pending()->create([
            'title' => 'Middle Suggestion',
            'created_at' => now()->subDay()
        ]);

        $result = $this->repository->getPending();
        $items = $result->items();

        $this->assertEquals('New Suggestion', $items[0]->title);
        $this->assertEquals('Middle Suggestion', $items[1]->title);
        $this->assertEquals('Old Suggestion', $items[2]->title);
    }

    /**
     * Test getAll without status filter returns all suggestions.
     */
    public function test_get_all_without_status_returns_all(): void
    {
        SongSuggestion::factory()->pending()->count(2)->create();
        SongSuggestion::factory()->approved()->count(3)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        $result = $this->repository->getAll();

        $this->assertEquals(6, $result->total());
    }

    /**
     * Test getAll with status filter returns filtered suggestions.
     */
    public function test_get_all_with_status_filter(): void
    {
        SongSuggestion::factory()->pending()->count(2)->create();
        SongSuggestion::factory()->approved()->count(3)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        $pendingResult = $this->repository->getAll(15, SongSuggestion::STATUS_PENDING);
        $approvedResult = $this->repository->getAll(15, SongSuggestion::STATUS_APPROVED);
        $rejectedResult = $this->repository->getAll(15, SongSuggestion::STATUS_REJECTED);

        $this->assertEquals(2, $pendingResult->total());
        $this->assertEquals(3, $approvedResult->total());
        $this->assertEquals(1, $rejectedResult->total());

        foreach ($pendingResult->items() as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);
        }
    }

    /**
     * Test getAll orders by created_at descending.
     */
    public function test_get_all_orders_by_created_at_desc(): void
    {
        $old = SongSuggestion::factory()->create([
            'title' => 'Old',
            'created_at' => now()->subDays(2)
        ]);

        $new = SongSuggestion::factory()->create([
            'title' => 'New',
            'created_at' => now()->subHours(1)
        ]);

        $result = $this->repository->getAll();
        $items = $result->items();

        $this->assertEquals('New', $items[0]->title);
        $this->assertEquals('Old', $items[1]->title);
    }

    /**
     * Test find returns correct suggestion.
     */
    public function test_find_returns_correct_suggestion(): void
    {
        $suggestion = SongSuggestion::factory()->create(['title' => 'Test Suggestion']);

        $result = $this->repository->find($suggestion->id);

        $this->assertNotNull($result);
        $this->assertEquals($suggestion->id, $result->id);
        $this->assertEquals('Test Suggestion', $result->title);
    }

    /**
     * Test find returns null for non-existent suggestion.
     */
    public function test_find_returns_null_for_non_existent(): void
    {
        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    /**
     * Test create creates suggestion with given data.
     */
    public function test_create_creates_suggestion(): void
    {
        $data = [
            'title' => 'New Suggestion',
            'youtube_url' => 'https://youtube.com/watch?v=test',
            'suggested_by' => 'Test User',
            'status' => SongSuggestion::STATUS_PENDING,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(SongSuggestion::class, $result);
        $this->assertEquals('New Suggestion', $result->title);
        $this->assertEquals(SongSuggestion::STATUS_PENDING, $result->status);
        
        $this->assertDatabaseHas('song_suggestions', $data);
    }

    /**
     * Test update updates suggestion data.
     */
    public function test_update_updates_suggestion_data(): void
    {
        $suggestion = SongSuggestion::factory()->create(['title' => 'Original Title']);
        $updateData = ['title' => 'Updated Title'];

        $result = $this->repository->update($suggestion, $updateData);

        $this->assertTrue($result);
        
        $suggestion->refresh();
        $this->assertEquals('Updated Title', $suggestion->title);
    }

    /**
     * Test approve updates suggestion to approved status.
     */
    public function test_approve_updates_suggestion_status(): void
    {
        $reviewer = User::factory()->create();
        $suggestion = SongSuggestion::factory()->pending()->create();

        $result = $this->repository->approve($suggestion, $reviewer->id);

        $this->assertTrue($result);
        
        $suggestion->refresh();
        $this->assertEquals(SongSuggestion::STATUS_APPROVED, $suggestion->status);
        $this->assertEquals($reviewer->id, $suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
    }

    /**
     * Test reject updates suggestion to rejected status.
     */
    public function test_reject_updates_suggestion_status(): void
    {
        $reviewer = User::factory()->create();
        $suggestion = SongSuggestion::factory()->pending()->create();

        $result = $this->repository->reject($suggestion, $reviewer->id);

        $this->assertTrue($result);
        
        $suggestion->refresh();
        $this->assertEquals(SongSuggestion::STATUS_REJECTED, $suggestion->status);
        $this->assertEquals($reviewer->id, $suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
    }

    /**
     * Test getByStatus returns suggestions with specific status.
     */
    public function test_get_by_status_returns_filtered_suggestions(): void
    {
        SongSuggestion::factory()->pending()->count(2)->create();
        SongSuggestion::factory()->approved()->count(3)->create();
        SongSuggestion::factory()->rejected()->count(1)->create();

        $pendingResult = $this->repository->getByStatus(SongSuggestion::STATUS_PENDING);
        $approvedResult = $this->repository->getByStatus(SongSuggestion::STATUS_APPROVED);
        $rejectedResult = $this->repository->getByStatus(SongSuggestion::STATUS_REJECTED);

        $this->assertCount(2, $pendingResult);
        $this->assertCount(3, $approvedResult);
        $this->assertCount(1, $rejectedResult);

        foreach ($pendingResult as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);
        }
    }

    /**
     * Test getRecentApproved returns approved suggestions ordered by reviewed_at.
     */
    public function test_get_recent_approved_returns_ordered_approved(): void
    {
        // Create some pending and rejected to verify they're not included
        SongSuggestion::factory()->pending()->create();
        SongSuggestion::factory()->rejected()->create();

        // Create approved suggestions with different review dates
        $old = SongSuggestion::factory()->approved()->create([
            'title' => 'Old Approved',
            'reviewed_at' => now()->subDays(3)
        ]);

        $new = SongSuggestion::factory()->approved()->create([
            'title' => 'New Approved',
            'reviewed_at' => now()->subHours(1)
        ]);

        $middle = SongSuggestion::factory()->approved()->create([
            'title' => 'Middle Approved',
            'reviewed_at' => now()->subDay()
        ]);

        $result = $this->repository->getRecentApproved(5);

        $this->assertCount(3, $result);
        $this->assertEquals('New Approved', $result->first()->title);
        $this->assertEquals('Middle Approved', $result->get(1)->title);
        $this->assertEquals('Old Approved', $result->last()->title);
    }

    /**
     * Test getRecentApproved respects limit parameter.
     */
    public function test_get_recent_approved_respects_limit(): void
    {
        SongSuggestion::factory()->approved()->count(5)->create();

        $result = $this->repository->getRecentApproved(3);

        $this->assertCount(3, $result);
    }

    /**
     * Test searchByTitle finds suggestions with matching title.
     */
    public function test_search_by_title_finds_matching_suggestions(): void
    {
        SongSuggestion::factory()->create(['title' => 'Amazing Grace']);
        SongSuggestion::factory()->create(['title' => 'Graceful Song']);
        SongSuggestion::factory()->create(['title' => 'Another Song']);
        SongSuggestion::factory()->create(['title' => 'Grace Notes']);

        $result = $this->repository->searchByTitle('grace', 10);

        $this->assertEquals(3, $result->total());
        
        foreach ($result->items() as $suggestion) {
            $this->assertStringContainsStringIgnoringCase('grace', $suggestion->title);
        }
    }

    /**
     * Test searchByTitle orders by created_at descending.
     */
    public function test_search_by_title_orders_by_created_at_desc(): void
    {
        $old = SongSuggestion::factory()->create([
            'title' => 'Test Song A',
            'created_at' => now()->subDays(2)
        ]);

        $new = SongSuggestion::factory()->create([
            'title' => 'Test Song B',
            'created_at' => now()->subHours(1)
        ]);

        $result = $this->repository->searchByTitle('test', 10);
        $items = $result->items();

        $this->assertEquals('Test Song B', $items[0]->title);
        $this->assertEquals('Test Song A', $items[1]->title);
    }

    /**
     * Test searchByTitle returns empty results for non-matching query.
     */
    public function test_search_by_title_returns_empty_for_no_matches(): void
    {
        SongSuggestion::factory()->create(['title' => 'Amazing Grace']);
        SongSuggestion::factory()->create(['title' => 'Another Song']);

        $result = $this->repository->searchByTitle('nonexistent', 10);

        $this->assertEquals(0, $result->total());
        $this->assertEmpty($result->items());
    }

    /**
     * Test pagination works correctly for getPending.
     */
    public function test_get_pending_pagination(): void
    {
        SongSuggestion::factory()->pending()->count(25)->create();

        $result = $this->repository->getPending(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    /**
     * Test getAll pagination with custom per page.
     */
    public function test_get_all_custom_pagination(): void
    {
        SongSuggestion::factory()->count(30)->create();

        $result = $this->repository->getAll(20);

        $this->assertEquals(20, $result->perPage());
        $this->assertEquals(30, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test approve sets review timestamp.
     */
    public function test_approve_sets_review_timestamp(): void
    {
        $reviewer = User::factory()->create();
        $suggestion = SongSuggestion::factory()->pending()->create(['reviewed_at' => null]);

        $beforeTime = now()->subSecond();
        $this->repository->approve($suggestion, $reviewer->id);
        $afterTime = now()->addSecond();

        $suggestion->refresh();
        $this->assertNotNull($suggestion->reviewed_at);
        $this->assertTrue($suggestion->reviewed_at->between($beforeTime, $afterTime));
    }

    /**
     * Test reject sets review timestamp.
     */
    public function test_reject_sets_review_timestamp(): void
    {
        $reviewer = User::factory()->create();
        $suggestion = SongSuggestion::factory()->pending()->create(['reviewed_at' => null]);

        $beforeTime = now()->subSecond();
        $this->repository->reject($suggestion, $reviewer->id);
        $afterTime = now()->addSecond();

        $suggestion->refresh();
        $this->assertNotNull($suggestion->reviewed_at);
        $this->assertTrue($suggestion->reviewed_at->between($beforeTime, $afterTime));
    }

    /**
     * Test getByStatus returns empty collection for non-existent status.
     */
    public function test_get_by_status_returns_empty_for_non_existent_status(): void
    {
        SongSuggestion::factory()->pending()->count(5)->create();

        $result = $this->repository->getByStatus('non_existent_status');

        $this->assertCount(0, $result);
    }

    /**
     * Test getRecentApproved returns empty collection when no approved suggestions.
     */
    public function test_get_recent_approved_returns_empty_when_none_approved(): void
    {
        SongSuggestion::factory()->pending()->count(3)->create();
        SongSuggestion::factory()->rejected()->count(2)->create();

        $result = $this->repository->getRecentApproved();

        $this->assertCount(0, $result);
    }
}