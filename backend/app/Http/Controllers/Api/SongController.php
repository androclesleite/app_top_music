<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Http\Resources\SongResource;
use App\Services\SongService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function __construct(
        protected SongService $songService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Se foi solicitado apenas o top 5
        if ($request->boolean('top_five_only')) {
            $songs = $this->songService->getTopFive();
            return response()->json([
                'success' => true,
                'data' => SongResource::collection($songs)
            ]);
        }

        // Se tem busca
        if ($request->filled('search')) {
            $songs = $this->songService->searchSongs(
                $request->get('search'),
                $request->get('per_page', 15)
            );
        } else {
            // Buscar outras músicas (não top 5) com paginação
            $songs = $this->songService->getOthers($request->get('per_page', 15));
        }

        return response()->json([
            'success' => true,
            'data' => SongResource::collection($songs->items()),
            'meta' => [
                'current_page' => $songs->currentPage(),
                'from' => $songs->firstItem(),
                'last_page' => $songs->lastPage(),
                'per_page' => $songs->perPage(),
                'to' => $songs->lastItem(),
                'total' => $songs->total(),
            ]
        ]);
    }

    public function topFive(): JsonResponse
    {
        $songs = $this->songService->getTopFive();

        return response()->json([
            'success' => true,
            'data' => SongResource::collection($songs)
        ]);
    }

    public function store(StoreSongRequest $request): JsonResponse
    {
        try {
            $song = $this->songService->createSong($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Música criada com sucesso.',
                'data' => new SongResource($song)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar a música: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $song = $this->songService->getSong($id);

        if (!$song) {
            return response()->json([
                'success' => false,
                'message' => 'Música não encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SongResource($song)
        ]);
    }

    public function update(UpdateSongRequest $request, int $id): JsonResponse
    {
        $song = $this->songService->getSong($id);

        if (!$song) {
            return response()->json([
                'success' => false,
                'message' => 'Música não encontrada.'
            ], 404);
        }

        try {
            $this->songService->updateSong($song, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Música atualizada com sucesso.',
                'data' => new SongResource($song->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar a música: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $song = $this->songService->getSong($id);

        if (!$song) {
            return response()->json([
                'success' => false,
                'message' => 'Música não encontrada.'
            ], 404);
        }

        try {
            $this->songService->deleteSong($song);

            return response()->json([
                'success' => true,
                'message' => 'Música excluída com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir a música: ' . $e->getMessage()
            ], 500);
        }
    }

    public function play(int $id): JsonResponse
    {
        $song = $this->songService->getSong($id);

        if (!$song) {
            return response()->json([
                'success' => false,
                'message' => 'Música não encontrada.'
            ], 404);
        }

        $this->songService->playSong($song);

        return response()->json([
            'success' => true,
            'message' => 'Reprodução contabilizada.',
            'data' => new SongResource($song->fresh())
        ]);
    }

    public function updatePositions(Request $request): JsonResponse
    {
        $request->validate([
            'positions' => ['required', 'array'],
            'positions.*' => ['required', 'integer', 'min:1', 'max:5']
        ]);

        try {
            $this->songService->updateTopFivePositions($request->get('positions'));

            return response()->json([
                'success' => true,
                'message' => 'Posições atualizadas com sucesso.'
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar as posições: ' . $e->getMessage()
            ], 500);
        }
    }
}