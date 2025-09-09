<?php

namespace App\Models;

use App\Helpers\YouTubeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'youtube_url',
        'position',
        'plays_count',
    ];

    protected $casts = [
        'position' => 'integer',
        'plays_count' => 'integer',
    ];

    public function suggestions(): HasMany
    {
        return $this->hasMany(SongSuggestion::class);
    }

    public function scopeTopFive($query)
    {
        return $query->whereBetween('position', [1, 5])
                     ->orderBy('position');
    }

    public function scopeOthers($query)
    {
        return $query->where('position', '>', 5)
                     ->orWhereNull('position')
                     ->orderBy('plays_count', 'desc');
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