<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'youtube_url' => $this->youtube_url,
            'youtube_video_id' => $this->youtube_video_id,
            'youtube_thumbnail' => $this->youtube_thumbnail,
            'position' => $this->position,
            'plays_count' => $this->plays_count,
            'is_top_five' => $this->position && $this->position <= 5,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}