<?php

namespace Tests\Unit\Models;

use App\Models\SongSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongSuggestionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SongSuggestion model has correct fillable attributes.
     */
    public function test_song_suggestion_has_correct_fillable_attributes(): void
    {
        $suggestion = new SongSuggestion();
        $expected = [
            'title',
            'youtube_url',
            'status',
            'suggested_by',
            'reviewed_by',
            'reviewed_at',
        ];

        $this->assertEquals($expected, $suggestion->getFillable());
    }

    /**
     * Test SongSuggestion model casts attributes correctly.
     */
    public function test_song_suggestion_casts_attributes_correctly(): void
    {
        $user = User::factory()->create();
        $suggestion = SongSuggestion::factory()->create([
            'reviewed_at' => '2023-01-01 12:00:00',
            'reviewed_by' => $user->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suggestion->reviewed_at);
    }

    /**
     * Test SongSuggestion model has correct status constants.
     */
    public function test_song_suggestion_has_correct_status_constants(): void
    {
        $this->assertEquals('pending', SongSuggestion::STATUS_PENDING);
        $this->assertEquals('approved', SongSuggestion::STATUS_APPROVED);
        $this->assertEquals('rejected', SongSuggestion::STATUS_REJECTED);
    }

    /**
     * Test SongSuggestion has reviewedBy relationship.
     */
    public function test_song_suggestion_has_reviewed_by_relationship(): void
    {
        $suggestion = new SongSuggestion();
        $relationship = $suggestion->reviewedBy();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('reviewed_by', $relationship->getForeignKeyName());
        $this->assertEquals('id', $relationship->getOwnerKeyName());
    }

    /**
     * Test reviewedBy relationship returns correct user.
     */
    public function test_reviewed_by_relationship_returns_correct_user(): void
    {
        $reviewer = User::factory()->create(['name' => 'Reviewer User']);
        $suggestion = SongSuggestion::factory()->create(['reviewed_by' => $reviewer->id]);

        $this->assertInstanceOf(User::class, $suggestion->reviewedBy);
        $this->assertEquals($reviewer->id, $suggestion->reviewedBy->id);
        $this->assertEquals('Reviewer User', $suggestion->reviewedBy->name);
    }

    /**
     * Test pending scope filters correctly.
     */
    public function test_pending_scope_filters_correctly(): void
    {
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_PENDING, 'title' => 'Pending 1']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_PENDING, 'title' => 'Pending 2']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_APPROVED, 'title' => 'Approved']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_REJECTED, 'title' => 'Rejected']);

        $pendingSuggestions = SongSuggestion::pending()->get();

        $this->assertCount(2, $pendingSuggestions);
        foreach ($pendingSuggestions as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);
        }
    }

    /**
     * Test approved scope filters correctly.
     */
    public function test_approved_scope_filters_correctly(): void
    {
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_PENDING]);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_APPROVED, 'title' => 'Approved 1']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_APPROVED, 'title' => 'Approved 2']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_REJECTED]);

        $approvedSuggestions = SongSuggestion::approved()->get();

        $this->assertCount(2, $approvedSuggestions);
        foreach ($approvedSuggestions as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_APPROVED, $suggestion->status);
        }
    }

    /**
     * Test rejected scope filters correctly.
     */
    public function test_rejected_scope_filters_correctly(): void
    {
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_PENDING]);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_APPROVED]);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_REJECTED, 'title' => 'Rejected 1']);
        SongSuggestion::factory()->create(['status' => SongSuggestion::STATUS_REJECTED, 'title' => 'Rejected 2']);

        $rejectedSuggestions = SongSuggestion::rejected()->get();

        $this->assertCount(2, $rejectedSuggestions);
        foreach ($rejectedSuggestions as $suggestion) {
            $this->assertEquals(SongSuggestion::STATUS_REJECTED, $suggestion->status);
        }
    }

    /**
     * Test YouTube thumbnail attribute generates correct URL.
     */
    public function test_youtube_thumbnail_attribute_generates_correct_url(): void
    {
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
            $suggestion = SongSuggestion::factory()->create(['youtube_url' => $testCase['url']]);
            
            $expectedThumbnail = "https://img.youtube.com/vi/{$testCase['expected_id']}/hqdefault.jpg";
            $this->assertEquals($expectedThumbnail, $suggestion->youtube_thumbnail);
        }
    }

    /**
     * Test YouTube thumbnail attribute returns placeholder for invalid URL.
     */
    public function test_youtube_thumbnail_returns_placeholder_for_invalid_url(): void
    {
        $suggestion = SongSuggestion::factory()->create(['youtube_url' => 'https://invalid-url.com/video']);
        
        $expectedPlaceholder = 'https://via.placeholder.com/480x360/cccccc/666666?text=No+Image';
        $this->assertEquals($expectedPlaceholder, $suggestion->youtube_thumbnail);
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
        ];

        foreach ($testCases as $url => $expectedId) {
            $suggestion = SongSuggestion::factory()->create(['youtube_url' => $url]);
            $this->assertEquals($expectedId, $suggestion->youtube_video_id);
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
        ];

        foreach ($invalidUrls as $url) {
            $suggestion = SongSuggestion::factory()->create(['youtube_url' => $url]);
            $this->assertNull($suggestion->youtube_video_id);
        }
    }

    /**
     * Test SongSuggestion factory creates valid model.
     */
    public function test_song_suggestion_factory_creates_valid_model(): void
    {
        $suggestion = SongSuggestion::factory()->create();

        $this->assertInstanceOf(SongSuggestion::class, $suggestion);
        $this->assertNotEmpty($suggestion->title);
        $this->assertNotEmpty($suggestion->youtube_url);
        $this->assertNotEmpty($suggestion->suggested_by);
        $this->assertContains($suggestion->status, [
            SongSuggestion::STATUS_PENDING,
            SongSuggestion::STATUS_APPROVED,
            SongSuggestion::STATUS_REJECTED,
        ]);
    }

    /**
     * Test SongSuggestion factory pending state.
     */
    public function test_song_suggestion_factory_pending_state(): void
    {
        $suggestion = SongSuggestion::factory()->pending()->create();

        $this->assertEquals(SongSuggestion::STATUS_PENDING, $suggestion->status);
        $this->assertNull($suggestion->reviewed_by);
        $this->assertNull($suggestion->reviewed_at);
    }

    /**
     * Test SongSuggestion factory approved state.
     */
    public function test_song_suggestion_factory_approved_state(): void
    {
        $suggestion = SongSuggestion::factory()->approved()->create();

        $this->assertEquals(SongSuggestion::STATUS_APPROVED, $suggestion->status);
        $this->assertNotNull($suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
        $this->assertInstanceOf(User::class, $suggestion->reviewedBy);
    }

    /**
     * Test SongSuggestion factory rejected state.
     */
    public function test_song_suggestion_factory_rejected_state(): void
    {
        $suggestion = SongSuggestion::factory()->rejected()->create();

        $this->assertEquals(SongSuggestion::STATUS_REJECTED, $suggestion->status);
        $this->assertNotNull($suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
        $this->assertInstanceOf(User::class, $suggestion->reviewedBy);
    }

    /**
     * Test SongSuggestion factory reviewedBy state.
     */
    public function test_song_suggestion_factory_reviewed_by_state(): void
    {
        $reviewer = User::factory()->create(['name' => 'Specific Reviewer']);
        $suggestion = SongSuggestion::factory()->reviewedBy($reviewer)->create();

        $this->assertEquals($reviewer->id, $suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
        $this->assertEquals('Specific Reviewer', $suggestion->reviewedBy->name);
    }

    /**
     * Test SongSuggestion model attributes are properly typed.
     */
    public function test_song_suggestion_attributes_are_properly_typed(): void
    {
        $reviewer = User::factory()->create();
        $suggestion = SongSuggestion::factory()->create([
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $suggestion->refresh();

        $this->assertIsString($suggestion->title);
        $this->assertIsString($suggestion->youtube_url);
        $this->assertIsString($suggestion->status);
        $this->assertIsString($suggestion->suggested_by);
        $this->assertIsInt($suggestion->reviewed_by);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suggestion->reviewed_at);
    }

    /**
     * Test SongSuggestion model timestamps.
     */
    public function test_song_suggestion_model_timestamps(): void
    {
        $suggestion = SongSuggestion::factory()->create();

        $this->assertNotNull($suggestion->created_at);
        $this->assertNotNull($suggestion->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suggestion->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suggestion->updated_at);
    }

    /**
     * Test SongSuggestion model can be updated.
     */
    public function test_song_suggestion_model_can_be_updated(): void
    {
        $suggestion = SongSuggestion::factory()->pending()->create();
        $reviewer = User::factory()->create();

        $suggestion->update([
            'status' => SongSuggestion::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->assertEquals(SongSuggestion::STATUS_APPROVED, $suggestion->status);
        $this->assertEquals($reviewer->id, $suggestion->reviewed_by);
        $this->assertNotNull($suggestion->reviewed_at);
    }

    /**
     * Test SongSuggestion handles null reviewed_by and reviewed_at.
     */
    public function test_song_suggestion_handles_null_review_fields(): void
    {
        $suggestion = SongSuggestion::factory()->pending()->create();

        $this->assertNull($suggestion->reviewed_by);
        $this->assertNull($suggestion->reviewed_at);
        $this->assertNull($suggestion->reviewedBy);
    }

    /**
     * Test SongSuggestion scopes can be chained.
     */
    public function test_song_suggestion_scopes_can_be_chained(): void
    {
        $oldApproved = SongSuggestion::factory()->approved()->create([
            'created_at' => now()->subDays(5),
            'title' => 'Old Approved'
        ]);

        $newApproved = SongSuggestion::factory()->approved()->create([
            'created_at' => now()->subHours(1),
            'title' => 'New Approved'
        ]);

        SongSuggestion::factory()->pending()->create(['title' => 'Pending']);

        $result = SongSuggestion::approved()
            ->orderBy('created_at', 'desc')
            ->get();

        $this->assertCount(2, $result);
        $this->assertEquals('New Approved', $result->first()->title);
        $this->assertEquals('Old Approved', $result->last()->title);
    }

    /**
     * Test SongSuggestion YouTube URL extraction edge cases.
     */
    public function test_youtube_url_extraction_edge_cases(): void
    {
        // Test with query parameters
        $suggestion = SongSuggestion::factory()->create([
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=playlist&index=1'
        ]);
        $this->assertEquals('dQw4w9WgXcQ', $suggestion->youtube_video_id);

        // Test with timestamp
        $suggestion = SongSuggestion::factory()->create([
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ?t=120'
        ]);
        $this->assertEquals('dQw4w9WgXcQ', $suggestion->youtube_video_id);

        // Test empty string
        $suggestion = SongSuggestion::factory()->create(['youtube_url' => '']);
        $this->assertNull($suggestion->youtube_video_id);
    }
}