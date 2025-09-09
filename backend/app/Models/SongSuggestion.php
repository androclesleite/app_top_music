<?php

namespace App\Models;

use App\Helpers\YouTubeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongSuggestion extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'artist',
        'youtube_url',
        'status',
        'suggested_by',
        'suggested_by_name',
        'suggested_by_email',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function getYoutubeThumbnailAttribute(): string
    {
        return YouTubeHelper::getThumbnailUrl($this->youtube_url) 
            ?? 'https://via.placeholder.com/480x360/cccccc/666666?text=No+Image';
    }

    public function getYoutubeVideoIdAttribute(): ?string
    {
        return YouTubeHelper::extractVideoId($this->youtube_url);
    }
}