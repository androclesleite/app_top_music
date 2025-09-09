<?php

namespace App\Repositories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SongRepository
{
    public function __construct(
        protected Song $model
    ) {}

    public function getTopFive(): Collection
    {
        return $this->model->topFive()->get();
    }

    public function getOthers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->others()->paginate($perPage);
    }

    public function find(int $id): ?Song
    {
        return $this->model->find($id);
    }

    public function create(array $data): Song
    {
        return $this->model->create($data);
    }

    public function update(Song $song, array $data): bool
    {
        return $song->update($data);
    }

    public function delete(Song $song): bool
    {
        return $song->delete();
    }

    public function incrementPlaysCount(Song $song): bool
    {
        return $song->increment('plays_count');
    }

    public function updatePositions(array $positions): void
    {
        foreach ($positions as $id => $position) {
            $this->model->where('id', $id)->update(['position' => $position]);
        }
    }

    public function getByPosition(int $position): ?Song
    {
        return $this->model->where('position', $position)->first();
    }

    public function searchByTitle(string $title, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('title', 'like', '%' . $title . '%')
            ->orderBy('plays_count', 'desc')
            ->paginate($perPage);
    }
}