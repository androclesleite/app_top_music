<?php

namespace App\Repositories;

use App\Models\SongSuggestion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SuggestionRepository
{
    public function __construct(
        protected SongSuggestion $model
    ) {}

    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->pending()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?SongSuggestion
    {
        return $this->model->find($id);
    }

    public function create(array $data): SongSuggestion
    {
        return $this->model->create($data);
    }

    public function update(SongSuggestion $suggestion, array $data): bool
    {
        return $suggestion->update($data);
    }

    public function approve(SongSuggestion $suggestion, int $reviewerId): bool
    {
        return $suggestion->update([
            'status' => SongSuggestion::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    public function reject(SongSuggestion $suggestion, int $reviewerId): bool
    {
        return $suggestion->update([
            'status' => SongSuggestion::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function getRecentApproved(int $limit = 10): Collection
    {
        return $this->model->approved()
            ->orderBy('reviewed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchByTitle(string $title, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('title', 'like', '%' . $title . '%')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}