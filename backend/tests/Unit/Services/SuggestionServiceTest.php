<?php

namespace Tests\Unit\Services;

use App\Models\SongSuggestion;
use App\Services\SuggestionService;
use App\Repositories\SuggestionRepository;
use Tests\TestCase;
use Mockery;

class SuggestionServiceTest extends TestCase
{
    protected SuggestionService $suggestionService;
    protected $suggestionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suggestionRepository = Mockery::mock(SuggestionRepository::class);
        $this->suggestionService = new SuggestionService($this->suggestionRepository);
    }

    public function test_can_get_paginated_suggestions()
    {
        $suggestions = SongSuggestion::factory()->count(5)->make();
        $paginatedData = [
            'data' => $suggestions,
            'current_page' => 1,
            'per_page' => 15,
            'total' => 5,
            'last_page' => 1
        ];

        $this->suggestionRepository
            ->shouldReceive('getPaginated')
            ->once()
            ->with(1, 15, [])
            ->andReturn($paginatedData);

        $result = $this->suggestionService->getPaginatedSuggestions(1, 15, []);

        $this->assertEquals($paginatedData, $result);
    }

    public function test_can_create_suggestion()
    {
        $suggestionData = [
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'youtube_url' => 'https://www.youtube.com/watch?v=test123',
            'youtube_video_id' => 'test123',
            'thumbnail_url' => 'https://img.youtube.com/vi/test123/hqdefault.jpg',
            'suggested_by_name' => 'John Doe',
            'suggested_by_email' => 'john@example.com',
            'status' => 'pending'
        ];

        $suggestion = SongSuggestion::factory()->make($suggestionData);

        $this->suggestionRepository
            ->shouldReceive('create')
            ->once()
            ->with($suggestionData)
            ->andReturn($suggestion);

        $result = $this->suggestionService->createSuggestion($suggestionData);

        $this->assertEquals($suggestion, $result);
    }

    public function test_can_update_suggestion()
    {
        $suggestion = SongSuggestion::factory()->make(['id' => 1]);
        $updateData = [
            'status' => 'approved',
            'admin_notes' => 'Approved by admin'
        ];

        $this->suggestionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($suggestion);

        $this->suggestionRepository
            ->shouldReceive('update')
            ->once()
            ->with($suggestion, $updateData)
            ->andReturn($suggestion);

        $result = $this->suggestionService->updateSuggestion(1, $updateData);

        $this->assertEquals($suggestion, $result);
    }

    public function test_can_approve_suggestion()
    {
        $suggestion = SongSuggestion::factory()->make([
            'id' => 1,
            'status' => 'pending'
        ]);

        $this->suggestionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($suggestion);

        $this->suggestionRepository
            ->shouldReceive('approve')
            ->once()
            ->with($suggestion, 'Approved')
            ->andReturn($suggestion);

        $result = $this->suggestionService->approveSuggestion(1, 'Approved');

        $this->assertEquals($suggestion, $result);
    }

    public function test_can_reject_suggestion()
    {
        $suggestion = SongSuggestion::factory()->make([
            'id' => 1,
            'status' => 'pending'
        ]);

        $this->suggestionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($suggestion);

        $this->suggestionRepository
            ->shouldReceive('reject')
            ->once()
            ->with($suggestion, 'Rejected')
            ->andReturn($suggestion);

        $result = $this->suggestionService->rejectSuggestion(1, 'Rejected');

        $this->assertEquals($suggestion, $result);
    }

    public function test_can_delete_suggestion()
    {
        $suggestion = SongSuggestion::factory()->make(['id' => 1]);

        $this->suggestionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($suggestion);

        $this->suggestionRepository
            ->shouldReceive('delete')
            ->once()
            ->with($suggestion)
            ->andReturn(true);

        $result = $this->suggestionService->deleteSuggestion(1);

        $this->assertTrue($result);
    }

    public function test_can_get_recent_suggestions()
    {
        $suggestions = SongSuggestion::factory()->count(5)->make();

        $this->suggestionRepository
            ->shouldReceive('getRecent')
            ->once()
            ->with(10)
            ->andReturn($suggestions);

        $result = $this->suggestionService->getRecentSuggestions(10);

        $this->assertEquals($suggestions, $result);
    }

    public function test_can_get_suggestions_by_status()
    {
        $suggestions = SongSuggestion::factory()->count(3)->make();

        $this->suggestionRepository
            ->shouldReceive('getByStatus')
            ->once()
            ->with('pending')
            ->andReturn($suggestions);

        $result = $this->suggestionService->getSuggestionsByStatus('pending');

        $this->assertEquals($suggestions, $result);
    }

    public function test_throws_exception_when_suggestion_not_found()
    {
        $this->suggestionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sugestão não encontrada');

        $this->suggestionService->updateSuggestion(999, []);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}