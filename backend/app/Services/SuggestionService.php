<?php

namespace App\Services;

use App\Helpers\YouTubeHelper;
use App\Models\Song;
use App\Models\SongSuggestion;
use App\Repositories\SongRepository;
use App\Repositories\SuggestionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SuggestionService
{
    public function __construct(
        protected SuggestionRepository $suggestionRepository,
        protected SongRepository $songRepository
    ) {}

    public function getAllSuggestions(int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        return $this->suggestionRepository->getAll($perPage, $status);
    }

    public function getPendingSuggestions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->suggestionRepository->getPending($perPage);
    }

    public function getSuggestion(int $id): ?SongSuggestion
    {
        return $this->suggestionRepository->find($id);
    }

    public function createSuggestion(array $data): SongSuggestion
    {
        // Normalize YouTube URL
        $normalizedUrl = YouTubeHelper::normalizeUrl($data['youtube_url']);
        if (!$normalizedUrl) {
            throw new \InvalidArgumentException('Invalid YouTube URL');
        }
        $data['youtube_url'] = $normalizedUrl;

        // Verificar se já existe uma sugestão com o mesmo YouTube URL
        $existingSuggestion = SongSuggestion::where('youtube_url', $normalizedUrl)->first();
        if ($existingSuggestion) {
            throw new \InvalidArgumentException('A suggestion with this YouTube URL already exists');
        }

        // Verificar se já existe uma música com o mesmo YouTube URL
        $existingSong = Song::where('youtube_url', $normalizedUrl)->first();
        if ($existingSong) {
            throw new \InvalidArgumentException('A song with this YouTube URL already exists');
        }

        $data['status'] = SongSuggestion::STATUS_PENDING;
        
        return $this->suggestionRepository->create($data);
    }

    public function approveSuggestion(SongSuggestion $suggestion, int $reviewerId): Song
    {
        if ($suggestion->status !== SongSuggestion::STATUS_PENDING) {
            throw new \InvalidArgumentException('Only pending suggestions can be approved');
        }

        // Aprovar a sugestão
        $this->suggestionRepository->approve($suggestion, $reviewerId);

        // Criar a música a partir da sugestão
        $songData = [
            'title' => $suggestion->title,
            'youtube_url' => $suggestion->youtube_url,
            'position' => null, // Não vai para o top 5 automaticamente
            'plays_count' => 0,
        ];

        return $this->songRepository->create($songData);
    }

    public function rejectSuggestion(SongSuggestion $suggestion, int $reviewerId): bool
    {
        if ($suggestion->status !== SongSuggestion::STATUS_PENDING) {
            throw new \InvalidArgumentException('Only pending suggestions can be rejected');
        }

        return $this->suggestionRepository->reject($suggestion, $reviewerId);
    }

    public function getRecentApprovedSuggestions(int $limit = 10): Collection
    {
        return $this->suggestionRepository->getRecentApproved($limit);
    }

    public function searchSuggestions(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->suggestionRepository->searchByTitle($query, $perPage);
    }

    public function getSuggestionStats(): array
    {
        return [
            'pending' => $this->suggestionRepository->getByStatus(SongSuggestion::STATUS_PENDING)->count(),
            'approved' => $this->suggestionRepository->getByStatus(SongSuggestion::STATUS_APPROVED)->count(),
            'rejected' => $this->suggestionRepository->getByStatus(SongSuggestion::STATUS_REJECTED)->count(),
        ];
    }
}