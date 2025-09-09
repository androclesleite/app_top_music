<?php

namespace App\Services;

use App\Helpers\YouTubeHelper;
use App\Models\Song;
use App\Repositories\SongRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SongService
{
    public function __construct(
        protected SongRepository $songRepository
    ) {}

    public function getTopFive(): Collection
    {
        return $this->songRepository->getTopFive();
    }

    public function getOthers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->songRepository->getOthers($perPage);
    }

    public function getSong(int $id): ?Song
    {
        return $this->songRepository->find($id);
    }

    public function createSong(array $data): Song
    {
        // Normalize YouTube URL if present
        if (isset($data['youtube_url'])) {
            $normalizedUrl = YouTubeHelper::normalizeUrl($data['youtube_url']);
            if (!$normalizedUrl) {
                throw new \InvalidArgumentException('Invalid YouTube URL');
            }
            $data['youtube_url'] = $normalizedUrl;
        }

        // Se uma posição foi especificada, precisamos reorganizar as posições existentes
        if (isset($data['position']) && $data['position'] <= 5) {
            $this->adjustPositions($data['position']);
        }

        return $this->songRepository->create($data);
    }

    public function updateSong(Song $song, array $data): bool
    {
        // Normalize YouTube URL if present
        if (isset($data['youtube_url'])) {
            $normalizedUrl = YouTubeHelper::normalizeUrl($data['youtube_url']);
            if (!$normalizedUrl) {
                throw new \InvalidArgumentException('Invalid YouTube URL');
            }
            $data['youtube_url'] = $normalizedUrl;
        }

        // Se a posição foi alterada, reorganizar as posições
        if (isset($data['position']) && $data['position'] !== $song->position) {
            $this->adjustPositions($data['position'], $song->id);
        }

        return $this->songRepository->update($song, $data);
    }

    public function deleteSong(Song $song): bool
    {
        $deleted = $this->songRepository->delete($song);

        if ($deleted && $song->position && $song->position <= 5) {
            $this->reorganizeTopFive();
        }

        return $deleted;
    }

    public function playSong(Song $song): bool
    {
        return $this->songRepository->incrementPlaysCount($song);
    }

    public function updateTopFivePositions(array $positions): void
    {
        // Validar que são exatamente 5 posições
        if (count($positions) !== 5) {
            throw new \InvalidArgumentException('Must provide exactly 5 positions');
        }

        // Validar que as posições são de 1 a 5
        $expectedPositions = [1, 2, 3, 4, 5];
        $providedPositions = array_values($positions);
        sort($providedPositions);

        if ($providedPositions !== $expectedPositions) {
            throw new \InvalidArgumentException('Positions must be 1, 2, 3, 4, 5');
        }

        $this->songRepository->updatePositions($positions);
    }

    public function searchSongs(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->songRepository->searchByTitle($query, $perPage);
    }

    protected function adjustPositions(int $newPosition, ?int $excludeId = null): void
    {
        if ($newPosition > 5) {
            return;
        }

        // Mover todas as músicas da posição desejada para baixo
        for ($i = $newPosition; $i <= 5; $i++) {
            $existingSong = $this->songRepository->getByPosition($i);
            
            if ($existingSong && $existingSong->id !== $excludeId) {
                $this->songRepository->update($existingSong, ['position' => $i + 1]);
            }
        }
    }

    protected function reorganizeTopFive(): void
    {
        $topFiveSongs = $this->songRepository->getTopFive();
        
        $position = 1;
        foreach ($topFiveSongs as $song) {
            if ($song->position !== $position) {
                $this->songRepository->update($song, ['position' => $position]);
            }
            $position++;
        }
    }
}