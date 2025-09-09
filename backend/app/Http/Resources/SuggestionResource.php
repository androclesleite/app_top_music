<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'youtube_url' => $this->youtube_url,
            'youtube_video_id' => $this->youtube_video_id,
            'youtube_thumbnail' => $this->youtube_thumbnail,
            'status' => $this->status,
            'suggested_by' => $this->suggested_by,
            'suggested_by_name' => $this->suggested_by_name,
            'suggested_by_email' => $this->suggested_by_email,
            'reviewed_by' => $this->whenLoaded('reviewedBy', function () {
                return new UserResource($this->reviewedBy);
            }),
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'status_label' => $this->getStatusLabel(),
        ];
    }

    protected function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
            default => 'Desconhecido'
        };
    }
}