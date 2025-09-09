<?php

namespace Tests\Unit\Models;

use App\Models\SongSuggestion;
use Tests\TestCase;

class SongSuggestionTest extends TestCase
{
    public function test_song_suggestion_can_be_created()
    {
        $suggestion = SongSuggestion::factory()->create([
            'title' => 'Test Suggestion',
            'artist' => 'Test Artist',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(SongSuggestion::class, $suggestion);
        $this->assertEquals('Test Suggestion', $suggestion->title);
        $this->assertEquals('Test Artist', $suggestion->artist);
        $this->assertEquals('pending', $suggestion->status);
    }

    public function test_song_suggestion_has_fillable_attributes()
    {
        $suggestion = new SongSuggestion();
        $fillable = $suggestion->getFillable();

        $expectedFillable = [
            'title',
            'artist', 
            'youtube_url',
            'youtube_video_id',
            'thumbnail_url',
            'suggested_by_name',
            'suggested_by_email',
            'status',
            'admin_notes'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_song_suggestion_has_correct_casts()
    {
        $suggestion = new SongSuggestion();
        $casts = $suggestion->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    public function test_pending_scope()
    {
        SongSuggestion::factory()->create(['status' => 'pending']);
        SongSuggestion::factory()->create(['status' => 'approved']);
        SongSuggestion::factory()->create(['status' => 'pending']);

        $pendingSuggestions = SongSuggestion::pending()->get();

        $this->assertCount(2, $pendingSuggestions);
        $this->assertTrue($pendingSuggestions->every(fn($s) => $s->status === 'pending'));
    }

    public function test_approved_scope()
    {
        SongSuggestion::factory()->create(['status' => 'pending']);
        SongSuggestion::factory()->create(['status' => 'approved']);
        SongSuggestion::factory()->create(['status' => 'approved']);

        $approvedSuggestions = SongSuggestion::approved()->get();

        $this->assertCount(2, $approvedSuggestions);
        $this->assertTrue($approvedSuggestions->every(fn($s) => $s->status === 'approved'));
    }

    public function test_rejected_scope()
    {
        SongSuggestion::factory()->create(['status' => 'pending']);
        SongSuggestion::factory()->create(['status' => 'rejected']);
        SongSuggestion::factory()->create(['status' => 'rejected']);

        $rejectedSuggestions = SongSuggestion::rejected()->get();

        $this->assertCount(2, $rejectedSuggestions);
        $this->assertTrue($rejectedSuggestions->every(fn($s) => $s->status === 'rejected'));
    }

    public function test_recent_scope()
    {
        // Create suggestions with different dates
        $old = SongSuggestion::factory()->create();
        $old->created_at = now()->subDays(10);
        $old->save();

        $recent = SongSuggestion::factory()->create();

        $recentSuggestions = SongSuggestion::recent()->get();

        $this->assertEquals($recent->id, $recentSuggestions->first()->id);
        $this->assertEquals($old->id, $recentSuggestions->last()->id);
    }

    public function test_by_email_scope()
    {
        $email = 'test@example.com';
        
        SongSuggestion::factory()->create(['suggested_by_email' => $email]);
        SongSuggestion::factory()->create(['suggested_by_email' => 'other@example.com']);
        SongSuggestion::factory()->create(['suggested_by_email' => $email]);

        $suggestions = SongSuggestion::byEmail($email)->get();

        $this->assertCount(2, $suggestions);
        $this->assertTrue($suggestions->every(fn($s) => $s->suggested_by_email === $email));
    }
}